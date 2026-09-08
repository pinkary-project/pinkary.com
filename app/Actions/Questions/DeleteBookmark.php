<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Bookmark;

final readonly class DeleteBookmark
{
    /**
     * Remove the bookmark.
     */
    public function handle(Bookmark $bookmark): bool
    {
        return (bool) $bookmark->delete();
    }
}
