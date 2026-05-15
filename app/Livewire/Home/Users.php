<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\Followable;
use App\Models\User;
use App\Queries\PeopleToFollow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Users extends Component
{
    use Followable;

    /**
     * The component's search query.
     */
    #[Url(as: 'q')]
    public string $query = '';

    /**
     * Indicates if the search input should be focused.
     */
    public bool $focusInput = false;

    /**
     * Renders the component.
     */
    public function render(): View
    {
        return view('livewire.home.users', [
            'users' => $this->query !== ''
                ? $this->usersByQuery()
                : $this->defaultUsers(),
        ]);
    }

    /**
     * Returns the users by query, ordered by the number of questions received.
     *
     * @return Collection<int, User>
     */
    private function usersByQuery(): Collection
    {
        return User::query()
            ->with('links')
            ->whereAny(['name', 'username'], 'like', "%{$this->query}%")
            ->withCount(['questionsReceived as answered_questions_count' => function (Builder $query): void {
                $query->whereNotNull('answer');
            }])
            ->orderBy('answered_questions_count', 'desc')
            ->when(auth()->check(), function (Builder $query): void {
                $query->withExists([
                    'following as is_follower' => function (Builder $query): void {
                        $query->where('user_id', auth()->id());
                    },
                    'followers as is_following' => function (Builder $query): void {
                        $query->where('follower_id', auth()->id());
                    },
                ]);
            })
            ->limit(10)
            ->get();
    }

    /**
     * Returns the default users, ordered by the number of questions received.
     *
     * @return Collection<int, User>
     */
    private function defaultUsers(): Collection
    {
        return new PeopleToFollow(auth()->user())->get(limit: 10);
    }
}
