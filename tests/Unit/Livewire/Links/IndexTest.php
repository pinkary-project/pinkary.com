<?php

declare(strict_types=1);

use App\Livewire\Links\Index;
use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Cache;
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

test('user can see qr download link', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertSee(route('qr-code.image'));
});

test('guest or random user cannot see qr download link', function () {
    $qrUser = User::factory()->create();

    $randomUser = User::factory()->create();

    $component = Livewire::actingAs($randomUser)->test(Index::class, [
        'userId' => $qrUser->id,
    ]);

    $component->assertDontSee(route('qr-code.image'));
});

test('when user click his own links the clicks counter is not incremented', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('click', $link->id);

    expect($link->refresh()->click_count)->toBe(0);
});

test('when user click another user link the clicks counter is incremented', function () {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $anotherUser->id,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $anotherUser->id,
    ]);

    $component->call('click', $link->id);

    expect($link->refresh()->click_count)->toBe(1);
});

test('click counter is not incremented if user already clicked the link during the day', function () {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $anotherUser->id,
        'click_count' => 30,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $anotherUser->id,
    ]);

    Cache::shouldReceive('has')
        ->once()
        ->andReturn(true);

    $component->call('click', $link->id);

    expect($link->refresh()->click_count)->toBe(30);
});

test('reset avatar', function () {

    $user = User::factory()->create([
        'avatar_updated_at' => now()->subDay(),
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('resetAvatar');

    $component->assertDispatched('notification.created', message: 'Avatar reset.');
});

test('cannot reset avatar if user is not the owner', function () {
    $user = User::factory()->create([
        'avatar_updated_at' => null,
    ]);

    $anotherUser = User::factory()->create([
        'avatar_updated_at' => null,
    ]);

    $component = Livewire::actingAs($anotherUser)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('resetAvatar');

    $component->assertDispatched('notification.created', message: 'You have to wait 24 hours before resetting the avatar again.');

    $this->assertNull($user->avatar_updated_at);

    $this->assertNull($anotherUser->avatar_updated_at);
});

test('cannot reset avatar if user avatar updated recently', function () {

    $avatarLastUpdated = now()->subHour()->format('Y-m-d H:i:s');

    $user = User::factory()->create([
        'avatar_updated_at' => $avatarLastUpdated,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('resetAvatar');

    $component->assertDispatched('notification.created', message: 'You have to wait 24 hours before resetting the avatar again.');

    $this->assertEquals($avatarLastUpdated, $user->avatar_updated_at->format('Y-m-d H:i:s'));
});

test('count to be abbreviated', function () {

    $user = User::factory()
        ->hasLinks(5, new Sequence(
            ['click_count' => 125],
            ['click_count' => 1250],
            ['click_count' => 12500],
            ['click_count' => 125000],
            ['click_count' => 1250000],
        ))
        ->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertSee('125')
        ->assertSee('1K')
        ->assertSee('12K')
        ->assertSee('125K')
        ->assertSee('1M');
});
