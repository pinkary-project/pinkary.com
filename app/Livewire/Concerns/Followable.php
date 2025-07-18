<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\User;
use Livewire\Attributes\Renderless;

trait Followable
{
    /**
     * Follows the given user.
     */
    #[Renderless]
    public function follow(int $id): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user->following()->attach($id);

        if ($this->shouldHandleFollowingCount()) {
            $this->dispatch('following.updated');
        }

        $this->dispatch('user.followed', id: $id);
    }

    /**
     * Unfollows the given user.
     */
    #[Renderless]
    public function unfollow(int $id): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user->following()->detach($id);

        if ($this->shouldHandleFollowingCount()) {
            $this->dispatch('following.updated');
        }

        $this->dispatch('user.unfollowed', id: $id);
    }

    /**
     * Indicates if the following count should be handled.
     */
    protected function shouldHandleFollowingCount(): bool
    {
        return false;
    }
}
