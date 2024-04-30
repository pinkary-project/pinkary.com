<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Models\Comment;
use App\Rules\NoBlankCharacters;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Edit extends Component
{
    /**
     * The Comment content.
     */
    #[Validate(['required', 'string', 'max:255', 'min:5', new NoBlankCharacters])]
    public ?string $content = null;

    /**
     * The Comment ID.
     */
    public ?string $commentId = null;

    /**
     * Edit modal state.
     */
    public bool $isOpen = false;

    /**
     * Open the edit modal.
     */
    #[On('comment.edit')]
    public function openModal(string $commentId): void
    {
        $this->commentId = $commentId;
        $comment = Comment::findOrFail($this->commentId);
        $this->authorize('update', $comment);
        $this->content = $comment->raw_content;
        $this->isOpen = true;
    }

    /**
     * Refresh the component.
     */
    public function refresh(): void
    {
        $this->content = '';
        $this->resetValidation('content');
        $this->isOpen = false;
    }

    /**
     * Update the comment.
     */
    public function update(): void
    {
        $comment = Comment::findOrFail($this->commentId);
        $this->authorize('update', $comment);

        if ($comment->content !== $this->content) {
            $comment->update([
                'content' => $this->validate()['content'],
            ]);

            $this->dispatch('comment.updated', ['commentId' => $comment->id]);
            $this->dispatch('notification.created', message: 'Comment updated.');
        }

        $this->refresh();
    }

    /**
     * Render the comment edit component.
     */
    public function render(): View
    {
        return view('livewire.comments.edit');
    }
}
