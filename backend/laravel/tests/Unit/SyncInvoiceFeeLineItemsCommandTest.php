<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SyncInvoiceFeeLineItemsCommandTest extends TestCase
{
    public function test_sync_fee_line_items_command_is_registered(): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey('invoices:sync-fee-line-items', $commands);
    }
}
