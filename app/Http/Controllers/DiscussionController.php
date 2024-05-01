<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\View\View;

final readonly class DiscussionController
{
    /**
     * Discuss the question
     */
    public function __invoke(Question $question): View
    {
        return view('discussion.show', [
            'questionId' => $question->id,
        ]);
    }
}
