<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Models\User;

trait CanFollow
{
    /**
     * Follow the given user.
     */
    public function follow(int $targetId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type(auth()->user())->as(User::class);

        $target = User::findOrFail($targetId);

        $this->authorize('follow', $target);

        if ($target->followers()->where('follower_id', $user->id)->exists()) {
            return;
        }

        $user->following()->attach($targetId);

        $this->dispatch('user.followed');
    }

    /**
     * Unfollow the given user.
     */
    public function unfollow(int $targetId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type(auth()->user())->as(User::class);

        $target = User::findOrFail($targetId);

        $this->authorize('unfollow', $target);

        if ($target->followers()->where('follower_id', $user->id)->doesntExist()) {
            return;
        }

        $user->following()->detach($targetId);

        $this->dispatch('user.unfollowed');
    }
}
