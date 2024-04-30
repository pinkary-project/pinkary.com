<?php

declare(strict_types=1);

use App\Livewire\Followers\Index;
use App\Models\User;

test('render', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertOk();
});

test('openFollowersModal', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
        'loadFollowers' => false,
    ]);

    $component->dispatch('openFollowersModal');

    $component->assertSetStrict('loadFollowers', true);

    $component->assertDispatched('open-modal', 'followers');
});
