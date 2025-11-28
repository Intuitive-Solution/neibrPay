<?php

namespace App\Services;

use App\Models\PlaidBankAccount;
use App\Models\PlaidTransaction;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlaidService
{
    private string $clientId;
    private string $clientSecret;
    private string $environment;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.plaid.client_id');
        $this->clientSecret = config('services.plaid.client_secret');
        $this->environment = config('services.plaid.environment', 'sandbox');
        $this->baseUrl = "https://{$this->environment}.plaid.com";
    }

    /**
     * Create a Plaid Link token for onboarding a new bank account
     */
    public function createLinkToken(int $tenantId, string $userEmail): array
    {
        try {
            // Log configuration for debugging
            Log::info('Plaid Link Token Request', [
                'environment' => $this->environment,
                'base_url' => $this->baseUrl,
                'client_id' => substr($this->clientId, 0, 10) . '...',
                'has_secret' => !empty($this->clientSecret),
                'redirect_uri' => config('services.plaid.redirect_uri', env('PLAID_REDIRECT_URI')),
            ]);

            $payload = [
                'client_id' => $this->clientId,
                'secret' => $this->clientSecret,
                'user' => [
                    'client_user_id' => "tenant_{$tenantId}",
                ],
                'client_name' => config('app.name', 'NeibrPay'),
                'language' => 'en',
                'country_codes' => ['US'],
                'products' => ['transactions'],
                'redirect_uri' => config('services.plaid.redirect_uri', env('PLAID_REDIRECT_URI')),
            ];

            $response = Http::post("{$this->baseUrl}/link/token/create", $payload);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('Plaid API Error Response', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                ]);
                throw new Exception('Failed to create Link token: ' . $errorBody);
            }

            Log::info('Plaid Link Token Created Successfully');
            return $response->json();
        } catch (Exception $e) {
            Log::error('Plaid Link Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exchange public token for access token after successful onboarding
     */
    public function exchangePublicToken(string $publicToken, int $tenantId): PlaidBankAccount
    {
        try {
            $response = Http::post("{$this->baseUrl}/item/public_token/exchange", [
                'client_id' => $this->clientId,
                'secret' => $this->clientSecret,
                'public_token' => $publicToken,
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to exchange public token: ' . $response->body());
            }

            $data = $response->json();
            $accessToken = $data['access_token'];
            $itemId = $data['item_id'];

            // Get account details
            $accountsResponse = Http::post("{$this->baseUrl}/accounts/get", [
                'client_id' => $this->clientId,
                'secret' => $this->clientSecret,
                'access_token' => $accessToken,
            ]);

            if ($accountsResponse->failed()) {
                throw new Exception('Failed to get account details: ' . $accountsResponse->body());
            }

            $accountsData = $accountsResponse->json();
            $account = $accountsData['accounts'][0]; // Use first account
            
            // Get institution info from item
            $item = $accountsData['item'] ?? [];
            $institutionId = $item['institution_id'] ?? 'unknown';
            $institutionName = $item['institution_name'] ?? 'Unknown Institution';
            
            // Alternative: if institution_data exists, use it
            if (isset($item['institution_data'])) {
                $institutionId = $item['institution_data']['institution_id'] ?? $institutionId;
                $institutionName = $item['institution_data']['name'] ?? $institutionName;
            }

            // Create or update bank account
            $bankAccount = PlaidBankAccount::updateOrCreate(
                ['plaid_item_id' => $itemId],
                [
                    'tenant_id' => $tenantId,
                    'plaid_access_token' => $accessToken,
                    'institution_id' => $institutionId,
                    'institution_name' => $institutionName,
                    'account_id' => $account['account_id'],
                    'account_name' => $account['name'],
                    'account_mask' => $account['mask'] ?? substr($account['account_id'], -4),
                    'status' => 'active',
                ]
            );

            return $bankAccount;
        } catch (Exception $e) {
            Log::error('Plaid Exchange Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get account balance from Plaid
     */
    public function getAccountBalance(PlaidBankAccount $bankAccount): ?array
    {
        try {
            $accessToken = $bankAccount->getDecryptedAccessToken();

            $response = Http::post("{$this->baseUrl}/accounts/balance/get", [
                'client_id' => $this->clientId,
                'secret' => $this->clientSecret,
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                Log::warning("Failed to get balance for account {$bankAccount->id}: " . $response->body());
                return null;
            }

            $data = $response->json();
            $accounts = $data['accounts'] ?? [];
            
            if (empty($accounts)) {
                return null;
            }

            // Find the matching account
            foreach ($accounts as $account) {
                if ($account['account_id'] === $bankAccount->account_id) {
                    return [
                        'current' => $account['balances']['current'] ?? 0,
                        'available' => $account['balances']['available'] ?? 0,
                        'limit' => $account['balances']['limit'] ?? null,
                        'iso_currency_code' => $account['balances']['iso_currency_code'] ?? 'USD',
                        'unofficial_currency_code' => $account['balances']['unofficial_currency_code'] ?? null,
                    ];
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error("Plaid Get Balance Error for account {$bankAccount->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync transactions for a bank account
     */
    public function syncTransactions(PlaidBankAccount $bankAccount): array
    {
        try {
            $accessToken = $bankAccount->getDecryptedAccessToken();
            $startDate = $bankAccount->sync_start_date ?? now()->subDays(90)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');

            $response = Http::post("{$this->baseUrl}/transactions/get", [
                'client_id' => $this->clientId,
                'secret' => $this->clientSecret,
                'access_token' => $accessToken,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'options' => [
                    'include_personal_finance_category' => true,
                ],
            ]);

            if ($response->failed()) {
                $bankAccount->update([
                    'status' => 'error',
                    'error_message' => 'Failed to sync transactions',
                ]);
                throw new Exception('Failed to get transactions: ' . $response->body());
            }

            $data = $response->json();
            $transactions = $data['transactions'];
            $totalTransactions = $data['total_transactions'];

            // Store transactions
            foreach ($transactions as $transaction) {
                PlaidTransaction::updateOrCreate(
                    [
                        'plaid_transaction_id' => $transaction['transaction_id'],
                        'plaid_bank_account_id' => $bankAccount->id,
                    ],
                    [
                        'tenant_id' => $bankAccount->tenant_id,
                        'amount' => $transaction['amount'],
                        'date' => $transaction['date'],
                        'name' => $transaction['name'],
                        'merchant_name' => $transaction['merchant_name'] ?? null,
                        'category' => $transaction['personal_finance_category']['primary'] ?? null,
                        'categories' => $transaction['categories'] ?? [],
                        'pending' => $transaction['pending'] ?? false,
                        'personal_finance_category' => json_encode($transaction['personal_finance_category'] ?? []),
                    ]
                );
            }

            // Get current balance
            $balance = $this->getAccountBalance($bankAccount);

            // Update sync metadata
            $updateData = [
                'last_synced_at' => now(),
                'status' => 'active',
                'error_message' => null,
            ];
            
            if ($balance) {
                $updateData['current_balance'] = $balance['current'];
                $updateData['available_balance'] = $balance['available'];
            }

            $bankAccount->update($updateData);

            return [
                'success' => true,
                'synced_count' => count($transactions),
                'total_transactions' => $totalTransactions,
                'bank_account_id' => $bankAccount->id,
            ];
        } catch (Exception $e) {
            Log::error("Plaid Sync Error for account {$bankAccount->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync all active bank accounts
     */
    public function syncAllAccounts(): array
    {
        $results = [
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
        ];

        $accounts = PlaidBankAccount::active()->get();

        foreach ($accounts as $account) {
            try {
                $this->syncTransactions($account);
                $results['success_count']++;
            } catch (Exception $e) {
                $results['error_count']++;
                $results['errors'][] = [
                    'account_id' => $account->id,
                    'message' => $e->getMessage(),
                ];
                Log::error("Failed to sync account {$account->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Disconnect a bank account (remove access)
     * Deletes all related transactions and the bank account record
     */
    public function disconnectAccount(PlaidBankAccount $bankAccount): bool
    {
        try {
            $accessToken = $bankAccount->getDecryptedAccessToken();
            $accountId = $bankAccount->id;
            $tenantId = $bankAccount->tenant_id;

            // Remove access from Plaid
            Http::post("{$this->baseUrl}/item/remove", [
                'client_id' => $this->clientId,
                'secret' => $this->clientSecret,
                'access_token' => $accessToken,
            ]);

            // Delete all related transactions
            PlaidTransaction::where('plaid_bank_account_id', $accountId)->delete();

            // Delete the bank account record
            $bankAccount->forceDelete();

            Log::info('Bank account and transactions deleted', [
                'bank_account_id' => $accountId,
                'tenant_id' => $tenantId,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Plaid Disconnect Error: ' . $e->getMessage());
            return false;
        }
    }
}

