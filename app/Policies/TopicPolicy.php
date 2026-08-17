<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;

final readonly class TopicPolicy
{
    /**
     * Determine whether the user can view the topic.
     */
    public function view(?User $user, Topic $topic): bool
    {
        return $topic->is_active;
    }

    /**
     * Determine whether the user can follow the topic.
     */
    public function follow(User $user, Topic $topic): bool
    {
        return $topic->is_active && ! $topic->is_system;
    }
}
