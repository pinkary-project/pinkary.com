<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Enums\FeedVisibility;
use App\Livewire\Feeds\Create;
use App\Livewire\Feeds\Edit;
use App\Livewire\Feeds\Index;
use App\Models\Feed;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use Livewire\Livewire;

it('allows an authenticated user to create a custom feed with topics and people', function (): void {
    $user = User::factory()->create();
    $topic = Topic::factory()->create(['name' => 'Laravel']);
    $person = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('name', 'Laravel Ecosystem')
        ->set('description', 'Posts from Laravel topic and top developers')
        ->set('visibility', FeedVisibility::Public->value)
        ->call('addTopic', $topic->id)
        ->call('addPerson', $person->id)
        ->call('store')
        ->assertRedirect();

    $feed = Feed::where('name', 'Laravel Ecosystem')->first();
    expect($feed)->not->toBeNull()
        ->and($feed->user_id)->toBe($user->id)
        ->and($feed->visibility)->toBe(FeedVisibility::Public)
        ->and($feed->topics->pluck('id')->all())->toContain($topic->id)
        ->and($feed->people->pluck('id')->all())->toContain($person->id);
});

it('allows an authenticated user to edit their custom feed', function (): void {
    $user = User::factory()->create();
    $feed = Feed::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);
    $newTopic = Topic::factory()->create(['name' => 'Vue.js']);

    Livewire::actingAs($user)
        ->test(Edit::class, ['feed' => $feed])
        ->set('name', 'Updated Name')
        ->call('addTopic', $newTopic->id)
        ->call('update')
        ->assertRedirect();

    expect($feed->fresh()->name)->toBe('Updated Name')
        ->and($feed->fresh()->topics->pluck('id')->all())->toContain($newTopic->id);
});

it('allows a user to follow and unfollow a public custom feed', function (): void {
    $author = User::factory()->create();
    $follower = User::factory()->create();
    $feed = Feed::factory()->create(['user_id' => $author->id, 'visibility' => FeedVisibility::Public]);

    Livewire::actingAs($follower)
        ->test(Index::class)
        ->call('toggleFollow', $feed->id)
        ->assertDispatched('notification.created');

    expect($follower->fresh()->followedFeeds->pluck('id')->all())->toContain($feed->id);

    Livewire::actingAs($follower)
        ->test(Index::class)
        ->call('toggleFollow', $feed->id)
        ->assertDispatched('notification.created');

    expect($follower->fresh()->followedFeeds->pluck('id')->all())->not->toContain($feed->id);
});

it('displays posts matching custom feed topic or user criteria', function (): void {
    $owner = User::factory()->create();
    $topic = Topic::factory()->create(['name' => 'Laravel']);
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();

    $feed = Feed::factory()->create(['user_id' => $owner->id, 'visibility' => FeedVisibility::Public]);
    $feed->topics()->attach($topic->id);
    $feed->people()->attach($creator->id);

    $postFromTopic = Question::factory()->create([
        'to_id' => $otherUser->id,
        'from_id' => $otherUser->id,
        'topic_id' => $topic->id,
        'answer' => 'Topic specific post',
    ]);

    $postFromCreator = Question::factory()->create([
        'to_id' => $creator->id,
        'from_id' => $creator->id,
        'topic_id' => null,
        'answer' => 'Creator specific post',
    ]);

    $unrelatedPost = Question::factory()->create([
        'to_id' => $otherUser->id,
        'from_id' => $otherUser->id,
        'topic_id' => null,
        'answer' => 'Completely unrelated post',
    ]);

    $this->get(route('feeds.show', $feed))
        ->assertOk()
        ->assertSee('Topic specific post')
        ->assertSee('Creator specific post')
        ->assertDontSee('Completely unrelated post');
});

it('prevents non-owners from viewing private feeds', function (): void {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $privateFeed = Feed::factory()->create(['user_id' => $owner->id, 'visibility' => FeedVisibility::Private]);

    $this->actingAs($visitor)
        ->get(route('feeds.show', $privateFeed))
        ->assertForbidden();
});
