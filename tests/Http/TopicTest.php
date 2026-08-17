<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Livewire\Topics\Index;
use App\Livewire\Topics\Show;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use Livewire\Livewire;

it('displays discoverable active topics on the topics index page', function (): void {
    $topicA = Topic::factory()->create(['name' => 'Laravel', 'slug' => 'laravel', 'is_active' => true, 'is_discoverable' => true]);
    $topicB = Topic::factory()->create(['name' => 'Secret', 'slug' => 'secret', 'is_active' => true, 'is_discoverable' => false]);

    $this->get(route('topics.index'))
        ->assertOk()
        ->assertSee('Laravel')
        ->assertDontSee('Secret');
});

it('allows filtering topics by search query', function (): void {
    Topic::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    Topic::factory()->create(['name' => 'PHP', 'slug' => 'php']);

    Livewire::test(Index::class)
        ->set('search', 'Lara')
        ->assertSee('Laravel')
        ->assertDontSee('PHP');
});

it('allows an authenticated user to follow and unfollow a topic', function (): void {
    $user = User::factory()->create();
    $topic = Topic::factory()->create(['name' => 'Filament', 'slug' => 'filament']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('toggleFollow', $topic->id)
        ->assertDispatched('notification.created');

    expect($user->fresh()->followedTopics->pluck('id')->all())->toContain($topic->id);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('toggleFollow', $topic->id)
        ->assertDispatched('notification.created');

    expect($user->fresh()->followedTopics->pluck('id')->all())->not->toContain($topic->id);
});

it('displays the topic detail page with its posts feed and followers count', function (): void {
    $topic = Topic::factory()->create(['name' => 'Laravel', 'slug' => 'laravel', 'description' => 'Everything Laravel']);
    $user = User::factory()->create();
    $topic->followers()->attach($user->id);

    $author = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
        'topic_id' => $topic->id,
        'answer' => 'Building with Laravel 12!',
    ]);

    $this->get(route('topics.show', $topic))
        ->assertOk()
        ->assertSee('Laravel')
        ->assertSee('Everything Laravel')
        ->assertSee('1 follower')
        ->assertSee('Building with Laravel 12!');
});

it('allows sorting topic feed by recent and trending', function (): void {
    $topic = Topic::factory()->create(['name' => 'PHP', 'slug' => 'php']);
    $author = User::factory()->create();

    $postA = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
        'topic_id' => $topic->id,
        'answer' => 'Post Alpha Trending',
        'answer_created_at' => now(),
    ]);

    $postB = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
        'topic_id' => $topic->id,
        'answer' => 'Post Beta Recent',
        'answer_created_at' => now(),
    ]);

    \App\Models\Like::factory()->count(5)->create(['question_id' => $postA->id]);

    Livewire::test(Show::class, ['topic' => $topic])
        ->call('setSort', 'trending')
        ->assertSet('sort', 'trending')
        ->assertSee("question-{$postA->id}");

    $this->get(route('topics.show', ['topic' => $topic, 'sort' => 'trending']))
        ->assertOk()
        ->assertSee('Post Alpha Trending');
});
