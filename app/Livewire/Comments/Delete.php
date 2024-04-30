<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Delete extends Component
{
    /**
     * Delete modal state.
     */
    public bool $isOpen = false;

    /**
     * The Comment ID.
     */
    public ?string $commentId = null;

    /**
     * Open the delete modal.
     */
    #[On('comment.delete')]
    public function openModal(string $commentId): void
    {
        $this->commentId = $commentId;
        $comment = Comment::findOrFail($this->commentId);
        $this->authorize('delete', $comment);
        $this->isOpen = true;
    }

    /**
     * Refresh the component.
     */
    public function refresh(): void
    {
        $this->isOpen = false;
        $this->commentId = null;
    }

    /**
     * Delete the comment.
     */
    public function delete(): void
    {
        $comment = Comment::findOrFail($this->commentId);
        $this->authorize('delete', $comment);
        $comment->delete();
        $this->dispatch('comment.deleted', ['commentId' => $comment->id]);
        $this->dispatch('notification.created', message: 'Comment deleted.');
        $this->refresh();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.comments.delete');
    }
}
