<?php

declare(strict_types=1);

namespace App\Livewire\Bookmarks;

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

        return view('livewire.bookmarks.index', [
            'user' => $user,
            'bookmarks' => $user->bookmarks()
                ->with('question')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }
}
