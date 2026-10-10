<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Repost;

final readonly class DeleteRepost
{
    /**
     * Remove the repost.
     */
    public function handle(Repost $repost): bool
    {
        return (bool) $repost->delete();
    }
}
