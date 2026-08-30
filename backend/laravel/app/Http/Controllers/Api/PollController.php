<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollRequest;
use App\Models\Poll;
use App\Models\PollAnswer;
use App\Models\PollOption;
use App\Models\PollQuestion;
use App\Models\PollRecipient;
use App\Models\PollResponse;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollController extends Controller
{
    /**
     * Display a listing of polls (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $status = $request->get('status'); // 'draft', 'open', 'closed', or null for all

        $query = Poll::forTenant($user->tenant_id)
            ->with(['creator', 'questions.options', 'recipients.unit']);

        if (in_array($status, [Poll::STATUS_DRAFT, Poll::STATUS_OPEN, Poll::STATUS_CLOSED], true)) {
            $query->where('status', $status);
        }

        $polls = $query->orderBy('created_at', 'desc')->get();

        $data = $polls->map(fn (Poll $poll) => $this->summarize($poll))->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'status' => $status,
                'open_count' => $polls->where('status', Poll::STATUS_OPEN)->count(),
                'unit_count' => Unit::forTenant($user->tenant_id)->where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * Store a newly created poll (admin only).
     */
    public function store(PollRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        $poll = DB::transaction(function () use ($validated, $user) {
            $status = $validated['status'];

            $poll = Poll::create([
                'tenant_id' => $user->tenant_id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $status,
                'opens_at' => $validated['opens_at'] ?? ($status === Poll::STATUS_OPEN ? now() : null),
                'closes_at' => $validated['closes_at'] ?? null,
                'results_visibility' => $validated['results_visibility'],
                'anonymous_responses' => true,
                'created_by' => $user->id,
            ]);

            $this->writeQuestions($poll, $validated['questions']);
            $this->writeRecipients($poll, $validated['recipients']);

            return $poll;
        });

        $poll->load(['creator', 'questions.options', 'recipients.unit']);

        // Only notify once the poll is actually live
        if ($poll->status === Poll::STATUS_OPEN) {
            $this->sendN8nWebhook($poll, $user, 'poll_published');
        }

        return response()->json([
            'data' => $this->summarize($poll),
            'message' => 'Poll created successfully',
        ], 201);
    }

    /**
     * Display the specified poll with tallies and participation (admin only).
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $poll = Poll::forTenant($user->tenant_id)
            ->with(['creator', 'questions.options', 'recipients.unit'])
            ->findOrFail($id);

        $payload = $this->summarize($poll);
        $payload['results'] = $this->tally($poll);
        $payload['participation'] = $this->participation($poll);

        return response()->json(['data' => $payload]);
    }

    /**
     * Update the specified poll (admin only).
     *
     * Drafts can be edited freely. Once a poll is open the questions and their
     * options are frozen as soon as any vote has been cast.
     */
    public function update(PollRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $poll = Poll::forTenant($user->tenant_id)->findOrFail($id);

        if ($poll->status === Poll::STATUS_CLOSED) {
            return response()->json(['message' => 'A closed poll can no longer be edited'], 422);
        }

        $validated = $request->validated();
        $hasVotes = $poll->responses()->exists();
        $nextStatus = $validated['status'];

        // An open poll can return to draft only before anyone has voted
        if ($poll->status === Poll::STATUS_OPEN && $nextStatus === Poll::STATUS_DRAFT) {
            if ($hasVotes) {
                return response()->json([
                    'message' => 'This poll cannot be moved back to draft because votes have already been cast.',
                ], 422);
            }
        } elseif ($poll->status === Poll::STATUS_OPEN) {
            $nextStatus = Poll::STATUS_OPEN;
        }

        $isGoingLive = $poll->status === Poll::STATUS_DRAFT
            && $nextStatus === Poll::STATUS_OPEN;

        DB::transaction(function () use ($poll, $validated, $hasVotes, $nextStatus) {
            $poll->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $nextStatus,
                'opens_at' => $validated['opens_at']
                    ?? ($nextStatus === Poll::STATUS_OPEN ? ($poll->opens_at ?? now()) : $poll->opens_at),
                'closes_at' => $validated['closes_at'] ?? null,
                'results_visibility' => $validated['results_visibility'],
            ]);

            // Rewriting options would orphan cast votes, so only do it before the first vote
            if (!$hasVotes) {
                $poll->questions()->each(function (PollQuestion $question) {
                    $question->options()->delete();
                    $question->delete();
                });
                $this->writeQuestions($poll, $validated['questions']);

                $poll->recipients()->delete();
                $this->writeRecipients($poll, $validated['recipients']);
            }
        });

        $poll->refresh()->load(['creator', 'questions.options', 'recipients.unit']);

        if ($isGoingLive) {
            $this->sendN8nWebhook($poll, $user, 'poll_published');
        }

        return response()->json([
            'data' => $this->summarize($poll),
            'message' => $hasVotes
                ? 'Poll updated. The questions and audience are locked because votes have been cast.'
                : 'Poll updated successfully',
        ]);
    }

    /**
     * Publish a draft poll (admin only).
     */
    public function publish(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $poll = Poll::forTenant($user->tenant_id)
            ->with(['questions.options', 'recipients.unit'])
            ->findOrFail($id);

        if ($poll->status !== Poll::STATUS_DRAFT) {
            return response()->json(['message' => 'Only a draft poll can be published'], 422);
        }

        if (!$poll->isReadyToPublish()) {
            return response()->json([
                'message' => 'This draft is not ready to publish. Add a question with at least two options.',
            ], 422);
        }

        $poll->update([
            'status' => Poll::STATUS_OPEN,
            'opens_at' => $poll->opens_at ?? now(),
        ]);

        $this->sendN8nWebhook($poll, $user, 'poll_published');

        return response()->json([
            'data' => $this->summarize($poll->fresh(['creator', 'questions.options', 'recipients.unit'])),
            'message' => 'Poll published successfully',
        ]);
    }

    /**
     * Close a poll early (admin only).
     */
    public function close(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $poll = Poll::forTenant($user->tenant_id)->findOrFail($id);

        if ($poll->status !== Poll::STATUS_OPEN) {
            return response()->json(['message' => 'Only an open poll can be closed'], 422);
        }

        $poll->update([
            'status' => Poll::STATUS_CLOSED,
            'closes_at' => now(),
        ]);

        $poll->load(['creator', 'questions.options', 'recipients.unit']);

        if ($poll->resultsVisibleToResidents()) {
            $this->sendN8nWebhook($poll, $user, 'poll_closed');
        }

        return response()->json([
            'data' => $this->summarize($poll),
            'message' => 'Poll closed successfully',
        ]);
    }

    /**
     * Nudge the units that have not voted yet (admin only).
     */
    public function remind(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $poll = Poll::forTenant($user->tenant_id)
            ->with(['questions.options', 'recipients.unit'])
            ->findOrFail($id);

        if ($poll->status !== Poll::STATUS_OPEN) {
            return response()->json(['message' => 'Only an open poll can be reminded on'], 422);
        }

        $targetUnitIds = $poll->targetUnitIds();
        $votedUnitIds = $poll->responses()->pluck('unit_id');
        $pendingUnitIds = $targetUnitIds->diff($votedUnitIds)->values();

        $this->sendN8nWebhook($poll, $user, 'poll_reminder', $pendingUnitIds->all());

        return response()->json([
            'message' => 'Reminder sent to '.$pendingUnitIds->count().' unit(s)',
            'meta' => ['reminded_unit_count' => $pendingUnitIds->count()],
        ]);
    }

    /**
     * Remove the specified poll (admin only, soft delete).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $poll = Poll::forTenant($user->tenant_id)->findOrFail($id);
        $poll->delete();

        return response()->json(['message' => 'Poll deleted successfully']);
    }

    /**
     * Get polls the current user's unit(s) are targeted by.
     */
    public function forUser(Request $request): JsonResponse
    {
        $user = $request->user();
        $userUnitIds = $user->ownedUnits()->pluck('units.id');

        $polls = Poll::forTenant($user->tenant_id)
            ->published()
            ->with(['questions.options', 'recipients'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function (Poll $poll) use ($userUnitIds) {
                return $poll->targetUnitIds()->intersect($userUnitIds)->isNotEmpty();
            })
            ->values();

        $data = $polls->map(function (Poll $poll) use ($userUnitIds, $user) {
            return $this->residentPayload($poll, $userUnitIds, $user);
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'open_count' => $data->where('can_vote', true)->count(),
            ],
        ]);
    }

    /**
     * Record a vote for the caller's unit.
     */
    public function vote(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.option_ids' => ['required', 'array', 'min:1'],
            'answers.*.option_ids.*' => ['required', 'integer'],
            'unit_id' => ['nullable', 'integer'],
        ]);

        $poll = Poll::forTenant($user->tenant_id)
            ->with(['questions.options', 'recipients'])
            ->findOrFail($id);

        if (!$poll->isAcceptingVotes()) {
            return response()->json(['message' => 'This poll is not open for voting'], 422);
        }

        $userUnitIds = $user->ownedUnits()->pluck('units.id');
        $eligibleUnitIds = $poll->targetUnitIds()->intersect($userUnitIds)->values();

        if ($eligibleUnitIds->isEmpty()) {
            return response()->json(['message' => 'Your unit is not eligible to vote on this poll'], 403);
        }

        $unitId = $validated['unit_id'] ?? $eligibleUnitIds->first();

        if (!$eligibleUnitIds->contains($unitId)) {
            return response()->json(['message' => 'Your unit is not eligible to vote on this poll'], 403);
        }

        if ($poll->responses()->where('unit_id', $unitId)->exists()) {
            return response()->json(['message' => 'Your unit has already voted on this poll'], 409);
        }

        if ($poll->questions->isEmpty()) {
            return response()->json(['message' => 'This poll has no questions to answer'], 422);
        }

        // question id => the option ids selected for it, validated per question
        $selections = [];

        foreach ($validated['answers'] as $answer) {
            $question = $poll->questions->firstWhere('id', $answer['question_id']);

            if (!$question) {
                return response()->json(['message' => 'That question is not part of this poll'], 422);
            }

            if (isset($selections[$question->id])) {
                return response()->json(['message' => 'A question was answered more than once'], 422);
            }

            $optionIds = collect($answer['option_ids'])->unique()->values();

            if ($optionIds->diff($question->options->pluck('id'))->isNotEmpty()) {
                return response()->json(['message' => 'Invalid option selected'], 422);
            }

            if (!$question->allowsMultipleAnswers() && $optionIds->count() > 1) {
                return response()->json([
                    'message' => "\"{$question->prompt}\" accepts a single answer",
                ], 422);
            }

            $selections[$question->id] = $optionIds;
        }

        // A unit votes on the whole poll at once, so every question must be answered
        $unanswered = $poll->questions->reject(fn (PollQuestion $question) => isset($selections[$question->id]));

        if ($unanswered->isNotEmpty()) {
            return response()->json([
                'message' => 'Please answer every question before submitting.',
                'unanswered_question_ids' => $unanswered->pluck('id')->values(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($poll, $unitId, $user, $selections) {
                $response = PollResponse::create([
                    'poll_id' => $poll->id,
                    'unit_id' => $unitId,
                    'responded_by' => $user->id,
                    'responded_at' => now(),
                ]);

                foreach ($selections as $questionId => $optionIds) {
                    foreach ($optionIds as $optionId) {
                        PollAnswer::create([
                            'poll_response_id' => $response->id,
                            'poll_question_id' => $questionId,
                            'poll_option_id' => $optionId,
                        ]);
                    }
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint on (poll_id, unit_id) - a co-owner beat us to it
            return response()->json(['message' => 'Your unit has already voted on this poll'], 409);
        }

        $poll->refresh()->load(['questions.options', 'recipients']);

        return response()->json([
            'data' => $this->residentPayload($poll, $userUnitIds, $user),
            'message' => 'Your vote has been recorded',
        ], 201);
    }

    /**
     * Get the tally for a poll, if the caller is allowed to see it.
     */
    public function results(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $poll = Poll::forTenant($user->tenant_id)
            ->with(['questions.options', 'recipients'])
            ->findOrFail($id);

        if (!$user->isAdmin()) {
            if ($poll->status === Poll::STATUS_DRAFT || !$poll->resultsVisibleToResidents()) {
                return response()->json(['message' => 'Results are not available yet'], 403);
            }

            $userUnitIds = $user->ownedUnits()->pluck('units.id');
            if ($poll->targetUnitIds()->intersect($userUnitIds)->isEmpty()) {
                return response()->json(['message' => 'Results are not available yet'], 403);
            }
        }

        return response()->json(['data' => $this->tally($poll)]);
    }

    /**
     * Persist the poll's questions and their options, in the order given.
     */
    private function writeQuestions(Poll $poll, array $questions): void
    {
        foreach (array_values($questions) as $questionIndex => $questionData) {
            // ConvertEmptyStringsToNull turns blank draft fields into null;
            // the columns are NOT NULL, so persist empty strings instead.
            $question = PollQuestion::create([
                'poll_id' => $poll->id,
                'prompt' => $questionData['prompt'] ?? '',
                'type' => $questionData['type'],
                'sort_order' => $questionIndex,
            ]);

            foreach (array_values($questionData['options'] ?? []) as $optionIndex => $option) {
                PollOption::create([
                    'poll_question_id' => $question->id,
                    'label' => $option['label'] ?? '',
                    'sort_order' => $optionIndex,
                ]);
            }
        }
    }

    /**
     * Persist the poll's targeting rules.
     */
    private function writeRecipients(Poll $poll, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            PollRecipient::create([
                'poll_id' => $poll->id,
                'recipient_type' => $recipient['recipient_type'],
                'recipient_id' => $recipient['recipient_id'] ?? null,
            ]);
        }
    }

    /**
     * Shape a poll for the admin list/detail payload.
     */
    private function summarize(Poll $poll): array
    {
        $targetUnitIds = $poll->targetUnitIds();

        return [
            'id' => $poll->id,
            'tenant_id' => $poll->tenant_id,
            'title' => $poll->title,
            'description' => $poll->description,
            'status' => $poll->status,
            'opens_at' => $poll->opens_at?->toIso8601String(),
            'closes_at' => $poll->closes_at?->toIso8601String(),
            'results_visibility' => $poll->results_visibility,
            'anonymous_responses' => $poll->anonymous_responses,
            'created_by' => $poll->created_by,
            'created_at' => $poll->created_at?->toIso8601String(),
            'updated_at' => $poll->updated_at?->toIso8601String(),
            'creator' => $poll->relationLoaded('creator') && $poll->creator ? [
                'id' => $poll->creator->id,
                'name' => $poll->creator->name,
                'email' => $poll->creator->email,
            ] : null,
            'questions' => $this->serializeQuestions($poll),
            'recipients' => $poll->recipients->map(fn (PollRecipient $recipient) => [
                'id' => $recipient->id,
                'recipient_type' => $recipient->recipient_type,
                'recipient_id' => $recipient->recipient_id,
                'unit' => $recipient->relationLoaded('unit') && $recipient->unit ? [
                    'id' => $recipient->unit->id,
                    'title' => $recipient->unit->title,
                    'address' => $recipient->unit->address,
                ] : null,
            ])->values(),
            'target_unit_count' => $targetUnitIds->count(),
            'responded_unit_count' => $poll->responses()->count(),
        ];
    }

    /**
     * Shape the poll's questions and their options for an API payload.
     */
    private function serializeQuestions(Poll $poll): array
    {
        return $poll->questions->map(fn (PollQuestion $question) => [
            'id' => $question->id,
            'prompt' => $question->prompt,
            'type' => $question->type,
            'sort_order' => $question->sort_order,
            'options' => $question->options->map(fn (PollOption $option) => [
                'id' => $option->id,
                'label' => $option->label,
                'sort_order' => $option->sort_order,
            ])->values()->all(),
        ])->values()->all();
    }

    /**
     * Compute the tally, one block per question.
     *
     * Deliberately grouped by question and option only - unit_id and
     * responded_by never appear in this result set, so a tally cannot be
     * traced back to a unit.
     */
    private function tally(Poll $poll): array
    {
        $questionIds = $poll->questions->pluck('id');

        if ($questionIds->isEmpty()) {
            return ['questions' => []];
        }

        $counts = PollAnswer::whereIn('poll_question_id', $questionIds)
            ->select('poll_question_id', 'poll_option_id', DB::raw('COUNT(*) as votes'))
            ->groupBy('poll_question_id', 'poll_option_id')
            ->get()
            ->groupBy('poll_question_id');

        // How many distinct responses touched each question - for multi-select
        // this differs from the sum of the per-option counts
        $unitsAnswered = PollAnswer::whereIn('poll_question_id', $questionIds)
            ->select('poll_question_id', DB::raw('COUNT(DISTINCT poll_response_id) as units'))
            ->groupBy('poll_question_id')
            ->pluck('units', 'poll_question_id');

        return [
            'questions' => $poll->questions->map(function (PollQuestion $question) use ($counts, $unitsAnswered) {
                $optionVotes = ($counts[$question->id] ?? collect())
                    ->pluck('votes', 'poll_option_id');

                $totalVotes = (int) $optionVotes->sum();

                return [
                    'question_id' => $question->id,
                    'prompt' => $question->prompt,
                    'type' => $question->type,
                    'total_votes' => $totalVotes,
                    'units_answered' => (int) ($unitsAnswered[$question->id] ?? 0),
                    'options' => $question->options->map(function (PollOption $option) use ($optionVotes, $totalVotes) {
                        $votes = (int) ($optionVotes[$option->id] ?? 0);

                        return [
                            'id' => $option->id,
                            'label' => $option->label,
                            'votes' => $votes,
                            'percentage' => $totalVotes > 0 ? round($votes / $totalVotes * 100) : 0,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    /**
     * The participation roster: which units have voted, never what they chose.
     */
    private function participation(Poll $poll): array
    {
        $targetUnitIds = $poll->targetUnitIds();

        $units = Unit::forTenant($poll->tenant_id)
            ->whereIn('id', $targetUnitIds)
            ->with('owners')
            ->orderBy('title')
            ->get();

        $respondedAt = $poll->responses()
            ->pluck('responded_at', 'unit_id');

        return $units->map(function (Unit $unit) use ($respondedAt) {
            $votedAt = $respondedAt[$unit->id] ?? null;

            return [
                'unit_id' => $unit->id,
                'unit_title' => $unit->title,
                'unit_address' => $unit->address,
                'owner_names' => $unit->owners->pluck('name')->values(),
                'has_voted' => $votedAt !== null,
                'responded_at' => $votedAt ? \Carbon\Carbon::parse($votedAt)->toIso8601String() : null,
            ];
        })->values()->all();
    }

    /**
     * Shape a poll for a resident, including their unit's voting state.
     */
    private function residentPayload(Poll $poll, $userUnitIds, User $user): array
    {
        $targetUnitIds = $poll->targetUnitIds();
        $eligibleUnitIds = $targetUnitIds->intersect($userUnitIds)->values();

        $response = $poll->responses()
            ->whereIn('unit_id', $eligibleUnitIds)
            ->with('respondent')
            ->first();

        $resultsVisible = $poll->resultsVisibleToResidents();

        return [
            'id' => $poll->id,
            'title' => $poll->title,
            'description' => $poll->description,
            'status' => $poll->status,
            'opens_at' => $poll->opens_at?->toIso8601String(),
            'closes_at' => $poll->closes_at?->toIso8601String(),
            'results_visibility' => $poll->results_visibility,
            'unit_id' => $eligibleUnitIds->first(),
            'questions' => $this->serializeQuestions($poll),
            'has_voted' => $response !== null,
            'voted_at' => $response?->responded_at?->toIso8601String(),
            // Named so a co-owner sees "already voted by your unit" rather than a form
            'voted_by_me' => $response !== null && $response->responded_by === $user->id,
            'voted_by_name' => $response?->respondent?->name,
            'can_vote' => $response === null && $poll->isAcceptingVotes(),
            'results_visible' => $resultsVisible,
            'results' => $resultsVisible ? $this->tally($poll) : null,
            'target_unit_count' => $targetUnitIds->count(),
            'responded_unit_count' => $poll->responses()->count(),
        ];
    }

    /**
     * Send webhook to n8n for notifications.
     */
    private function sendN8nWebhook(Poll $poll, User $user, string $event, ?array $limitToUnitIds = null): void
    {
        $webhookUrl = config('services.n8n.webhook_url');

        if (!$webhookUrl) {
            Log::warning('N8N webhook URL not configured. Skipping notification.');
            return;
        }

        try {
            $recipientEmails = $this->collectRecipientEmails($poll, $limitToUnitIds);

            if ($recipientEmails === []) {
                Log::warning('N8N poll notification skipped: no audience emails', [
                    'poll_id' => $poll->id,
                    'event' => $event,
                ]);
                return;
            }

            $toEmail = $recipientEmails[0];
            $bccEmails = array_values(array_filter(
                $recipientEmails,
                fn ($email) => $email !== $toEmail
            ));

            $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
            $loginUrl = $frontendUrl.'/auth';
            $pollUrl = $frontendUrl.'/my-polls';

            $payload = [
                'type' => $event, // 'poll_published' | 'poll_closed' | 'poll_reminder'
                'tenant_name' => $user->tenant->name ?? 'HOA',
                'to' => $toEmail,
                'bcc' => $bccEmails,
                'frontend_url' => $frontendUrl,
                'login_url' => $loginUrl,
                'poll_url' => $pollUrl,
                'poll' => [
                    'id' => $poll->id,
                    'title' => $poll->title,
                    'description' => $poll->description,
                    'status' => $poll->status,
                    'closes_at' => $poll->closes_at?->toIso8601String(),
                    'question_count' => $poll->questions->count(),
                    'first_question' => $poll->questions->first()?->prompt,
                ],
                'tenant' => [
                    'id' => $user->tenant_id,
                    'name' => $user->tenant->name ?? null,
                ],
                'recipients' => $recipientEmails,
            ];

            Http::timeout(10)->post($webhookUrl, $payload);

            Log::info('N8N webhook sent successfully for poll', [
                'poll_id' => $poll->id,
                'event' => $event,
                'recipient_count' => count($recipientEmails),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send N8N webhook for poll', [
                'poll_id' => $poll->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - webhook failure shouldn't block the poll action
        }
    }

    /**
     * Collect the owner emails of the units this poll targets.
     */
    private function collectRecipientEmails(Poll $poll, ?array $limitToUnitIds = null): array
    {
        $unitIds = $poll->targetUnitIds();

        if ($limitToUnitIds !== null) {
            $unitIds = $unitIds->intersect($limitToUnitIds);
        }

        if ($unitIds->isEmpty()) {
            return [];
        }

        return Unit::forTenant($poll->tenant_id)
            ->whereIn('id', $unitIds)
            ->with(['owners' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->flatMap(fn (Unit $unit) => $unit->owners->pluck('email'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
