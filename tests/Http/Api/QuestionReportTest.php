<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

test('the recipient can report an unanswered question', function (): void {
    $recipient = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $recipient->id,
        'answer' => null,
        'answer_created_at' => null,
        'is_reported' => false,
    ]);
    $headers = ['Authorization' => 'Bearer '.$recipient->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.report', $question), [], $headers)
        ->assertNoContent();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'is_reported' => true,
    ]);
});

test('reporting an answered question returns 422', function (): void {
    $recipient = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $recipient->id,
        'answer' => 'Already answered.',
    ]);
    $headers = ['Authorization' => 'Bearer '.$recipient->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.report', $question), [], $headers)
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Only unanswered questions can be reported.');

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'is_reported' => false,
    ]);
});

test("reporting another user's question returns 403", function (): void {
    $recipient = User::factory()->create();
    $stranger = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $recipient->id,
        'answer' => null,
        'answer_created_at' => null,
    ]);
    $headers = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.report', $question), [], $headers)
        ->assertForbidden();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'is_reported' => false,
    ]);
});
