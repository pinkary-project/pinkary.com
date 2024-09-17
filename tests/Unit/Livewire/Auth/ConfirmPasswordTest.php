<?php

declare(strict_types=1);

use App\Livewire\Auth\ConfirmPassword;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('is confirmed with valid password', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(ConfirmPassword::class);

    $component->dispatch('confirm-password', idToConfirm: 'id-to-confirm');

    $component->assertSet('idToConfirm', 'id-to-confirm');
    $component->assertDispatched('open-modal', 'confirm-password');

    $component->set('password', 'password');

    $component->call('confirm');

    $component->assertSet('password', '');
    $component->assertDispatched('close-modal', 'confirm-password');
    $component->assertDispatched('password-confirmed-id-to-confirm');
});

test('is not confirmed with invalid password', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(ConfirmPassword::class);

    $component->dispatch('confirm-password', idToConfirm: 'id-to-confirm');

    $component->assertSet('idToConfirm', 'id-to-confirm');
    $component->assertDispatched('open-modal', 'confirm-password');

    $component->set('password', 'wrong-password');

    $component->call('confirm');

    $component->assertHasErrors('password');
    $component->assertNotDispatched('close-modal', 'confirm-password');
    $component->assertNotDispatched('password-confirmed-id-to-confirm');
});
