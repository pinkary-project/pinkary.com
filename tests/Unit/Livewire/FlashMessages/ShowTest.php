<?php

declare(strict_types=1);

use App\Livewire\FlashMessages\Show;
use Livewire\Livewire;

test('displays flash message', function () {

    $component = Livewire::test(Show::class);

    $component->dispatch('notification.created', 'Hello, world!');

    $component->assertSee('Hello, world!');
    $component->assertDontSee('Goodbye, world!');

    $component->dispatch('notification.created', 'Goodbye, world!');

    $component->assertDontSee('Hello, world!');
    $component->assertSee('Goodbye, world!');
});

test('flushes flash message', function () {
    $component = Livewire::test(Show::class);

    $component->dispatch('notification.created', 'Hello, world!');

    $component->assertSee('Hello, world!');
    $component->assertSee('Hello, world!');

    $component->call('flush');

    $component->assertDontSee('Hello, world!');
});
