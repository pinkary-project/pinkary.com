<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
final class Aside extends Component
{
    /**
     * Get the placeholder for the component.
     */
    public function placeholder(): View
    {
        return view('livewire.home.aside-placeholder');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // not the final
        return view('livewire.home.aside', [
            'usersToFollow' => User::inRandomOrder()
                ->whereDoesntHave('followers', fn ($query) => $query->where('follower_id', auth()->id()))
                ->withExists([
                    'following as is_follower' => function (Builder $query): void {
                        $query->where('user_id', auth()->id());
                    },
                ])
                ->limit(5)
                ->get()
                ->map(fn (User $user): User => $user->setAttribute('is_following', false)),
        ]);
    }
}
