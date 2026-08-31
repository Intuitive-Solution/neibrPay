<?php

namespace App\Console\Commands;

use App\Models\Poll;
use App\Services\PollNotificationService;
use Illuminate\Console\Command;

class CloseExpiredPollsCommand extends Command
{
    protected $signature = 'polls:close-expired';

    protected $description = 'Transition open polls to closed once their close date has passed';

    public function handle(PollNotificationService $pollNotifications): int
    {
        $expired = Poll::open()
            ->with(['questions.options', 'recipients.unit', 'tenant'])
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', now())
            ->get();

        $emailed = 0;

        foreach ($expired as $poll) {
            $poll->update(['status' => Poll::STATUS_CLOSED]);
            $poll->refresh();

            if ($poll->resultsVisibleToResidents()) {
                $pollNotifications->send($poll, 'poll_closed');
                $emailed++;
            }
        }

        $this->info(
            "Closed {$expired->count()} expired poll(s); emailed audience for {$emailed}."
        );

        return self::SUCCESS;
    }
}
