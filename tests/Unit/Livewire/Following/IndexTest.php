<?php

declare(strict_types=1);

use App\Livewire\Following\Index;
use App\Models\User;
use Livewire\Livewire;

test('render', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertOk();
});

test('render with following', function () {
    $user = User::factory()->create();
    $following = User::factory(10)->create();

    $user->following()->sync($following->pluck('id'));

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $following->each(function (User $user) use ($component): void {
        $component->assertSee($user->name);
    });
});

test('render with follows you badge', function () {
    $user = User::factory()->create();
    $following = User::factory(10)->create();

    $user->following()->sync($following->pluck('id'));

    $followers = $following->random(5);

    $user->followers()->sync($followers->pluck('id'));

    $orderedFollowing = $user->following()->latest('followers.id')->get();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $orderedText = [];
    $orderedFollowing->each(function (User $user) use (&$orderedText, $followers): void {
        $orderedText[] = $user->username;
        if ($followers->contains($user)) {
            $orderedText[] = 'Follows you';
        }
    });

    $component->assertSeeInOrder($orderedText);
});

test('users data has is_following and is_follower keys as expected', function () {
    $user = User::factory()->create();
    $following = User::factory(10)->create();

    $user->following()->sync($following->pluck('id'));

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $component->viewData('following')->each(function (User $user): void {
        expect($user)->not->toHaveKey('is_following');
        expect($user)->toHaveKey('is_follower');
    });

    $anotherUser = User::factory()->create();

    $component = Livewire::actingAs($anotherUser)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $component->viewData('following')->each(function (User $user): void {
        expect($user)->toHaveKey('is_following');
        expect($user)->toHaveKey('is_follower');
    });
});

test('shouldHandleFollowingCount returns true when the user is viewing his profile', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    expect(invade($component->instance())->shouldHandleFollowingCount())->toBeTrue();

    $anotherUser = User::factory()->create();

    $component = Livewire::actingAs($anotherUser)->test(Index::class, [
        'userId' => $user->id,
    ]);

    expect($component->invade()->shouldHandleFollowingCount())->toBeFalse();
});
