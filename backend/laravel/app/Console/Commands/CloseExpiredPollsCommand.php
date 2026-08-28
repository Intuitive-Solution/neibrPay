<?php

namespace App\Console\Commands;

use App\Models\Poll;
use Illuminate\Console\Command;

class CloseExpiredPollsCommand extends Command
{
    protected $signature = 'polls:close-expired';

    protected $description = 'Transition open polls to closed once their close date has passed';

    public function handle(): int
    {
        $expired = Poll::open()
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', now())
            ->get();

        foreach ($expired as $poll) {
            $poll->update(['status' => Poll::STATUS_CLOSED]);
        }

        $this->info("Closed {$expired->count()} expired poll(s)");

        return self::SUCCESS;
    }
}
