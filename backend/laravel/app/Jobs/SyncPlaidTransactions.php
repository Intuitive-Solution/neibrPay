<?php

namespace App\Jobs;

use App\Models\PlaidBankAccount;
use App\Services\PlaidService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPlaidTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PlaidService $plaidService): void
    {
        try {
            Log::info('Starting Plaid transaction sync job');

            // Get all active bank accounts
            $accounts = PlaidBankAccount::where('status', 'active')->get();

            if ($accounts->isEmpty()) {
                Log::info('No active Plaid bank accounts to sync');
                return;
            }

            $successCount = 0;
            $errorCount = 0;

            // Sync each account
            foreach ($accounts as $account) {
                try {
                    Log::info("Syncing transactions for account: {$account->id}");
                    
                    $result = $plaidService->syncTransactions($account);
                    
                    Log::info("Successfully synced {$result['synced_count']} transactions for account {$account->id}");
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to sync account {$account->id}: " . $e->getMessage());
                    $errorCount++;

                    // Update account status to error
                    $account->update([
                        'status' => 'error',
                        'error_message' => substr($e->getMessage(), 0, 255),
                    ]);
                }
            }

            Log::info("Plaid sync job completed", [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_accounts' => $accounts->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Plaid sync job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

