<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Feed;
use App\Models\User;

final readonly class FeedPolicy
{
    /**
     * Determine whether the user can view the feed.
     */
    public function view(?User $user, Feed $feed): bool
    {
        return $feed->isPublic() || $feed->user_id === $user?->id;
    }

    /**
     * Determine whether the user can create a feed.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the feed.
     */
    public function update(User $user, Feed $feed): bool
    {
        return $user->id === $feed->user_id;
    }

    /**
     * Determine whether the user can delete the feed.
     */
    public function delete(User $user, Feed $feed): bool
    {
        return $user->id === $feed->user_id;
    }

    /**
     * Determine whether the user can follow the feed.
     */
    public function follow(User $user, Feed $feed): bool
    {
        return $feed->isPublic() && $feed->user_id !== $user->id;
    }
}
