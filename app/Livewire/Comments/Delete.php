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
     * Refresh the component.
     */
    public function refresh(): void
    {
        $this->commentId = '';
        $this->dispatch('close-modal', name: "comment.delete.{$this->commentId}");
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
