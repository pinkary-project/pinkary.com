<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Component;

final class Delete extends Component
{
    /**
     * The Comment ID.
     */
    public string $commentId = '';

    /**
     * Mount the component.
     */
    public function mount(string $commentId): void
    {
        $this->commentId = $commentId;
    }

    /**
     * Delete the comment.
     */
    public function delete(): void
    {
        $comment = Comment::findOrFail($this->commentId);
        $this->authorize('delete', $comment);
        $comment->delete();

        $this->dispatch('refresh.comments');
        $this->dispatch('close-modal', "comment.delete.{$this->commentId}");
        $this->dispatch('notification.created', message: 'Comment deleted.');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.comments.delete');
    }
}
