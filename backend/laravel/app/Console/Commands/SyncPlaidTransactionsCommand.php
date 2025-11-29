<?php

namespace App\Console\Commands;

use App\Jobs\SyncPlaidTransactions;
use Illuminate\Console\Command;

class SyncPlaidTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plaid:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all active Plaid bank accounts and fetch transactions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching Plaid sync job...');

        // Dispatch the job to the queue
        dispatch(new SyncPlaidTransactions());

        $this->info('Plaid sync job dispatched successfully!');

        return self::SUCCESS;
    }
}




