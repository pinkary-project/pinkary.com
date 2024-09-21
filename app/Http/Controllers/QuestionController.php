<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final readonly class QuestionController
{
    /**
     * Display the question.
     */
    public function show(User $user, Question $question): View
    {
        Gate::authorize('view', $question);

        abort_unless($question->to_id === $user->id, 404);

        $parentQuestions = [];
        $parentQuestion = $question->parent;

        do {
            $parentQuestions[] = $parentQuestion;
        } while ($parentQuestion = $parentQuestion?->parent);

        $parentQuestions = collect($parentQuestions)->filter()->reverse()->all();

        return view('questions.show', [
            'question' => $question,
            'parentQuestions' => $parentQuestions,
        ]);
    }
}
