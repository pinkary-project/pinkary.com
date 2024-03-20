<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Index extends Component
{
    /**
     * The component's search query.
     */
    #[Url(as: 'q')]
    public string $query = '';

    /**
     * Renders the component.
     */
    public function render(): View
    {
        return view('livewire.users.index', [
            'users' => $this->query !== ''
                ? $this->usersByQuery()
                : $this->usersWithMostAnswers(),
        ]);
    }

    /**
     * Returns the users by query, ordered by the number of questions received.
     *
     * @return Collection<int, User>
     */
    private function usersByQuery(): Collection
    {
        return User::where('name', 'like', "%{$this->query}%")
            ->orWhere('username', 'like', "%{$this->query}%")
            ->withCount('questionsReceived')
            ->orderBy('questions_received_count', 'desc')->limit(10)->get();
    }

    /**
     * Returns the users with the most questions received.
     *
     * @return Collection<int, User>
     */
    private function usersWithMostAnswers(): Collection
    {
        return User::whereHas('links', function (Builder $query): void {
            $query->where('url', 'like', '%twitter.com%')
                ->orWhere('url', 'like', '%github.com%');
        })
            ->withCount('questionsReceived')
            ->orderBy('questions_received_count', 'desc')
            ->limit(10)->get();
    }
}
