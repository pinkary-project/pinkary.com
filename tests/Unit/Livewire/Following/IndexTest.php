<?php

declare(strict_types=1);

use App\Livewire\Following\Index;
use App\Models\User;
use Livewire\Livewire;

test('render', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertOk();
});

test('render with following', function () {
    $user = User::factory()->create();
    $following = User::factory(10)->create();

    $user->following()->sync($following->pluck('id'));

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $following->each(function (User $user) use ($component): void {
        $component->assertSee($user->name);
    });
});

test('render with follows you badge', function () {
    $user = User::factory()->create();
    $following = User::factory(10)->create();

    $user->following()->sync($following->pluck('id'));

    $followers = $following->random(5);

    $user->followers()->sync($followers->pluck('id'));

    $orderedFollowing = $user->following()->latest('followers.id')->get();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $orderedText = [];
    $orderedFollowing->each(function (User $user) use (&$orderedText, $followers): void {
        $orderedText[] = $user->username;
        if ($followers->contains($user)) {
            $orderedText[] = 'Follows you';
        }
    });

    $component->assertSeeInOrder($orderedText);
});
