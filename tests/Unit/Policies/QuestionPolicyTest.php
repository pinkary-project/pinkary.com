<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

test('view', function () {
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

test('update', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    expect($user->can('update', $question))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('update', $question))->toBeFalse();
});

test('ignore', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    expect($user->can('ignore', $question))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('ignore', $question))->toBeFalse();
});
