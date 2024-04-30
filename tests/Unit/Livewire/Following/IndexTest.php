<?php

declare(strict_types=1);

use App\Livewire\Following\Index;
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
        'loadFollowing' => false,
    ]);

    $component->dispatch('openFollowingModal');

    $component->assertSetStrict('loadFollowing', true);

    $component->assertDispatched('open-modal', 'following');
});
