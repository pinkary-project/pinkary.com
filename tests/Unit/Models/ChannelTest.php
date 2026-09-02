<?php

declare(strict_types=1);

use App\Models\Channel;
use App\Models\Question;
use App\Models\User;

test('to array', function (): void {
    $channel = Channel::factory()->create();

    expect(array_keys($channel->toArray()))->toContain(
        'id',
        'user_id',
        'name',
        'slug',
        'description',
        'questions_count',
        'created_at',
        'updated_at',
    )->toHaveCount(8);
});

test('relations', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create(['user_id' => $user->id]);
    Question::factory()->forChannel($channel)->create();

    expect($channel->user)->toBeInstanceOf(User::class)
        ->and($channel->questions)->toContainOnlyInstancesOf(Question::class)
        ->and($channel->questions)->toHaveCount(1)
        ->and($user->channels)->toContainOnlyInstancesOf(Channel::class)
        ->and($user->channels)->toHaveCount(1)
        ->and($channel->getRouteKeyName())->toBe('slug');
});
