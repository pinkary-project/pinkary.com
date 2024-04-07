<?php

declare(strict_types=1);

use App\Livewire\LinkSettings\Edit;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('pre selects user link shape and gradient', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->assertSet('link_shape', 'rounded-lg');
    $component->assertSet('gradient', 'from-blue-500 to-purple-600');

    $user = User::factory()->create([
        'settings' => [
            'link_shape' => 'rounded-none',
            'gradient' => 'from-red-500 to-orange-600',
        ],
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->assertSet('link_shape', 'rounded-none');
    $component->assertSet('gradient', 'from-red-500 to-orange-600');
});

it('allows user to update link settings', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->set('link_shape', 'rounded-none');
    $component->set('gradient', 'from-red-500 to-orange-600');

    $component->call('update');

    $component->assertDispatched('link-settings.updated');
    $component->assertDispatched('notification.created', message: 'Link settings updated.');

    $user->refresh();

    expect($user->settings)->toBe([
        'link_shape' => 'rounded-none',
        'gradient' => 'from-red-500 to-orange-600',
    ]);
});
