<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloseExpiredPollsCommandTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.n8n.webhook_url' => 'https://n8n.example.test/webhook']);

        $this->tenant = Tenant::create([
            'name' => 'Maple Court HOA',
            'slug' => 'maple-court',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Board Admin',
            'email' => 'admin@example.test',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_expired_poll_is_closed_and_audience_is_emailed(): void
    {
        Http::fake();

        $resident = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Resident',
            'email' => 'owner@example.test',
            'role' => 'resident',
            'is_active' => true,
        ]);

        Unit::create([
            'tenant_id' => $this->tenant->id,
            'title' => '12 Maple Court',
            'address' => '12 Maple Court',
            'city' => 'Atlanta',
            'state' => 'GA',
            'zip_code' => '30303',
            'is_active' => true,
        ])->owners()->attach($resident->id, ['type' => 'owner']);

        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', [
                'title' => 'Movie night',
                'description' => 'Pick a date',
                'status' => 'open',
                'results_visibility' => 'after_close',
                'closes_at' => now()->subHour()->toIso8601String(),
                'questions' => [[
                    'prompt' => 'Which date?',
                    'type' => 'single_choice',
                    'options' => [
                        ['label' => 'Friday'],
                        ['label' => 'Saturday'],
                    ],
                ]],
                'recipients' => [
                    ['recipient_type' => 'all_units'],
                ],
            ])
            ->assertCreated()
            ->json('data');

        $this->assertSame('open', Poll::find($poll['id'])->status);

        $this->artisan('polls:close-expired')->assertSuccessful();

        $closed = Poll::find($poll['id']);
        $this->assertSame(Poll::STATUS_CLOSED, $closed->status);

        Http::assertSent(function ($request) use ($poll) {
            $body = $request->data();

            return $request->url() === 'https://n8n.example.test/webhook'
                && ($body['type'] ?? null) === 'poll_closed'
                && ($body['poll']['id'] ?? null) === $poll['id']
                && str_contains($body['poll_url'] ?? '', '/my-polls?poll='.$poll['id']);
        });
    }

    public function test_expired_admins_only_poll_closes_without_email(): void
    {
        Http::fake();

        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', [
                'title' => 'Internal board poll',
                'status' => 'open',
                'results_visibility' => 'admins_only',
                'closes_at' => now()->subHour()->toIso8601String(),
                'questions' => [[
                    'prompt' => 'Approve minutes?',
                    'type' => 'yes_no',
                    'options' => [
                        ['label' => 'Yes'],
                        ['label' => 'No'],
                    ],
                ]],
                'recipients' => [
                    ['recipient_type' => 'all_units'],
                ],
            ])
            ->assertCreated()
            ->json('data');

        $this->artisan('polls:close-expired')->assertSuccessful();

        $this->assertSame(Poll::STATUS_CLOSED, Poll::find($poll['id'])->status);
        Http::assertNothingSent();
    }
}
