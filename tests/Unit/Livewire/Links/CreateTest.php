<?php

declare(strict_types=1);

use App\Livewire\Links\Create;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('allows to create link', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('url', 'https://example.com');
    $component->set('description', 'Example');

    $component->call('store');

    $component->assertDispatched('link.created');
    $component->assertDispatched('notification.created', message: 'Link created.');

    $user->refresh();

    expect($user->links->count())->toBe(1);

    $link = $user->links->first();

    expect($link->url)->toBe('https://example.com')
        ->and($link->description)->toBe('Example');
});

test('https is added to the URL', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('url', 'example.com');
    $component->set('description', 'Example');

    $component->call('store');

    $component->assertDispatched('link.created');
    $component->assertDispatched('notification.created', message: 'Link created.');

    $user->refresh();

    $link = $user->links->first();

    expect($link->url)->toBe('https://example.com');
});

test('only 10 links are allowed', function () {
    $user = User::factory()->create();

    $links = $user->links()->createMany(
        array_fill(0, 9, [
            'url' => 'https://example.com',
            'description' => 'Example',
        ])
    );

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('url', 'https://example.com');
    $component->set('description', 'Example');

    $component->call('store');

    expect($user->links->count())->toBe(10);

    $component->call('store');

    $component->assertHasErrors('url');

    $user->refresh();

    expect($user->links->count())->toBe(10);
});

test('only 20 links are allowed for "is_verified" users', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'is_verified' => true,
    ]);

    $links = $user->links()->createMany(
        array_fill(0, 19, [
            'url' => 'https://example.com',
            'description' => 'Example',
        ])
    );

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('url', 'https://example.com');
    $component->set('description', 'Example');

    $component->call('store');

    expect($user->links->count())->toBe(20);

    $component->call('store');

    $component->assertHasErrors('url');

    $user->refresh();

    expect($user->links->count())->toBe(20);
});
