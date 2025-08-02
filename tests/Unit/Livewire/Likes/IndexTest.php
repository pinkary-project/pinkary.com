<?php

declare(strict_types=1);

use App\Livewire\Likes\Index;
use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('render', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->assertOk();
});

test('render with likes', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);
    $likers = User::factory(5)->create();

    $likers->each(function (User $liker) use ($question): void {
        Like::factory()->create([
            'user_id' => $liker->id,
            'question_id' => $question->id,
        ]);
    });

    $component = Livewire::actingAs($user)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $likers->each(function (User $liker) use ($component): void {
        $component->assertSee($liker->name);
    });
});

test('users data has is_following and is_follower keys as expected', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);
    $likers = User::factory(5)->create();

    $likers->each(function (User $liker) use ($question): void {
        Like::factory()->create([
            'user_id' => $liker->id,
            'question_id' => $question->id,
        ]);
    });

    $component = Livewire::actingAs($user)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $users = $component->viewData('users');

    foreach ($users->items() as $liker) {
        expect($liker->hasAttribute('is_follower'))->toBeTrue();
        expect($liker->hasAttribute('is_following'))->toBeTrue();
        expect($liker->is_follower)->toBeFalse();
        expect($liker->is_following)->toBeFalse();
    }
});

test('users data has is_following and is_follower keys for non-owner viewers', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $owner->id]);
    $likers = User::factory(3)->create();

    $likers->each(function (User $liker) use ($question): void {
        Like::factory()->create([
            'user_id' => $liker->id,
            'question_id' => $question->id,
        ]);
    });

    $component = Livewire::actingAs($viewer)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->assertForbidden();
});

test('users data shows correct following relationships', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);
    $likers = User::factory(3)->create();

    $likers->each(function (User $liker) use ($question): void {
        Like::factory()->create([
            'user_id' => $liker->id,
            'question_id' => $question->id,
        ]);
    });

    $followedLiker = $likers->first();
    $user->following()->attach($followedLiker->id);

    $followerLiker = $likers->last();
    $followerLiker->following()->attach($user->id);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->set('isOpened', true);
    $component->refresh();

    $users = $component->viewData('users');
    $usersCollection = collect($users->items());
    $usersById = $usersCollection->keyBy('id');

    expect($usersById[$followedLiker->id]->is_following)->toBeTrue();
    expect($usersById[$followedLiker->id]->is_follower)->toBeFalse();

    expect($usersById[$followerLiker->id]->is_follower)->toBeTrue();
    expect($usersById[$followerLiker->id]->is_following)->toBeFalse();

    $middleLiker = $likers->skip(1)->first();
    expect($usersById[$middleLiker->id]->is_following)->toBeFalse();
    expect($usersById[$middleLiker->id]->is_follower)->toBeFalse();
});

test('only post owner can view likes', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    Like::factory()->create([
        'user_id' => $otherUser->id,
        'question_id' => $question->id,
    ]);

    $component = Livewire::actingAs($otherUser)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->assertForbidden();
});

test('show empty state when no likes', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $component->assertSee('No one has liked this post yet');
});

test('show pagination when many likes', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);
    $likers = User::factory(15)->create();

    $likers->each(function (User $liker) use ($question): void {
        Like::factory()->create([
            'user_id' => $liker->id,
            'question_id' => $question->id,
        ]);
    });

    $component = Livewire::actingAs($user)->test(Index::class, [
        'questionId' => $question->id,
    ]);

    $component->set('isOpened', true);

    $component->refresh();

    $likedUsers = $component->viewData('users');
    expect($likedUsers->items())->toHaveCount(10);
    expect($likedUsers->hasPages())->toBeTrue();
});
