<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

it('exposes a single post to an authenticated user', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    $response = $this->actingAs($user)->get("/api/v1/questions/{$question->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $question->id);
});

it('returns not found for a missing post', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/api/v1/questions/'.fake()->uuid())
        ->assertNotFound();
});

it('gives a guest neutral viewer state on a post', function (): void {
    $question = Question::factory()->create();

    $response = $this->get("/api/v1/questions/{$question->id}");

    $response->assertOk()
        ->assertJsonPath('data.metrics.liked', false)
        ->assertJsonPath('data.metrics.bookmarked', false);
});

it('refuses to show an unanswered post to a guest', function (): void {
    $question = Question::factory()->create([
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $this->get("/api/v1/questions/{$question->id}")
        ->assertForbidden();
});

it('includes a thread post ancestors oldest first', function (): void {
    $user = User::factory()->create();
    $root = Question::factory()->create(['to_id' => $user->id]);
    $middle = Question::factory()->create([
        'to_id' => $user->id,
        'root_id' => $root->id,
        'parent_id' => $root->id,
    ]);
    $leaf = Question::factory()->create([
        'to_id' => $user->id,
        'root_id' => $root->id,
        'parent_id' => $middle->id,
    ]);

    $response = $this->actingAs($user)->get("/api/v1/questions/{$leaf->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'thread')
        ->assertJsonPath('thread.0.id', $root->id)
        ->assertJsonPath('thread.1.id', $middle->id)
        ->assertJsonPath('data.thread.parent_id', $middle->id)
        ->assertJsonPath('data.thread.root_id', $root->id);
});

it('lists a post comments oldest first', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    $older = Question::factory()->create([
        'to_id' => $user->id,
        'parent_id' => $question->id,
        'created_at' => now()->subHour(),
    ]);
    $newer = Question::factory()->create([
        'to_id' => $user->id,
        'parent_id' => $question->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->get("/api/v1/questions/{$question->id}/comments");

    $response->assertOk()
        ->assertJsonPath('data.0.id', $older->id)
        ->assertJsonPath('data.1.id', $newer->id);
});

it('refuses to list the comments of an unanswered post to a guest', function (): void {
    $question = Question::factory()->create([
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $this->get("/api/v1/questions/{$question->id}/comments")
        ->assertForbidden();
});

it('exposes a public profile to a guest', function (): void {
    $user = User::factory()->create(['username' => 'nunomaduro']);

    $response = $this->get('/api/v1/users/nunomaduro');

    $response->assertOk()
        ->assertJsonPath('data.username', 'nunomaduro')
        ->assertJsonPath('data.followed_by_me', false);
});

it('returns not found for a missing profile', function (): void {
    $this->get('/api/v1/users/nobody-here')
        ->assertNotFound();
});
