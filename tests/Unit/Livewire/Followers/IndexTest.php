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
