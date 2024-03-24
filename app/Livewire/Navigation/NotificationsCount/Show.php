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
    #[On('question.destroyed')]
    public function refresh(): void
    {
        //
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = $request->user();
        assert($user instanceof User);

        return view('livewire.navigation.notifications-count.show', [
            'count' => $user->notifications()->whereNull('read_at')->count(),
        ]);
    }
}
