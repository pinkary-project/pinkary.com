<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

final class WelcomeUsersAvatars extends Component
{
    /**
     * Render the component.
     */
    public function render(): View
    {
        $users = User::query()
            ->with('links')
            ->withCount('questionsReceived')
            ->orderBy('questions_received_count', 'desc')
            ->limit(14)
            ->get()
            ->shuffle();

        return view('livewire.welcome-users-avatars', [
            'users' => $users,
        ]);
    }
}
