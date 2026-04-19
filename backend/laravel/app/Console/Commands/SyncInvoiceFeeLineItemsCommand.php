<?php

namespace App\Console\Commands;

use App\Services\InvoiceFeeLineItemSyncService;
use Illuminate\Console\Command;

class SyncInvoiceFeeLineItemsCommand extends Command
{
    protected $signature = 'invoices:sync-fee-line-items';

    protected $description = 'Sync late fee and early payment discount invoice line items (scheduled daily)';

    public function handle(InvoiceFeeLineItemSyncService $syncService): int
    {
        $result = $syncService->run();

        $this->info('Invoice fee line item sync complete');
        $rows = [];
        foreach ($result as $metric => $value) {
            $rows[] = [$metric, $value];
        }
        $this->table(['Metric', 'Value'], $rows);

        return self::SUCCESS;
    }
}
