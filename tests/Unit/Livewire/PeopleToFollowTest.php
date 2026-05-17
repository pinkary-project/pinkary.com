<?php

declare(strict_types=1);

use App\Livewire\PeopleToFollow;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::forget('top-50-users');
});

it('renders the people to follow list', function () {
    User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    $outsideDiscoveryPool = User::factory()
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(1, ['answer' => 'answer'])
        ->create(['name' => 'Outside Discovery Pool']);

    $component = Livewire::test(PeopleToFollow::class);

    $component->assertSee('People to follow')
        ->assertDontSee($outsideDiscoveryPool->name);

    expect($component->viewData('users'))->toHaveCount(5);
});

it('excludes users already followed by the authenticated user', function () {
    $user = User::factory()->create();

    $discoveryPool = User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    $followedUser = $discoveryPool->first();

    $user->following()->attach($followedUser);

    $component = Livewire::actingAs($user)->test(PeopleToFollow::class);

    expect($component->viewData('users')->contains(fn (User $suggestedUser): bool => $suggestedUser->is($followedUser)))
        ->toBeFalse()
        ->and($component->viewData('users'))
        ->toHaveCount(5);
});

it('caches the top 50 users', function () {
    User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    Livewire::test(PeopleToFollow::class);

    expect(Cache::has('top-50-users'))->toBeTrue();
});
