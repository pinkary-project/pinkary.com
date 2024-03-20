<?php

declare(strict_types=1);

use App\Models\Question;
use App\Notifications\QuestionCreated;
use Illuminate\Support\Facades\Notification;

test('created', function () {
    Notification::fake();

    $question = Question::factory()->create();

    Notification::assertSentTo($question->to, QuestionCreated::class, function ($notification) use ($question) {
        return expect($notification->toDatabase($question->to))->toBe(['question_id' => $question->id]);
    });
});

test('updated', function () {
    $question = Question::factory()->create();

    expect($question->to->notifications->count())->toBe(1);

    $question->update(['is_reported' => true]);

    expect($question->fresh()->to->notifications->count())->toBe(0);

    $question = Question::factory()->create();

    expect($question->to->notifications->count())->toBe(1);

    $question->update(['answer' => 'answer']);

    expect($question->fresh()->to->notifications->count())->toBe(0);
});

test('deleted', function () {
    $question = Question::factory()->create();

    expect($question->to->notifications->count())->toBe(1);

    $user = $question->to;

    $question->delete();

    expect($user->fresh()->notifications->count())->toBe(0);
});
