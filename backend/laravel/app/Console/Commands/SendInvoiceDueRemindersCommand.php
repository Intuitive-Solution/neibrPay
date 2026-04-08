<?php

namespace App\Console\Commands;

use App\Services\InvoiceReminderService;
use Illuminate\Console\Command;

class SendInvoiceDueRemindersCommand extends Command
{
    protected $signature = 'invoices:send-due-reminders';

    protected $description = 'Send tenant-configured invoice due reminders';

    public function __construct(
        private readonly InvoiceReminderService $reminderService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = $this->reminderService->run();

        $this->info('Invoice reminders run complete');
        $this->table(
            ['Metric', 'Value'],
            [
                ['sent_count', $result['sent_count']],
                ['skipped_duplicate_count', $result['skipped_duplicate_count']],
                ['skipped_ineligible_count', $result['skipped_ineligible_count']],
                ['failed_count', $result['failed_count']],
            ]
        );

        return self::SUCCESS;
    }
}

