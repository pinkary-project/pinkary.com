<?php

declare(strict_types=1);

use App\Models\Bookmark;
use App\Models\Question;
use App\Models\User;

test('a guest cannot list bookmarks', function (): void {
    $this->getJson(route('api.v1.bookmarks.index'))->assertUnauthorized();
});

test('an authenticated user lists bookmarked posts newest first', function (): void {
    $user = User::factory()->create();
    $first = Question::factory()->create();
    $second = Question::factory()->create();
    Bookmark::factory()->create(['user_id' => $user->id, 'question_id' => $first->id, 'created_at' => now()->subHour()]);
    Bookmark::factory()->create(['user_id' => $user->id, 'question_id' => $second->id]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.bookmarks.index'), $headers)
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $second->id)
        ->assertJsonPath('data.0.metrics.bookmarked', true)
        ->assertJsonPath('data.1.id', $first->id);
});

test('a bookmarked reply includes its root and parent posts', function (): void {
    $user = User::factory()->create();
    $root = Question::factory()->create();
    $parent = Question::factory()->create([
        'root_id' => $root->id,
        'parent_id' => $root->id,
    ]);
    $reply = Question::factory()->create([
        'root_id' => $root->id,
        'parent_id' => $parent->id,
    ]);
    Bookmark::factory()->create([
        'user_id' => $user->id,
        'question_id' => $reply->id,
    ]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.bookmarks.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.id', $reply->id)
        ->assertJsonPath('data.0.thread.posts.0.id', $root->id)
        ->assertJsonPath('data.0.thread.posts.1.id', $parent->id);
});

test('bookmarks only include the authenticated user’s own', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $question = Question::factory()->create();
    Bookmark::factory()->create(['user_id' => $other->id, 'question_id' => $question->id]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.bookmarks.index'), $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
