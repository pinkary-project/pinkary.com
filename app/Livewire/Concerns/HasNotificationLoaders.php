<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Question;
use App\Models\User;
use App\Notifications\UserFollowed;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

/**
 * Shared notification data loaders used by both the Livewire notification
 * component and the API NotificationController. Extracted here to avoid
 * duplicating the batch-query logic across both callers.
 */
trait HasNotificationLoaders
{
    /**
     * Load the questions referenced by the given notifications in a single query.
     *
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

        if ($ids === []) {
            return collect();
        }

        return Question::query()
            ->whereIn('id', $ids)
            ->with(['from:id,name,username,avatar,is_verified,is_company_verified', 'to:id,name,username,avatar,is_verified,is_company_verified', 'parent:id,parent_id,content,from_id,to_id'])
            ->get()
            ->keyBy('id');
    }

    /**
     * Load the followers referenced by the given notifications in a single query.
     *
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

        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->select('id', 'name', 'username', 'avatar', 'is_verified', 'is_company_verified')
            ->get()
            ->keyBy('id');
    }

    /**
     * Extract the question ID from a notification's data payload.
     */
    protected function questionIdFrom(DatabaseNotification $notification): ?string
    {
        $id = $notification->data['question_id'] ?? null;

        return is_string($id) ? $id : null;
    }
}
