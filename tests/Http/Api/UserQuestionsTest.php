<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest cannot view user questions', function (): void {
    $user = User::factory()->create();

    getJson(route('api.v1.users.questions.index', $user->username))->assertUnauthorized();
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

    $response = getJson(route('api.v1.users.questions.index', 'alice'), $headers);

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

    $response = getJson(route('api.v1.users.questions.index', 'alice'), $headers);

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

    $response = getJson(route('api.v1.users.questions.index', 'alice'), $headers);

    $response->assertOk()
        ->assertJsonPath('data.0.pinned', true)
        ->assertJsonPath('data.0.answer', 'Old pinned answer')
        ->assertJsonPath('data.1.pinned', false)
        ->assertJsonPath('data.1.answer', 'Recent answer');
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

    getJson(route('api.v1.users.questions.index', 'alice'), $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
