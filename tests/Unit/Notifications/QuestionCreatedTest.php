<?php

declare(strict_types=1);

use App\Models\Question;
use App\Notifications\QuestionCreated;

test('to database', function () {
    $question = Question::factory()->create();

    $notification = new QuestionCreated($question);

    expect($notification->toDatabase($question->to))->toBe(['question_id' => $question->id]);
});
