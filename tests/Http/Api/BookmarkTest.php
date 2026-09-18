<?php

declare(strict_types=1);

use App\Models\Bookmark;
use App\Models\Question;
use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest cannot list bookmarks', function (): void {
    getJson(route('api.v1.bookmarks.index'))->assertUnauthorized();
});

test('an authenticated user lists bookmarked posts newest first', function (): void {
    $user = User::factory()->create();
    $first = Question::factory()->create();
    $second = Question::factory()->create();
    Bookmark::factory()->create(['user_id' => $user->id, 'question_id' => $first->id, 'created_at' => now()->subHour()]);
    Bookmark::factory()->create(['user_id' => $user->id, 'question_id' => $second->id]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    getJson(route('api.v1.bookmarks.index'), $headers)
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $second->id)
        ->assertJsonPath('data.0.metrics.bookmarked', true)
        ->assertJsonPath('data.1.id', $first->id);
});

test('bookmarks only include the authenticated user’s own', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $question = Question::factory()->create();
    Bookmark::factory()->create(['user_id' => $other->id, 'question_id' => $question->id]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    getJson(route('api.v1.bookmarks.index'), $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
