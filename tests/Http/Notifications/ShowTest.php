<?php

declare(strict_types=1);

use App\Models\Question;

test('guest', function () {
    $question = Question::factory()->create([
        'content' => 'This is the answer',
    ]);

    $response = $this->get(route('notifications.show', [
        'username' => $question->from->username,
        'notification' => $question->to->notifications()->first(),
    ]));

    $response->assertRedirect(route('login'));
});

test('notifications about answers are deleted', function () {
    $question = Question::factory()
        ->hasAnswer()
        ->create();

    $question->answer->update(['content' => 'Question answer']);

    $notification = $question->from->notifications()->first();
    expect($notification->fresh())->not->toBe(null);

    /** @var Illuminate\Testing\TestResponse $response */
    $response = $this->actingAs($question->from)
        ->get(route('notifications.show', [
            'notification' => $notification,
        ]));

    $response->assertRedirectToRoute('questions.show', ['question' => $question, 'username' => $question->to->username]);
    expect($notification->fresh())->toBeNull();
});

test('notifications about questions are not deleted', function () {
    $question = Question::factory()
        ->create();

    expect($question->to->notifications()->count())->toBe(1);

    $notification = $question->to->notifications()->first();

    /** @var Illuminate\Testing\TestResponse $response */
    $response = $this->actingAs($question->to)
        ->get(route('notifications.show', [
            'notification' => $notification,
        ]));

    $response->assertRedirectToRoute('questions.show', ['question' => $question, 'username' => $question->to->username]);
    expect($notification->fresh())->not->toBeNull();
});
