<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Livewire\Questions\Create;
use App\Livewire\Questions\Edit;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use Livewire\Livewire;

it('allows creating a top-level post without a topic', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'Testing post content with no topic')
        ->set('topicId', null)
        ->call('store')
        ->assertHasNoErrors();

    $post = Question::where('answer', 'Testing post content with no topic')->first();
    expect($post)->not->toBeNull()
        ->and($post->topic_id)->toBeNull();
});

it('allows creating a post with a valid topic', function (): void {
    $user = User::factory()->create();
    $topic = Topic::factory()->create(['name' => 'Laravel']);

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'Testing post content with topic')
        ->set('topicId', $topic->id)
        ->call('store')
        ->assertHasNoErrors();

    $post = Question::where('answer', 'Testing post content with topic')->first();
    expect($post)->not->toBeNull()
        ->and($post->topic_id)->toBe($topic->id);
});

it('allows the author to update the topic of an existing post', function (): void {
    $user = User::factory()->create();
    $topicA = Topic::factory()->create(['name' => 'PHP']);
    $topicB = Topic::factory()->create(['name' => 'Laravel']);

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'from_id' => $user->id,
        'topic_id' => $topicA->id,
        'answer' => 'Original content',
        'answer_created_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['questionId' => $question->id])
        ->set('answer', 'Updated content')
        ->set('topicId', $topicB->id)
        ->call('update')
        ->assertDispatched('notification.created');

    expect($question->fresh()->topic_id)->toBe($topicB->id);
});

it('allows creating a topic on the fly in the composer', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->call('selectOrCreateTopic', 'Artificial Intelligence')
        ->set('content', 'Building with modern AI!')
        ->call('store')
        ->assertHasNoErrors();

    $createdTopic = Topic::where('slug', 'artificial-intelligence')->first();
    expect($createdTopic)->not->toBeNull()
        ->and($createdTopic->name)->toBe('Artificial Intelligence');

    $post = Question::where('answer', 'Building with modern AI!')->first();
    expect($post)->not->toBeNull()
        ->and($post->topic_id)->toBe($createdTopic->id);
});
