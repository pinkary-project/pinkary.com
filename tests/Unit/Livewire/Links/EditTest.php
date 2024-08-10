<?php

declare(strict_types=1);

use App\Livewire\Links\Edit;
use App\Models\Link;
use App\Models\User;
use Livewire\Livewire;

test('renders the edit link form with property values', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->call('edit', $link->id);
    $component->assertSet('linkId', $link->id);
    $component->assertSet('url', $link->url);
    $component->assertSet('description', $link->description);
});

test('updates link', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.org',
        'description' => 'Example Org',
    ]);

    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->call('edit', $link->id);
    $component->set('url', 'https://example.com');
    $component->set('description', 'Example');

    $component->call('update');
    $component->assertDispatched('link.updated');
    $component->assertDispatched('notification.created', message: 'Link updated.');

    $link->refresh();

    expect($user->links->count())->toBe(1);

    $link = $user->links->first();

    expect($link->url)
        ->toBe('https://example.com')
        ->and($link->description)
        ->toBe('Example');
});

it('prefixes with http or https if missing', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.org',
        'description' => 'Example Org',
    ]);

    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->call('edit', $link->id);
    $component->set('url', 'example.com');
    $component->set('description', 'Example');

    $component->call('update');
    $component->assertDispatched('link.updated');
    $component->assertDispatched('notification.created', message: 'Link updated.');

    $link->refresh();

    expect($user->links->count())->toBe(1);

    $link = $user->links->first();

    expect($link->url)
        ->toBe('https://example.com')
        ->and($link->description)
        ->toBe('Example');
});

test('link click count reset on url update', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.org',
        'click_count' => 10,
    ]);

    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->call('edit', $link->id);
    $component->set('url', 'https://example.com');
    $component->call('update');

    $link->refresh();

    expect($link->click_count)
        ->toBe(0)
        ->and($link->url)
        ->toBe('https://example.com');

});

test('link click count does not reset on only description update', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.org',
        'description' => 'Example Org',
        'click_count' => 10,
    ]);

    $component = Livewire::actingAs($user)->test(Edit::class);

    $component->call('edit', $link->id);
    $component->set('description', 'Example');
    $component->call('update');

    $link->refresh();

    expect($link->click_count)
        ->toBe(10)
        ->and($link->description)
        ->toBe('Example')
        ->and($link->url)
        ->toBe('https://example.org');
});
