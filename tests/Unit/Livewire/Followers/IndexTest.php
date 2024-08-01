<?php

declare(strict_types=1);

use App\Livewire\Followers\Index;
use App\Models\User;
use Livewire\Livewire;

test('render', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertOk();
});

test('render with followers', function () {
    $user = User::factory()->create();
    $followers = User::factory(10)->create();

    $user->followers()->sync($followers->pluck('id'));

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $followers->each(function (User $user) use ($component): void {
        $component->assertSee($user->name);
    });
});

test('render with follows you badge', function () {
    $user = User::factory()->create();
    $viewer = User::factory()->create();
    $followers = User::factory(10)->create();

    $user->followers()->sync($followers->pluck('id'));

    $following = $followers->random(5);

    $viewer->followers()->sync($following->pluck('id'));

    $orderedFollowers = $user->followers()->latest('followers.id')->get();

    $component = Livewire::actingAs($viewer)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $orderedText = [];
    $orderedFollowers->each(function (User $user) use (&$orderedText, $following): void {
        $orderedText[] = $user->username;
        if ($following->contains($user)) {
            $orderedText[] = 'Follows you';
        }
    });

    $component->assertSeeInOrder($orderedText);
});

test('do not see follows you badge if user is view his profile', function () {
    $user = User::factory()->create();
    $followers = User::factory(10)->create();

    $user->followers()->sync($followers->pluck('id'));

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $component->assertDontSee('Follows you');
});
