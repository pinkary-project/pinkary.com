<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\Component;

final class DefaultAside extends Component
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.default-aside', [
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
