<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final readonly class CommentPolicy
{
    use HandlesAuthorization;

    public function create(?User $user): bool
    {
        return $user?->id !== null;
    }

    public function update(?User $user, Comment $comment): bool
    {
        return $user?->id === $comment->user_id;
    }

    public function delete(?User $user, Comment $comment): bool
    {
        return $user?->id === $comment->user_id;
    }
}
