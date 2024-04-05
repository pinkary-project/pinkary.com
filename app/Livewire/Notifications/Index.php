<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Component;

final class Index extends Component
{
    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = type($request->user())->as(User::class);

        return view('livewire.notifications.index', [
            'user' => $user,
            'notifications' => $user->notifications()->get(),
        ]);
    }
}
