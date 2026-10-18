<?php

declare(strict_types=1);

use App\Actions\Questions\CreateQuestion;
use App\Actions\Questions\UpdateQuestion;
use App\Models\Question;
use App\Models\User;

test('public posts and thread replies from users without followers need approval', function (): void {
    $user = User::factory()->create();

    $questions = app(CreateQuestion::class)->handle($user, [
        [
            'to_id' => $user->id,
            'content' => '__UPDATE__',
            'answer' => 'First post',
            'answer_created_at' => now(),
        ],
        [
            'to_id' => $user->id,
            'content' => '__UPDATE__',
            'answer' => 'Thread reply',
            'answer_created_at' => now(),
        ],
    ], [], [], null);

    expect($questions)->toHaveCount(2)
        ->and($questions[0]->requires_approval)->toBeTrue()
        ->and($questions[1]->requires_approval)->toBeTrue();
});

test('public posts from users with followers do not need approval', function (): void {
    $user = User::factory()->create();
    $user->followers()->attach(User::factory()->create());

    $question = app(CreateQuestion::class)->handle($user, [[
        'to_id' => $user->id,
        'content' => '__UPDATE__',
        'answer' => 'A public post',
        'answer_created_at' => now(),
    ]], [], [], null)[0];

    expect($question->requires_approval)->toBeFalse();
});

test('private questions do not need approval even when the sender has no followers', function (): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $question = app(CreateQuestion::class)->handle($sender, [[
        'to_id' => $recipient->id,
        'content' => 'A private question?',
    ]], [], [], null)[0];

    expect($question)->toBeInstanceOf(Question::class)
        ->and($question->requires_approval)->toBeFalse();
});

test('a first public answer from a user without followers needs approval', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $user->id,
        'answer' => null,
        'answer_created_at' => null,
    ]);

    app(UpdateQuestion::class)->handle($question, [
        'answer' => 'A public answer',
        'answer_created_at' => now(),
    ], false, null, false);

    expect($question->refresh()->requires_approval)->toBeTrue();
});

test('a first public answer from a user with followers is published immediately', function (): void {
    $user = User::factory()->create();
    $user->followers()->attach(User::factory()->create());
    $question = Question::factory()->create([
        'to_id' => $user->id,
        'answer' => null,
        'answer_created_at' => null,
    ]);

    app(UpdateQuestion::class)->handle($question, [
        'answer' => 'A public answer',
        'answer_created_at' => now(),
    ], false, null, false);

    expect($question->refresh()->requires_approval)->toBeFalse();
});
