<?php

declare(strict_types=1);

namespace App\Livewire\Likes;

use App\Livewire\Concerns\Followable;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

final class Index extends Component
{
    use Followable, WithoutUrlPagination, WithPagination;

    /**
     * The component's question ID.
     */
    #[Locked]
    public string $questionId;

    /**
     * Indicates if the modal is opened.
     */
    public bool $isOpened = false;

    /**
     * Renders the users who liked the question.
     */
    public function render(): View
    {
        $question = Question::findOrFail($this->questionId);

        $this->authorize('viewLikes', $question);

        if ($this->isOpened) {
            $users = $question->likers()->latest('likes.created_at')
                ->withExists([
                    'following as is_follower' => function (Builder $query): void {
                        $query->where('user_id', auth()->id());
                    },
                    'followers as is_following' => function (Builder $query): void {
                        $query->where('follower_id', auth()->id());
                    },
                ])
                ->paginate(10);
        }

        return view('livewire.likes.index', [
            'question' => $question,
            'users' => $users ?? collect(),
        ]);
    }
}
