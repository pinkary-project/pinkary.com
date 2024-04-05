<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

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
