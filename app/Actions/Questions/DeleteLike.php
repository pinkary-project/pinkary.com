<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Like;

final readonly class DeleteLike
{
    /**
     * Remove the like.
     */
    public function handle(Like $like): void
    {
        $like->delete();
    }
}
