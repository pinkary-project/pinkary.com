<?php

declare(strict_types=1);

use App\Models\Question;

test('guest', function () {
    $question = Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $response = $this->get(route('notifications.show', [
        'user' => $question->from->username,
        'notification' => $question->to->notifications()->first(),
    ]));

    $response->assertRedirect(route('login'));
});

test('mark notification as read', function () {
    $question = Question::factory()->create();

    $question->update(['answer' => 'Question answer']);

    $notification = $question->from->notifications()->first();
    expect($notification->fresh()->read_at)->toBe(null);

    /** @var Illuminate\Testing\TestResponse $response */
    $response = $this->actingAs($question->from)
        ->get(route('notifications.show', [
            'notification' => $notification,
        ]));

    $response->assertRedirectToRoute('questions.show', ['question' => $question, 'user' => $question->from->username]);
    expect($notification->fresh()->read_at)->not->toBe(null);
});
