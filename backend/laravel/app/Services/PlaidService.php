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
     * Processes ALL accounts returned by Plaid, not just the first one
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
            $accounts = $accountsData['accounts'] ?? [];
            
            if (empty($accounts)) {
                throw new Exception('No accounts found in Plaid response');
            }
            
            // Get institution info from item
            $item = $accountsData['item'] ?? [];
            $institutionId = $item['institution_id'] ?? 'unknown';
            $institutionName = $item['institution_name'] ?? 'Unknown Institution';
            
            // Alternative: if institution_data exists, use it
            if (isset($item['institution_data'])) {
                $institutionId = $item['institution_data']['institution_id'] ?? $institutionId;
                $institutionName = $item['institution_data']['name'] ?? $institutionName;
            }

            // Process ALL accounts, not just the first one
            $createdAccounts = [];
            foreach ($accounts as $account) {
                // Extract balance information from the account data
                $balances = $account['balances'] ?? [];
                
                // Use account_id + tenant_id as unique key to allow multiple accounts per tenant
                // account_id is globally unique in Plaid, so this ensures we don't overwrite accounts
                $bankAccount = PlaidBankAccount::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'account_id' => $account['account_id'],
                    ],
                    [
                        'plaid_item_id' => $itemId,
                        'plaid_access_token' => $accessToken,
                        'institution_id' => $institutionId,
                        'institution_name' => $institutionName,
                        'account_name' => $account['name'],
                        'account_mask' => $account['mask'] ?? substr($account['account_id'], -4),
                        'current_balance' => $balances['current'] ?? null,
                        'available_balance' => $balances['available'] ?? null,
                        'status' => 'active',
                    ]
                );
                
                $createdAccounts[] = $bankAccount;
            }

            Log::info('Plaid accounts processed', [
                'tenant_id' => $tenantId,
                'item_id' => $itemId,
                'accounts_count' => count($createdAccounts),
            ]);

            // Return the first account for backward compatibility
            return $createdAccounts[0];
        } catch (Exception $e) {
            Log::error('Plaid Exchange Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get account balance from Plaid
     * Uses /accounts/get endpoint which includes balances and doesn't require min_last_updated_datetime
     */
    public function getAccountBalance(PlaidBankAccount $bankAccount): ?array
    {
        try {
            $accessToken = $bankAccount->getDecryptedAccessToken();

            // Use /accounts/get which includes balances and doesn't require min_last_updated_datetime
            $response = Http::post("{$this->baseUrl}/accounts/get", [
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
     * Note: Plaid returns transactions for ALL accounts under the same Item,
     * so we need to match each transaction to the correct bank account by account_id
     */
    public function syncTransactions(PlaidBankAccount $bankAccount): array
    {
        try {
            $accessToken = $bankAccount->getDecryptedAccessToken();
            $itemId = $bankAccount->plaid_item_id;
            $tenantId = $bankAccount->tenant_id;
            
            // Use the earliest sync_start_date from all accounts sharing this Item
            $allAccountsForItem = PlaidBankAccount::where('plaid_item_id', $itemId)
                ->where('tenant_id', $tenantId)
                ->get();
            
            $startDate = $allAccountsForItem
                ->filter(fn($acc) => $acc->sync_start_date !== null)
                ->min('sync_start_date');
            
            if (!$startDate) {
                $startDate = now()->subDays(90)->format('Y-m-d');
            } else {
                $startDate = $startDate->format('Y-m-d');
            }
            
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
            $transactions = $data['transactions'] ?? [];
            $totalTransactions = $data['total_transactions'] ?? 0;

            // Create a map of Plaid account_id -> PlaidBankAccount model
            $accountMap = [];
            foreach ($allAccountsForItem as $acc) {
                $accountMap[$acc->account_id] = $acc;
            }

            // Store transactions, matching each to the correct bank account
            $syncedByAccount = [];
            foreach ($transactions as $transaction) {
                $plaidAccountId = $transaction['account_id'] ?? null;
                
                if (!$plaidAccountId) {
                    Log::warning('Transaction missing account_id', [
                        'transaction_id' => $transaction['transaction_id'] ?? 'unknown',
                    ]);
                    continue;
                }

                // Find the matching bank account
                $matchingBankAccount = $accountMap[$plaidAccountId] ?? null;
                
                if (!$matchingBankAccount) {
                    Log::warning('Transaction account_id not found in bank accounts', [
                        'plaid_account_id' => $plaidAccountId,
                        'transaction_id' => $transaction['transaction_id'] ?? 'unknown',
                        'item_id' => $itemId,
                    ]);
                    continue;
                }

                // Check if transaction already exists (plaid_transaction_id is unique)
                $existingTransaction = PlaidTransaction::where('plaid_transaction_id', $transaction['transaction_id'])
                    ->first();

                if ($existingTransaction) {
                    // Transaction exists - update if bank_account_id changed (shouldn't happen, but handle it)
                    if ($existingTransaction->plaid_bank_account_id !== $matchingBankAccount->id) {
                        Log::warning('Transaction already exists with different bank_account_id', [
                            'transaction_id' => $transaction['transaction_id'],
                            'existing_account_id' => $existingTransaction->plaid_bank_account_id,
                            'new_account_id' => $matchingBankAccount->id,
                        ]);
                        // Update the transaction to the correct account
                        $existingTransaction->update([
                            'plaid_bank_account_id' => $matchingBankAccount->id,
                            'amount' => $transaction['amount'],
                            'date' => $transaction['date'],
                            'name' => $transaction['name'],
                            'merchant_name' => $transaction['merchant_name'] ?? null,
                            'category' => $transaction['personal_finance_category']['primary'] ?? null,
                            'categories' => $transaction['categories'] ?? [],
                            'pending' => $transaction['pending'] ?? false,
                            'personal_finance_category' => json_encode($transaction['personal_finance_category'] ?? []),
                        ]);
                    } else {
                        // Transaction already exists with correct account - just update fields
                        $existingTransaction->update([
                            'amount' => $transaction['amount'],
                            'date' => $transaction['date'],
                            'name' => $transaction['name'],
                            'merchant_name' => $transaction['merchant_name'] ?? null,
                            'category' => $transaction['personal_finance_category']['primary'] ?? null,
                            'categories' => $transaction['categories'] ?? [],
                            'pending' => $transaction['pending'] ?? false,
                            'personal_finance_category' => json_encode($transaction['personal_finance_category'] ?? []),
                        ]);
                    }
                } else {
                    // New transaction - create it
                    PlaidTransaction::create([
                        'plaid_transaction_id' => $transaction['transaction_id'],
                        'plaid_bank_account_id' => $matchingBankAccount->id,
                        'tenant_id' => $tenantId,
                        'amount' => $transaction['amount'],
                        'date' => $transaction['date'],
                        'name' => $transaction['name'],
                        'merchant_name' => $transaction['merchant_name'] ?? null,
                        'category' => $transaction['personal_finance_category']['primary'] ?? null,
                        'categories' => $transaction['categories'] ?? [],
                        'pending' => $transaction['pending'] ?? false,
                        'personal_finance_category' => json_encode($transaction['personal_finance_category'] ?? []),
                    ]);
                }

                // Track synced count per account
                if (!isset($syncedByAccount[$matchingBankAccount->id])) {
                    $syncedByAccount[$matchingBankAccount->id] = 0;
                }
                $syncedByAccount[$matchingBankAccount->id]++;
            }

            // Update sync metadata and balances for all accounts in this Item
            foreach ($allAccountsForItem as $acc) {
                $balance = $this->getAccountBalance($acc);
                
                $updateData = [
                    'last_synced_at' => now(),
                    'status' => 'active',
                    'error_message' => null,
                ];
                
                if ($balance) {
                    $updateData['current_balance'] = $balance['current'];
                    $updateData['available_balance'] = $balance['available'];
                }

                $acc->update($updateData);
            }

            $syncedCount = $syncedByAccount[$bankAccount->id] ?? 0;

            Log::info('Plaid transactions synced', [
                'item_id' => $itemId,
                'tenant_id' => $tenantId,
                'total_transactions' => $totalTransactions,
                'synced_by_account' => $syncedByAccount,
            ]);

            return [
                'success' => true,
                'synced_count' => $syncedCount,
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
     * Deletes all accounts sharing the same Plaid Item and their transactions
     * Note: Disconnecting one account removes all accounts from the same Plaid Item
     */
    public function disconnectAccount(PlaidBankAccount $bankAccount): bool
    {
        try {
            $accessToken = $bankAccount->getDecryptedAccessToken();
            $itemId = $bankAccount->plaid_item_id;
            $tenantId = $bankAccount->tenant_id;

            // Remove access from Plaid (this removes the entire Item, not just one account)
            Http::post("{$this->baseUrl}/item/remove", [
                'client_id' => $this->clientId,
                'secret' => $this->clientSecret,
                'access_token' => $accessToken,
            ]);

            // Find all bank accounts sharing the same plaid_item_id
            $accountsToDelete = PlaidBankAccount::where('plaid_item_id', $itemId)
                ->where('tenant_id', $tenantId)
                ->get();

            // Delete all related transactions for all accounts in this Item
            foreach ($accountsToDelete as $account) {
                PlaidTransaction::where('plaid_bank_account_id', $account->id)->delete();
            }

            // Delete all bank account records for this Item
            foreach ($accountsToDelete as $account) {
                $account->forceDelete();
            }

            Log::info('Plaid Item disconnected - all accounts and transactions deleted', [
                'plaid_item_id' => $itemId,
                'tenant_id' => $tenantId,
                'accounts_deleted' => $accountsToDelete->count(),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Plaid Disconnect Error: ' . $e->getMessage());
            return false;
        }
    }
}

