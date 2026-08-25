<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Question;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\DatabaseNotification;

final class DeleteOrphanNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DatabaseNotification::query()
            ->whereNotNull('data->question_id')
            ->whereNotIn('data->question_id', Question::query()->select('id'))
            ->eachById(fn (DatabaseNotification $notification): bool => (bool) $notification->delete());
    }
}
