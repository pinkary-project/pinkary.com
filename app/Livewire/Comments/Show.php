<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Show extends Component
{
    /**
     * The comment ID.
     */
    #[Locked]
    public string $commentId;

    /**
     * Refresh the component.
     */
    #[On([
        'comment.updated.{commentId}',
        'refresh.comments',
    ])]
    public function refresh(): void
    {
        //
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.comments.show', [
            'comment' => Comment::where('id', $this->commentId)
                ->with('owner')
                ->firstOrFail(),
        ]);
    }
}
