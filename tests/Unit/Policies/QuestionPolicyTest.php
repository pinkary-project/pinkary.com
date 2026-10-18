<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

test('view', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    expect($user->can('view', $question))->toBeTrue();

    $question->update(['is_reported' => true]);
    expect($user->can('view', $question))->toBeFalse();

    $question->update(['is_reported' => false]);
    expect($user->can('view', $question))->toBeTrue();

    $question->update(['is_ignored' => true]);
    expect($user->can('view', $question))->toBeFalse();
});

test('only the author and admins can view posts awaiting approval', function (): void {
    $author = User::factory()->create();
    $question = Question::factory()->sharedUpdate()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
        'requires_approval' => true,
    ]);
    $otherUser = User::factory()->create();
    $admin = User::factory()->create(['email' => 'enunomaduro@gmail.com']);

    expect($author->can('view', $question))->toBeTrue()
        ->and($otherUser->can('view', $question))->toBeFalse()
        ->and($admin->can('view', $question))->toBeTrue();
});

test('only admins can approve an unmoderated pending post', function (): void {
    $question = Question::factory()->sharedUpdate()->create([
        'requires_approval' => true,
    ]);
    $user = User::factory()->create();
    $admin = User::factory()->create(['email' => 'enunomaduro@gmail.com']);

    expect($user->can('approve', $question))->toBeFalse()
        ->and($admin->can('approve', $question))->toBeTrue();

    $question->update(['is_reported' => true]);

    expect($admin->can('approve', $question))->toBeFalse();
});

test('update', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    expect($user->can('update', $question))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('update', $question))->toBeFalse();
});

test('ignore', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    expect($user->can('ignore', $question))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('ignore', $question))->toBeFalse();
});

test('view likes', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    expect($user->can('viewLikes', $question))->toBeTrue();

    $otherUser = User::factory()->create();

    expect($otherUser->can('viewLikes', $question))->toBeFalse();
});
