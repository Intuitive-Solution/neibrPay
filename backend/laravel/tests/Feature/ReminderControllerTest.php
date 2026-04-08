<?php

namespace Tests\Feature;

use App\Services\InvoiceReminderService;
use Mockery;
use Tests\TestCase;

class ReminderControllerTest extends TestCase
{
    public function test_run_endpoint_requires_valid_api_key(): void
    {
        config(['services.n8n.scheduler_api_key' => 'expected-key']);

        $response = $this->postJson('/api/reminders/invoices/run');
        $response->assertStatus(401);
    }

    public function test_run_endpoint_rejects_wrong_api_key(): void
    {
        config(['services.n8n.scheduler_api_key' => 'expected-key']);

        $response = $this->postJson('/api/reminders/invoices/run', [], [
            'X-N8N-API-Key' => 'wrong-key',
        ]);
        $response->assertStatus(401);
    }

    public function test_run_endpoint_returns_structured_counters(): void
    {
        config(['services.n8n.scheduler_api_key' => 'expected-key']);

        $service = Mockery::mock(InvoiceReminderService::class);
        $service->shouldReceive('run')->once()->andReturn([
            'sent_count' => 2,
            'skipped_duplicate_count' => 1,
            'skipped_ineligible_count' => 5,
            'failed_count' => 0,
        ]);
        $this->app->instance(InvoiceReminderService::class, $service);

        $response = $this->postJson('/api/reminders/invoices/run', [], [
            'X-N8N-API-Key' => 'expected-key',
        ]);

        $response->assertOk()
            ->assertJsonPath('results.sent_count', 2)
            ->assertJsonPath('results.skipped_duplicate_count', 1)
            ->assertJsonPath('results.failed_count', 0);
    }
}

