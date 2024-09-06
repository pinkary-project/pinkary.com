<?php

declare(strict_types=1);

namespace App\Livewire\Navigation\NotificationsCount;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Show extends Component
{
    /**
     * Refresh the component.
     */
    #[On('question.created')]
    #[On('question.updated')]
    #[On('question.reported')]
    #[On('question.ignored')]
    public function refresh(): void
    {
        //
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = type($request->user())->as(User::class);

        return view('livewire.navigation.notifications-count.show', [
            'count' => $user->notifications()->count(),
        ]);
    }
}
