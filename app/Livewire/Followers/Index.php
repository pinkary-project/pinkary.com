<?php

declare(strict_types=1);

namespace App\Livewire\Followers;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use WithPagination;

    /**
     * The component's user ID.
     */
    #[Locked]
    public int $userId;

    /**
     * Indicate whether the user's followers should be loaded.
     */
    #[Locked]
    public bool $loadFollowers = false;

    #[On('openFollowersModal')]
    public function openFollowersModal(): void
    {
        $this->loadFollowers = true;

        $this->dispatch('open-modal', 'followers');
    }

    /**
     * Renders the user's followers.
     */
    public function render(): View
    {
        $user = User::findOrFail($this->userId);

        return view('livewire.followers.index', [
            'user' => $user,
            'followers' => ($this->loadFollowers) ? $user->followers()->orderBy('created_at', 'desc')->simplePaginate(10) : null,
        ]);
    }
}
