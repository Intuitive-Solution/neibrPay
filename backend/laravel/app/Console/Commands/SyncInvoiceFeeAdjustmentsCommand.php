<?php

namespace App\Console\Commands;

use App\Services\InvoiceFeeLineItemSyncService;
use Illuminate\Console\Command;

class SyncInvoiceFeeAdjustmentsCommand extends Command
{
    protected $signature = 'invoices:sync-fee-adjustments';

    protected $description = 'Sync late-fee adjustment line items for due invoices';

    public function __construct(
        private readonly InvoiceFeeLineItemSyncService $syncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = $this->syncService->run();

        $this->info('Invoice fee adjustment sync complete');
        $this->table(
            ['Metric', 'Value'],
            [
                ['invoices_seen', $result['invoices_seen']],
                ['invoices_updated', $result['invoices_updated']],
                ['late_fee_first_insert_email_attempted', $result['late_fee_first_insert_email_attempted']],
                ['late_fee_first_insert_email_http_success', $result['late_fee_first_insert_email_http_success']],
                ['pdfs_regenerated', $result['pdfs_regenerated']],
            ]
        );

        return self::SUCCESS;
    }
}
