<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

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

    private function makeUnit(string $title, ?User $owner = null): Unit
    {
        $unit = Unit::create([
            'tenant_id' => $this->tenant->id,
            'title' => $title,
            'address' => "{$title} Maple Ct",
            'city' => 'Atlanta',
            'state' => 'GA',
            'zip_code' => '30303',
            'is_active' => true,
        ]);

        if ($owner) {
            $unit->owners()->attach($owner->id, ['type' => 'owner']);
        }

        return $unit;
    }

    private function makeResident(string $email): User
    {
        return User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Resident '.$email,
            'email' => $email,
            'role' => 'resident',
            'is_active' => true,
        ]);
    }

    private function pollPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Summer amenities',
            'description' => 'Two things the board needs your read on.',
            'status' => 'open',
            'results_visibility' => 'live',
            'questions' => [
                [
                    'prompt' => 'Which pool schedule do you prefer?',
                    'type' => 'single_choice',
                    'options' => [
                        ['label' => '7am - 9pm daily'],
                        ['label' => '9am - 8pm daily'],
                    ],
                ],
                [
                    'prompt' => 'Which upgrades should we budget for?',
                    'type' => 'multi_select',
                    'options' => [
                        ['label' => 'New grills'],
                        ['label' => 'Shade sails'],
                        ['label' => 'Pool heater'],
                    ],
                ],
            ],
            'recipients' => [
                ['recipient_type' => 'all_units'],
            ],
        ], $overrides);
    }

    public function test_admin_can_create_a_poll_with_multiple_questions(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload());

        $response->assertCreated()
            ->assertJsonCount(2, 'data.questions')
            ->assertJsonPath('data.questions.0.type', 'single_choice')
            ->assertJsonPath('data.questions.1.type', 'multi_select')
            ->assertJsonCount(3, 'data.questions.1.options');

        // sort_order preserves the order the admin entered them in
        $this->assertSame(0, $response->json('data.questions.0.sort_order'));
        $this->assertSame(1, $response->json('data.questions.1.sort_order'));
    }

    public function test_poll_requires_at_least_one_question(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload(['questions' => []]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('questions');
    }

    public function test_resident_votes_once_per_unit_across_every_question(): void
    {
        $resident = $this->makeResident('owner@example.test');
        $unit = $this->makeUnit('12', $resident);

        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload())
            ->json('data');

        $singleChoice = $poll['questions'][0];
        $multiSelect = $poll['questions'][1];

        $this->actingAs($resident)
            ->postJson("/api/polls/{$poll['id']}/vote", [
                'answers' => [
                    [
                        'question_id' => $singleChoice['id'],
                        'option_ids' => [$singleChoice['options'][0]['id']],
                    ],
                    [
                        'question_id' => $multiSelect['id'],
                        // multi-select accepts several options on one question
                        'option_ids' => [
                            $multiSelect['options'][0]['id'],
                            $multiSelect['options'][2]['id'],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.has_voted', true);

        $this->assertDatabaseCount('poll_responses', 1);
        $this->assertDatabaseCount('poll_answers', 3);

        // The unit cannot vote a second time
        $this->actingAs($resident)
            ->postJson("/api/polls/{$poll['id']}/vote", [
                'answers' => [[
                    'question_id' => $singleChoice['id'],
                    'option_ids' => [$singleChoice['options'][1]['id']],
                ]],
            ])
            ->assertStatus(409);

        $this->assertDatabaseCount('poll_responses', 1);
        $this->assertSame(1, Poll::find($poll['id'])->responses()->count());
        $this->assertSame($unit->id, Poll::find($poll['id'])->responses()->first()->unit_id);
    }

    public function test_vote_is_rejected_when_a_question_is_left_unanswered(): void
    {
        $resident = $this->makeResident('owner2@example.test');
        $this->makeUnit('14', $resident);

        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload())
            ->json('data');

        $this->actingAs($resident)
            ->postJson("/api/polls/{$poll['id']}/vote", [
                'answers' => [[
                    'question_id' => $poll['questions'][0]['id'],
                    'option_ids' => [$poll['questions'][0]['options'][0]['id']],
                ]],
            ])
            ->assertStatus(422)
            ->assertJsonPath('unanswered_question_ids.0', $poll['questions'][1]['id']);

        $this->assertDatabaseCount('poll_responses', 0);
    }

    public function test_single_choice_question_rejects_multiple_options(): void
    {
        $resident = $this->makeResident('owner3@example.test');
        $this->makeUnit('16', $resident);

        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload())
            ->json('data');

        $singleChoice = $poll['questions'][0];

        $this->actingAs($resident)
            ->postJson("/api/polls/{$poll['id']}/vote", [
                'answers' => [
                    [
                        'question_id' => $singleChoice['id'],
                        'option_ids' => [
                            $singleChoice['options'][0]['id'],
                            $singleChoice['options'][1]['id'],
                        ],
                    ],
                    [
                        'question_id' => $poll['questions'][1]['id'],
                        'option_ids' => [$poll['questions'][1]['options'][0]['id']],
                    ],
                ],
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('poll_responses', 0);
    }

    public function test_tally_reports_each_question_separately(): void
    {
        $residentA = $this->makeResident('a@example.test');
        $residentB = $this->makeResident('b@example.test');
        $this->makeUnit('12', $residentA);
        $this->makeUnit('14', $residentB);

        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload())
            ->json('data');

        $single = $poll['questions'][0];
        $multi = $poll['questions'][1];

        foreach ([$residentA, $residentB] as $resident) {
            $this->actingAs($resident)
                ->postJson("/api/polls/{$poll['id']}/vote", [
                    'answers' => [
                        [
                            'question_id' => $single['id'],
                            'option_ids' => [$single['options'][0]['id']],
                        ],
                        [
                            'question_id' => $multi['id'],
                            'option_ids' => [
                                $multi['options'][0]['id'],
                                $multi['options'][1]['id'],
                            ],
                        ],
                    ],
                ])
                ->assertCreated();
        }

        $detail = $this->actingAs($this->admin)
            ->getJson("/api/polls/{$poll['id']}")
            ->assertOk();

        $detail->assertJsonCount(2, 'data.results.questions')
            // single choice: both units picked option 1
            ->assertJsonPath('data.results.questions.0.total_votes', 2)
            ->assertJsonPath('data.results.questions.0.units_answered', 2)
            ->assertJsonPath('data.results.questions.0.options.0.votes', 2)
            ->assertJsonPath('data.results.questions.0.options.0.percentage', 100)
            // multi-select: 2 units x 2 selections = 4 selections, still 2 units
            ->assertJsonPath('data.results.questions.1.total_votes', 4)
            ->assertJsonPath('data.results.questions.1.units_answered', 2)
            ->assertJsonPath('data.results.questions.1.options.2.votes', 0);

        // The roster reports participation, never an answer
        $detail->assertJsonCount(2, 'data.participation')
            ->assertJsonPath('data.participation.0.has_voted', true);

        $participation = $detail->json('data.participation.0');
        $this->assertArrayNotHasKey('poll_option_id', $participation);
        $this->assertArrayNotHasKey('responded_by', $participation);
    }

    public function test_admin_can_save_an_incomplete_draft(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload([
                'status' => 'draft',
                'questions' => [
                    [
                        'prompt' => '',
                        'type' => 'single_choice',
                        'options' => [
                            ['label' => ''],
                            ['label' => ''],
                        ],
                    ],
                ],
            ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.title', 'Summer amenities');
    }

    public function test_incomplete_draft_cannot_be_published(): void
    {
        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload([
                'status' => 'draft',
                'questions' => [
                    [
                        'prompt' => '',
                        'type' => 'single_choice',
                        'options' => [
                            ['label' => ''],
                            ['label' => ''],
                        ],
                    ],
                ],
            ]))
            ->json('data');

        $this->actingAs($this->admin)
            ->postJson("/api/polls/{$poll['id']}/publish")
            ->assertStatus(422);
    }

    public function test_open_poll_without_votes_can_return_to_draft(): void
    {
        $poll = $this->actingAs($this->admin)
            ->postJson('/api/polls', $this->pollPayload())
            ->json('data');

        $this->actingAs($this->admin)
            ->putJson("/api/polls/{$poll['id']}", $this->pollPayload([
                'status' => 'draft',
                'title' => 'Pulled back',
            ]))
            ->assertOk()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.title', 'Pulled back');
    }
}
