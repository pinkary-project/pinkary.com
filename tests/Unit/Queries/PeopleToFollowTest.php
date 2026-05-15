<?php

declare(strict_types=1);

use App\Models\User;
use App\Queries\PeopleToFollow;
use Illuminate\Support\Facades\Cache;

test('it returns only users from the discovery pool', function () {
    User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    User::factory()
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(1, ['answer' => 'answer'])
        ->create(['name' => 'Outside Discovery Pool']);

    $users = new PeopleToFollow()->get(limit: 5);

    expect($users)->toHaveCount(5)
        ->and($users->pluck('name'))->not->toContain('Outside Discovery Pool');
});

test('it excludes the current viewer', function () {
    $viewer = User::factory()->create([
        'is_verified' => true,
    ]);

    $viewer->links()->create([
        'url' => 'https://twitter.com/viewer',
        'description' => 'twitter',
    ]);

    User::factory(10)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(1, ['answer' => 'answer'])
        ->create();

    $users = new PeopleToFollow($viewer)->get(limit: 5);

    expect($users->pluck('id'))->not->toContain($viewer->id);
});

test('it caches the top 50 discovery users', function () {
    User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    Cache::forget('top-50-users');

    new PeopleToFollow()->get(limit: 5);

    expect(Cache::has('top-50-users'))->toBeTrue();
});
