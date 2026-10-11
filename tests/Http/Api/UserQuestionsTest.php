<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\Repost;
use App\Models\User;

test('a guest can read answered questions for a profile', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Public question?',
        'answer' => 'Public answer.',
        'answer_created_at' => now(),
    ]);

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Unanswered question?',
        'answer' => null,
    ]);

    $this->getJson(route('api.v1.users.questions.index', 'alice'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.answer', 'Public answer.')
        ->assertJsonPath('data.0.metrics.liked', false)
        ->assertJsonPath('data.0.metrics.bookmarked', false);
});

test('a guest sees post counts but never their own state or private fields', function (): void {
    $owner = User::factory()->create(['username' => 'alice', 'email' => 'alice@example.com']);
    $viewer = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Public question?',
        'answer' => 'Public answer.',
        'answer_created_at' => now(),
    ]);

    $viewer->bookmarks()->create(['question_id' => $question->id]);
    App\Models\Like::factory()->create(['user_id' => $viewer->id, 'question_id' => $question->id]);
    $headers = ['Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.users.questions.index', 'alice'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.metrics.liked', true)
        ->assertJsonPath('data.0.metrics.bookmarked', true)
        ->assertJsonPath('data.0.metrics.likes', 1);

    auth()->forgetGuards();

    $this->getJson(route('api.v1.users.questions.index', 'alice'))
        ->assertOk()
        ->assertJsonPath('data.0.metrics.liked', false)
        ->assertJsonPath('data.0.metrics.bookmarked', false)
        ->assertJsonPath('data.0.metrics.likes', 1)
        ->assertJsonMissingPath('data.0.email')
        ->assertJsonMissingPath('data.0.from.email')
        ->assertJsonMissingPath('data.0.to.email');
});

test('a guest cannot see a profile owner unanswered questions', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Unanswered question?',
        'answer' => null,
    ]);

    $this->getJson(route('api.v1.users.questions.index', 'alice'))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('an authenticated user can view answered questions for a profile', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);
    $viewer = User::factory()->create();

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Public question?',
        'answer' => 'Public answer.',
        'answer_created_at' => now(),
    ]);

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Unanswered question?',
        'answer' => null,
    ]);

    $headers = ['Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken];

    $response = $this->getJson(route('api.v1.users.questions.index', 'alice'), $headers);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.answer', 'Public answer.');
});

test('profile owner can view both answered and unanswered questions', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Public question?',
        'answer' => 'Public answer.',
        'answer_created_at' => now(),
    ]);

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Unanswered question?',
        'answer' => null,
    ]);

    $headers = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $response = $this->getJson(route('api.v1.users.questions.index', 'alice'), $headers);

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('pinned question appears first in user questions', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);
    $viewer = User::factory()->create();

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Recent question',
        'answer' => 'Recent answer',
        'answer_created_at' => now(),
        'pinned' => false,
    ]);

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Old pinned question',
        'answer' => 'Old pinned answer',
        'answer_created_at' => now()->subDays(10),
        'pinned' => true,
    ]);

    $headers = ['Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken];

    $response = $this->getJson(route('api.v1.users.questions.index', 'alice'), $headers);

    $response->assertOk()
        ->assertJsonPath('data.0.pinned', true)
        ->assertJsonPath('data.0.answer', 'Old pinned answer')
        ->assertJsonPath('data.1.pinned', false)
        ->assertJsonPath('data.1.answer', 'Recent answer');
});

test('only the profile owner pinned posts are promoted ahead of reposts', function (): void {
    $this->freezeTime();
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $pinned = Question::factory()->sharedUpdate()->create([
        'from_id' => $owner->id,
        'to_id' => $owner->id,
        'pinned' => true,
        'updated_at' => now()->subDays(3),
    ]);
    $repostedPost = Question::factory()->sharedUpdate()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
        'pinned' => true,
    ]);
    Repost::factory()->create([
        'user_id' => $owner->id,
        'question_id' => $repostedPost->id,
        'created_at' => now()->subDays(2),
    ]);
    $recent = Question::factory()->sharedUpdate()->create([
        'from_id' => $owner->id,
        'to_id' => $owner->id,
        'updated_at' => now()->subDay(),
    ]);

    $this->getJson(route('api.v1.users.questions.index', $owner->username))
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.id', $pinned->id)
        ->assertJsonPath('data.1.id', $recent->id)
        ->assertJsonPath('data.2.id', $repostedPost->id);
});

test('a thread shows up even when its newest post is a reply', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);
    $friend = User::factory()->create();

    $root = Question::factory()->create([
        'from_id' => $friend->id,
        'to_id' => $owner->id,
        'content' => '__UPDATE__',
        'answer' => 'First post.',
        'answer_created_at' => now()->subDay(),
    ]);

    $reply = Question::factory()->create([
        'from_id' => $friend->id,
        'to_id' => $owner->id,
        'content' => '__UPDATE__',
        'answer' => 'A reply in the thread.',
        'answer_created_at' => now(),
        'parent_id' => $root->id,
        'root_id' => $root->id,
    ]);

    $this->getJson(route('api.v1.users.questions.index', 'alice'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $reply->id)
        ->assertJsonPath('data.0.answer', 'A reply in the thread.')
        ->assertJsonPath('data.0.thread.parent_id', (string) $root->id)
        ->assertJsonPath('data.0.thread.root_id', (string) $root->id);
});

test('one row per thread, ordered by when the thread last changed', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);

    $oldest = Question::factory()->create([
        'from_id' => $owner->id,
        'to_id' => $owner->id,
        'content' => '__UPDATE__',
        'answer' => 'Oldest thread.',
        'answer_created_at' => now()->subDays(5),
    ]);

    Question::factory()->create([
        'from_id' => $owner->id,
        'to_id' => $owner->id,
        'content' => '__UPDATE__',
        'answer' => 'Middle thread.',
        'answer_created_at' => now()->subDays(3),
    ]);

    $reply = Question::factory()->create([
        'from_id' => $owner->id,
        'to_id' => $owner->id,
        'content' => '__UPDATE__',
        'answer' => 'A later reply.',
        'answer_created_at' => now(),
        'parent_id' => $oldest->id,
        'root_id' => $oldest->id,
    ]);

    $response = $this->getJson(route('api.v1.users.questions.index', 'alice'))
        ->assertOk()
        ->assertJsonCount(2, 'data');

    expect($response->json('data.0.id'))->toBe($reply->id)
        ->and($response->json('data.1.answer'))->toBe('Middle thread.');
});

test('ignored questions are excluded from profile questions', function (): void {
    $owner = User::factory()->create(['username' => 'alice']);
    $viewer = User::factory()->create();

    Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'Ignored question',
        'answer' => 'An answer',
        'answer_created_at' => now(),
        'is_ignored' => true,
    ]);

    $headers = ['Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.users.questions.index', 'alice'), $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
