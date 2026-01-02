<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync Plaid transactions weekly (optimized for low-activity accounts)
        // Runs every Monday at 00:00 UTC
        // This allows time for all pending transactions to post (typically 1-3 days)
        $schedule->command('plaid:sync')
            ->weekly()
            ->mondays()
            ->timezone('UTC')
            ->withoutOverlapping()
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Plaid sync job failed');
            })
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Plaid sync job completed successfully');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
