<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollNotificationService
{
    /**
     * Notify poll audience via n8n (publish, close, reminder).
     */
    public function send(
        Poll $poll,
        string $event,
        ?User $actor = null,
        ?array $limitToUnitIds = null
    ): void {
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
            // Query param (not hash) so auth redirect can preserve the full path.
            $pollUrl = $frontendUrl.'/my-polls?poll='.$poll->id;

            $tenant = $actor?->tenant ?? $poll->tenant;
            $tenantName = $tenant->name ?? 'HOA';

            $payload = [
                'type' => $event, // 'poll_published' | 'poll_closed' | 'poll_reminder'
                'tenant_name' => $tenantName,
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
                    'target_unit_count' => $poll->targetUnitIds()->count(),
                    'responded_unit_count' => $poll->responses()->count(),
                ],
                'tenant' => [
                    'id' => $poll->tenant_id,
                    'name' => $tenantName,
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
     *
     * @return list<string>
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
