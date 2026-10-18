<?php

declare(strict_types=1);

namespace App\Livewire\Bookmarks;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Index extends Component
{
    use HasLoadMore;

    /**
     * Refresh the component.
     */
    #[On('question.unbookmarked')]
    public function refresh(): void {}

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] User $user): View
    {
        return view('livewire.bookmarks.index', [
            'user' => $user,
            'bookmarks' => $user->bookmarks()
                ->whereHas('question', fn (Builder $query) => $query->tap(new WhereNotModerated))
                ->with('question')
                ->orderBy('created_at', 'desc')
                ->simplePaginate($this->perPage),
        ]);
    }
}
