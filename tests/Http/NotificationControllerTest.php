<?php

declare(strict_types=1);

use App\Models\Question;

test('mark notification as read', function () {
    $question = Question::factory()->create();

    $question->update(['answer' => 'Question answer']);

    $notification = $question->from->notifications()->first();

    /** @var Illuminate\Testing\TestResponse $response */
    $response = $this->actingAs($question->from)
        ->get(route('notifications.show', [
            'notification' => $notification,
            'user' => $question->from,
        ]));

    $response->assertRedirectToRoute('questions.show', ['question' => $question, 'user' => $question->from]);
    expect($notification->fresh()->read_at)->not->toBe(null);
});
