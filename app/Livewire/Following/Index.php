<?php

declare(strict_types=1);

namespace App\Livewire\Following;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
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
     * Renders the user's followers.
     */
    public function render(): View
    {
        $user = User::findOrFail($this->userId);

        return view('livewire.following.index', [
            'user' => $user,
            'following' => $user->following()->orderBy('created_at', 'desc')->simplePaginate(10),
        ]);
    }
}
