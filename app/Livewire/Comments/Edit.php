<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Models\Comment;
use App\Rules\NoBlankCharacters;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Edit extends Component
{
    /**
     * The Comment content.
     */
    #[Validate(['required', 'string', 'max:255', 'min:5', new NoBlankCharacters])]
    public string $content = '';

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
        $comment = Comment::findOrFail($this->commentId);
        $this->content = $comment->raw_content ?? '';
    }

    /**
     * Refresh the component.
     */
    public function refresh(): void
    {
        $this->resetValidation('content');
        $this->dispatch('close-modal', name: "comment.edit.{$this->commentId}");
    }

    /**
     * Update the comment.
     */
    public function update(): void
    {
        $comment = Comment::findOrFail($this->commentId);
        $this->authorize('update', $comment);

        $this->validate();

        if ($comment->content !== $this->content) {
            $comment->update([
                'content' => $this->content,
            ]);

            $this->dispatch('refresh.comments');
            $this->dispatch("comment.updated.{$this->commentId}");
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
