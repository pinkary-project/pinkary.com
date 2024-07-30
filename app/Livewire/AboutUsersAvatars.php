<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

final class AboutUsersAvatars extends Component
{
    /**
     * Render the component.
     */
    public function render(): View
    {
        $users = User::query()
            ->with('links')
            ->withCount(['questionsReceived as answered_questions_count' => function (Builder $query): void {
                $query->whereNotNull('answer');
            }])
            ->orderBy('answered_questions_count', 'desc')
            ->limit(14)
            ->get()
            ->shuffle();

        return view('livewire.about-users-avatars', [
            'users' => $users,
        ]);
    }
}
