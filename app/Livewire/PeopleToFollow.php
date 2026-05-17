<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

final class PeopleToFollow extends Component
{
    /**
     * Render the component.
     */
    public function render(): View
    {
        $famousUsers = Cache::remember('top-50-users', now()->endOfDay(), fn (): array => User::query()
            ->whereHas('links', function (Builder $query): void {
                $query->where('url', 'like', '%twitter.com%')
                    ->orWhere('url', 'like', '%github.com%')
                    ->orWhere('url', 'like', '://x.com%');
            })
            ->withCount(['questionsReceived as answered_questions_count' => function (Builder $query): void {
                $query->whereNotNull('answer');
            }])
            ->orderBy('answered_questions_count', 'desc')
            ->limit(50)->pluck('id')->toArray()
        );

        return view('livewire.people-to-follow', [
            'users' => User::query()
                ->whereIn('id', $famousUsers)
                ->inRandomOrder()
                ->limit(5)
                ->get(),
        ]);
    }
}
