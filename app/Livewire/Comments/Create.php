<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Create extends Component
{
    /**
     * The ID of the question.
     */
    #[Locked]
    public string $questionId;

    /**
     * The content of the comment.
     */
    #[Validate(['required', 'string', 'max:255', 'min:5', new NoBlankCharacters])]
    public ?string $content = null;

    /**
     * Refresh the component.
     */
    #[On('comment.created')]
    public function refresh(): void
    {
        $this->content = '';
        $this->resetValidation('content');
    }

    /**
     * Store a new comment.
     */
    public function store(): void
    {
        $this->authorize('create', Comment::class);

        $user = type(auth()->user())->as(User::class);

        $comment = $user->comments()->create([
            'content' => $this->validate()['content'],
            'question_id' => $this->questionId,
        ]);

        $this->dispatch('notification.created', message: 'Comment added successfully!');

        $this->dispatch('comment.created', ['commentId' => $comment->id]);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.comments.create');
    }
}
