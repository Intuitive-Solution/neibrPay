<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaidBankAccount;
use App\Models\PlaidTransaction;
use App\Services\PlaidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlaidController extends Controller
{
    private PlaidService $plaidService;

    public function __construct(PlaidService $plaidService)
    {
        $this->plaidService = $plaidService;
    }

    /**
     * GET /api/plaid/debug
     * Debug endpoint to check Plaid configuration
     */
    public function debug(Request $request): JsonResponse
    {
        try {
            $clientId = config('services.plaid.client_id');
            $clientSecret = config('services.plaid.client_secret');
            $environment = config('services.plaid.environment', 'sandbox');
            $redirectUri = config('services.plaid.redirect_uri', env('PLAID_REDIRECT_URI'));

            return response()->json([
                'plaid_configuration' => [
                    'environment' => $environment,
                    'client_id' => $clientId ? substr($clientId, 0, 10) . '...' : 'NOT SET',
                    'client_secret' => $clientSecret ? '***SET***' : 'NOT SET',
                    'redirect_uri' => $redirectUri,
                    'base_url' => "https://{$environment}.plaid.com",
                ],
                'validation' => [
                    'client_id_valid' => !empty($clientId) && strlen($clientId) > 5,
                    'client_secret_valid' => !empty($clientSecret) && strlen($clientSecret) > 5,
                    'environment_valid' => in_array($environment, ['sandbox', 'development', 'production']),
                    'redirect_uri_valid' => !empty($redirectUri),
                ],
                'status' => 'Configuration loaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/plaid/link-token
     * Create a Link token for onboarding
     */
    public function createLinkToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $tenantId = $user->tenant_id;
            $token = $this->plaidService->createLinkToken($tenantId, $user->email);

            return response()->json($token);
        } catch (\Exception $e) {
            Log::error('Create Link Token Error: ' . $e->getMessage());
            return response()->json(
                ['error' => 'Failed to create Link token: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * POST /api/plaid/exchange-token
     * Exchange public token and save bank account
     */
    public function exchangeToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $validated = $request->validate([
                'public_token' => 'required|string',
            ]);

            $bankAccount = $this->plaidService->exchangePublicToken(
                $validated['public_token'],
                $user->tenant_id
            );

            return response()->json([
                'message' => 'Bank account(s) connected successfully',
                'bank_account' => [
                    'id' => $bankAccount->id,
                    'account_name' => $bankAccount->account_name,
                    'account_mask' => $bankAccount->account_mask,
                    'institution_name' => $bankAccount->institution_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Exchange Token Error: ' . $e->getMessage());
            return response()->json(
                ['error' => 'Failed to connect bank account: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * GET /api/plaid/bank-accounts
     * List all connected bank accounts for the tenant
     */
    public function getBankAccounts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $accounts = PlaidBankAccount::forTenant($user->tenant_id)
                ->select('id', 'account_name', 'account_mask', 'institution_name', 'status', 'current_balance', 'available_balance', 'last_synced_at', 'sync_start_date')
                ->get();

            return response()->json([
                'bank_accounts' => $accounts,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Bank Accounts Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve bank accounts'], 500);
        }
    }

    /**
     * DELETE /api/plaid/bank-accounts/{id}
     * Disconnect a bank account
     */
    public function disconnectBankAccount(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $bankAccount = PlaidBankAccount::forTenant($user->tenant_id)->findOrFail($id);

            $this->plaidService->disconnectAccount($bankAccount);

            return response()->json([
                'message' => 'Bank account disconnected successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Bank account not found'], 404);
        } catch (\Exception $e) {
            Log::error('Disconnect Bank Account Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to disconnect bank account'], 500);
        }
    }

    /**
     * GET /api/plaid/transactions
     * Get transactions with filtering and pagination
     */
    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $validated = $request->validate([
                'bank_account_id' => 'nullable|integer',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'search' => 'nullable|string',
                'pending' => 'nullable|in:true,false,1,0',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'sort_by' => 'nullable|string|in:date,name,amount,category,pending,plaid_bank_account_id',
                'sort_order' => 'nullable|string|in:asc,desc',
            ]);

            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 20;
            
            // Convert pending string to boolean if present
            if (isset($validated['pending'])) {
                $validated['pending'] = filter_var($validated['pending'], FILTER_VALIDATE_BOOLEAN);
            }

            $query = PlaidTransaction::forTenant($user->tenant_id);

            // Filter by bank account if specified
            if ($validated['bank_account_id'] ?? null) {
                $query->forAccount($validated['bank_account_id']);
            }

            // Filter by date range
            if ($validated['start_date'] ?? null) {
                $endDate = $validated['end_date'] ?? now()->format('Y-m-d');
                $query->dateBetween($validated['start_date'], $endDate);
            }

            // Search by name or merchant
            if ($validated['search'] ?? null) {
                $query->search($validated['search']);
            }

            // Filter by pending status
            if (isset($validated['pending'])) {
                $query->where('pending', $validated['pending']);
            }

            // Sorting
            $sortBy = $validated['sort_by'] ?? 'date';
            $sortOrder = $validated['sort_order'] ?? 'desc';
            
            // Calculate total amount of all matching transactions (before pagination)
            $totalAmount = (float) (clone $query)->sum('amount');
            
            $transactions = $query
                ->with('bankAccount:id,account_name,account_mask')
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $transactions->items(),
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                    'total_amount' => $totalAmount,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get Transactions Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve transactions'], 500);
        }
    }

    /**
     * POST /api/plaid/sync-all
     * Manual trigger to sync all active bank accounts (secured with API key)
     */
    public function syncAll(Request $request): JsonResponse
    {
        try {
            // Verify API key
            $apiKey = $request->header('X-N8N-API-Key');
            if ($apiKey !== config('services.plaid.api_key')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $results = $this->plaidService->syncAllAccounts();

            return response()->json([
                'message' => 'Sync completed',
                'results' => $results,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Sync All Error: ' . $e->getMessage());
            return response()->json(
                ['error' => 'Failed to sync accounts: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * POST /api/plaid/sync
     * Manual sync for a specific bank account
     */
    public function syncAccount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $validated = $request->validate([
                'bank_account_id' => 'required|integer',
            ]);

            $bankAccount = PlaidBankAccount::forTenant($user->tenant_id)
                ->findOrFail($validated['bank_account_id']);

            $result = $this->plaidService->syncTransactions($bankAccount);

            return response()->json([
                'message' => 'Sync completed successfully',
                'result' => $result,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Bank account not found'], 404);
        } catch (\Exception $e) {
            Log::error('Sync Account Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to sync account: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/plaid/webhook
     * Webhook endpoint to handle Plaid events (public, no authentication)
     * Processes SYNC_UPDATES_AVAILABLE events for incremental transaction updates
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            // Get payload - handle both JSON and form-encoded requests
            $payload = [];
            if ($request->isJson()) {
                $payload = $request->json()->all();
            } else {
                $payload = $request->all();
            }

            // Log all webhook events for debugging
            Log::info('Plaid webhook received', [
                'webhook_type' => $payload['webhook_type'] ?? 'unknown',
                'webhook_code' => $payload['webhook_code'] ?? 'unknown',
                'item_id' => $payload['item_id'] ?? 'unknown',
                'content_type' => $request->header('Content-Type'),
                'has_json' => $request->isJson(),
            ]);

            // Verify webhook signature if secret is configured (optional but recommended)
            if (config('services.plaid.webhook_secret')) {
                try {
                    $this->verifyWebhookSignature($request);
                } catch (\Exception $e) {
                    Log::warning('Webhook signature verification failed', [
                        'error' => $e->getMessage(),
                    ]);
                    // Continue processing even if signature verification fails
                    // (you may want to throw here in production)
                }
            }

            $webhookType = $payload['webhook_type'] ?? null;
            $webhookCode = $payload['webhook_code'] ?? null;
            $itemId = $payload['item_id'] ?? null;

            // Handle SYNC_UPDATES_AVAILABLE webhook
            if ($webhookType === 'TRANSACTIONS' && $webhookCode === 'SYNC_UPDATES_AVAILABLE') {
                if (!$itemId) {
                    Log::warning('SYNC_UPDATES_AVAILABLE webhook missing item_id');
                    return response()->json(['status' => 'received'], 200);
                }

                // Find all bank accounts for this item and sync them
                $accounts = PlaidBankAccount::where('plaid_item_id', $itemId)
                    ->get();

                if ($accounts->isEmpty()) {
                    Log::warning('No bank accounts found for Plaid item', ['item_id' => $itemId]);
                    return response()->json(['status' => 'received'], 200);
                }

                // Sync only the first account (will sync all accounts for the item)
                // Note: For production, consider queueing this to avoid webhook timeouts
                try {
                    // Dispatch sync job in background to avoid webhook timeout
                    // For now, we'll run it synchronously but with error handling
                    $syncResult = $this->plaidService->syncTransactions($accounts->first());
                    Log::info('Webhook sync completed', [
                        'item_id' => $itemId,
                        'sync_result' => $syncResult,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Webhook sync failed', [
                        'item_id' => $itemId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Still return 200 to acknowledge receipt
                }
            } else {
                // Log other webhook types for informational purposes
                Log::info('Plaid webhook ignored (not SYNC_UPDATES_AVAILABLE)', [
                    'webhook_type' => $webhookType,
                    'webhook_code' => $webhookCode,
                ]);
            }

            // Always return 200 OK to acknowledge receipt
            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            Log::error('Plaid Webhook Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Still return 200 to prevent Plaid from retrying
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        } catch (\Throwable $e) {
            // Catch any other throwable (PHP 7+)
            Log::error('Plaid Webhook Fatal Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 200);
        }
    }

    /**
     * Verify webhook signature using HMAC-SHA256
     * Optional: Use if webhook_secret is configured in environment
     */
    private function verifyWebhookSignature(Request $request): void
    {
        $signature = $request->header('Plaid-Verification');
        if (!$signature) {
            throw new \Exception('Missing webhook signature');
        }

        $secret = config('services.plaid.webhook_secret');
        $body = $request->getContent();
        
        // Plaid uses HMAC-SHA256 for signature verification
        $expectedSignature = hash_hmac('sha256', $body, $secret);
        
        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception('Invalid webhook signature');
        }
    }
}

