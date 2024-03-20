<?php

declare(strict_types=1);

use App\Livewire\Links\Index;
use App\Models\Link;
use App\Models\User;
use Livewire\Livewire;

test('renders a list of links', function () {
    $user = User::factory()->create();

    $links = Link::factory(3)->create([
        'user_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    foreach ($links as $link) {
        $component->assertSee($link->url);
        $component->assertSee($link->description);
    }
});

test('stores links order', function () {
    $user = User::factory()->create();

    $links = Link::factory(3)->create([
        'user_id' => $user->id,
    ]);

    $anotherUserLink = Link::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('storeSort', [
        $links[2]->id,
        $links[0]->id,
        $links[1]->id,
        23456789, // non-existing link
        $anotherUserLink->id,
    ]);

    $user->refresh();

    expect($user->links_sort)->toBe([
        $links[2]->id,
        $links[0]->id,
        $links[1]->id,
    ]);
});

test('destroy link', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('destroy', $link->id);

    $user->refresh();

    expect($user->links->count())->toBe(0)
        ->and($link->fresh())->toBeNull();
});

test('is refreshable', function () {
    $user = User::factory()->create();

    $component = Livewire::test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('refresh');

    $this->expectNotToPerformAssertions();
});
