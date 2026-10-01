<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

test('a guest cannot like or bookmark questions', function (): void {
    $question = Question::factory()->create();

    $this->postJson(route('api.v1.questions.like', $question))->assertUnauthorized();
    $this->deleteJson(route('api.v1.questions.unlike', $question))->assertUnauthorized();
    $this->postJson(route('api.v1.questions.bookmark', $question))->assertUnauthorized();
    $this->deleteJson(route('api.v1.questions.unbookmark', $question))->assertUnauthorized();
});

test('an authenticated user can like and unlike a question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.like', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.liked', true)
        ->assertJsonPath('data.likes', 1);

    // Liking twice stays idempotent.
    $this->postJson(route('api.v1.questions.like', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.likes', 1);

    $this->assertDatabaseCount('likes', 1);

    $this->deleteJson(route('api.v1.questions.unlike', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.liked', false)
        ->assertJsonPath('data.likes', 0);

    // Unliking twice stays a no-op.
    $this->deleteJson(route('api.v1.questions.unlike', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.likes', 0);

    $this->assertDatabaseCount('likes', 0);
});

test('an authenticated user can bookmark and unbookmark a question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarked', true)
        ->assertJsonPath('data.bookmarks', 1);

    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarks', 1);

    $this->assertDatabaseCount('bookmarks', 1);

    $this->deleteJson(route('api.v1.questions.unbookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarked', false)
        ->assertJsonPath('data.bookmarks', 0);

    $this->assertDatabaseCount('bookmarks', 0);
});

test('liking, bookmarking and voting require a viewable question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'answer' => null,
        'answer_created_at' => null,
        'poll_expires_at' => now()->addDay(),
    ]);
    $option = App\Models\PollOption::factory()->create(['question_id' => $question->id]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.like', $question), [], $headers)->assertForbidden();
    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)->assertForbidden();
    $this->postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $option->id], $headers)
        ->assertForbidden();

    expect($question->likes()->count())->toBe(0)
        ->and($question->bookmarks()->count())->toBe(0)
        ->and($question->pollVotes()->count())->toBe(0);
});

test('a guest cannot view who liked a question', function (): void {
    $question = Question::factory()->create();

    $this->getJson(route('api.v1.questions.likes.index', $question))->assertUnauthorized();
});

test('a question owner can view who liked the question', function (): void {
    $owner = User::factory()->create();
    $liker = User::factory()->create(['name' => 'Liker User']);
    $stranger = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $owner->id]);

    App\Models\Like::create([
        'user_id' => $liker->id,
        'question_id' => $question->id,
    ]);

    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];
    $ownerHeaders = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.questions.likes.index', $question), $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();

    $this->getJson(route('api.v1.questions.likes.index', $question), $ownerHeaders)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $liker->id)
        ->assertJsonPath('data.0.name', 'Liker User');
});

test('a user can ignore received questions', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $owner->id]);

    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];
    $ownerHeaders = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.ignore', $question), [], $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();

    $this->postJson(route('api.v1.questions.ignore', $question), [], $ownerHeaders)
        ->assertOk()
        ->assertJsonPath('data.ignored', true);

    expect($question->fresh()->is_ignored)->toBeTrue();
});
