<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final readonly class QuestionController
{
    /**
     * Display the question.
     */
    public function show(User $user, Question $question): Response|View
    {
        Gate::authorize('view', $question);

        abort_unless($question->to_id === $user->id, 404);

        return view('questions.show', [
            'question' => $question,
        ]);
    }
}
