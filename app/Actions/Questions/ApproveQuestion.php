<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use Illuminate\Support\Facades\Gate;

final readonly class ApproveQuestion
{
    /**
     * Approve a question for public visibility.
     */
    public function handle(Question $question): void
    {
        Gate::authorize('approve', $question);

        $question->update(['requires_approval' => false]);
    }
}
