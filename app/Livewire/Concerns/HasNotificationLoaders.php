<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Question;
use App\Models\User;
use App\Notifications\UserFollowed;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

trait HasNotificationLoaders
{
    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return Collection<string, Question>
     */
    protected function questionsFor(Collection $notifications): Collection
    {
        /** @var list<string> $ids */
        $ids = $notifications
            ->map(fn (DatabaseNotification $notification): ?string => $this->questionIdFrom($notification))
            ->filter()
            ->unique()
            ->values()
            ->all();

        /** @var Collection<string, Question> $questions */
        $questions = $ids === []
            ? new Collection()
            : Question::query()
                ->whereIn('id', $ids)
                ->with(['from', 'to', 'parent'])
                ->get()
                ->keyBy('id');

        return $questions;
    }

    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return Collection<int, User>
     */
    protected function followersFor(Collection $notifications): Collection
    {
        /** @var list<int> $ids */
        $ids = $notifications
            ->filter(fn (DatabaseNotification $notification): bool => $notification->type === UserFollowed::class)
            ->map(fn (DatabaseNotification $notification): mixed => $notification->data['follower_id'] ?? null)
            ->filter(fn (mixed $id): bool => is_int($id))
            ->unique()
            ->values()
            ->all();

        /** @var Collection<int, User> $followers */
        $followers = $ids === []
            ? new Collection()
            : User::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

        return $followers;
    }

    /** A notification's subject id, or null when the payload carries none. */
    protected function questionIdFrom(DatabaseNotification $notification): ?string
    {
        $id = $notification->data['question_id'] ?? null;

        return is_string($id) ? $id : null;
    }
}
