<?php

declare(strict_types=1);

use App\Livewire\PeopleToFollow;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('it renders the people to follow list', function () {
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

test('it caches the top 50 users', function () {
    User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    Cache::forget('top-50-users');

    Livewire::test(PeopleToFollow::class);

    expect(Cache::has('top-50-users'))->toBeTrue();
});
