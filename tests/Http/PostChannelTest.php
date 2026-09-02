<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Livewire\Questions\Create;
use App\Livewire\Questions\Edit;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('creates a post assigned to a channel and increments questions count', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create(['questions_count' => 0]);

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'Testing a post with a selected channel')
        ->set('channelId', $channel->id)
        ->call('store')
        ->assertHasNoErrors();

    $question = Question::where('answer', 'Testing a post with a selected channel')->first();

    expect($question)->not->toBeNull()
        ->and($question->channel_id)->toBe($channel->id)
        ->and($channel->fresh()->questions_count)->toBe(1);
});

it('can edit a post and change its channel', function (): void {
    $user = User::factory()->create();
    $channelA = Channel::factory()->create(['questions_count' => 1]);
    $channelB = Channel::factory()->create(['questions_count' => 0]);

    $question = Question::factory()->forChannel($channelA)->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => '__UPDATE__',
        'answer' => 'Initial update text',
        'answer_created_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['questionId' => $question->id])
        ->set('answer', 'Updated text with channel B')
        ->set('channelId', $channelB->id)
        ->call('update')
        ->assertHasNoErrors();

    expect($question->fresh()->channel_id)->toBe($channelB->id)
        ->and($channelA->fresh()->questions_count)->toBe(0)
        ->and($channelB->fresh()->questions_count)->toBe(1);
});

it('assigns channel to root post when posting a thread', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create(['questions_count' => 0]);

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'Root post with channel')
        ->set('channelId', $channel->id)
        ->set('threadPosts', ['Thread reply 1', 'Thread reply 2'])
        ->call('store')
        ->assertHasNoErrors();

    $root = Question::where('answer', 'Root post with channel')->first();
    expect($root)->not->toBeNull()
        ->and($root->channel_id)->toBe($channel->id)
        ->and($root->parent_id)->toBeNull()
        ->and($channel->fresh()->questions_count)->toBe(1);

    $thread1 = Question::where('answer', 'Thread reply 1')->first();
    expect($thread1)->not->toBeNull()
        ->and($thread1->channel_id)->toBeNull()
        ->and($thread1->root_id)->toBe($root->id);

    $thread2 = Question::where('answer', 'Thread reply 2')->first();
    expect($thread2)->not->toBeNull()
        ->and($thread2->channel_id)->toBeNull()
        ->and($thread2->root_id)->toBe($root->id);
});

it('can create a new channel directly from composer and select it', function (): void {
    $user = User::factory()->create();

    expect(Channel::count())->toBe(0);

    $test = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->call('createChannel', 'Web Development')
        ->assertHasNoErrors()
        ->assertSet('channelName', 'Web Development');

    expect(Channel::count())->toBe(0);

    $test->set('content', 'First post in Web Development')
        ->call('store')
        ->assertHasNoErrors();

    $channel = Channel::where('name', 'Web Development')->first();
    expect($channel)->not->toBeNull()
        ->and($channel->slug)->toBe('web-development')
        ->and($channel->questions_count)->toBe(1);

    $post = Question::where('answer', 'First post in Web Development')->first();
    expect($post)->not->toBeNull()
        ->and($post->channel_id)->toBe($channel->id);
});

it('can clear selected channel from composer', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->call('selectChannel', $channel->id)
        ->assertSet('channelId', $channel->id)
        ->call('selectChannel', null)
        ->assertSet('channelId', null);
});

it('mounts with pre-selected channelId when specified', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id, 'channelId' => $channel->id])
        ->assertSet('channelId', $channel->id);
});

it('can search channels dynamically', function (): void {
    $user = User::factory()->create();
    Channel::factory()->create(['name' => 'Laravel']);
    Channel::factory()->create(['name' => 'Livewire']);
    Channel::factory()->create(['name' => 'Vue']);

    $component = Livewire::actingAs($user)->test(Create::class, ['toId' => $user->id]);

    $results = $component->instance()->searchChannels('Live');
    expect($results)->toHaveCount(1)
        ->and($results[0]['name'])->toBe('Livewire');

    $all = $component->instance()->searchChannels('');
    expect($all)->toHaveCount(3);
});

it('validates channel name when creating a channel in composer', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->call('createChannel', '')
        ->assertHasErrors(['newChannel'])
        ->call('createChannel', 'A')
        ->assertHasErrors(['newChannel'])
        ->call('createChannel', 'Invalid @ Name!')
        ->assertHasErrors(['newChannel'])
        ->call('createChannel', '---')
        ->assertHasErrors(['newChannel']);
});

it('reuses existing channel when creating a channel with same slug', function (): void {
    $user = User::factory()->create();
    $existing = Channel::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->call('createChannel', 'laravel')
        ->assertHasNoErrors()
        ->assertSet('channelId', $existing->id);

    expect(Channel::where('slug', 'laravel')->count())->toBe(1);
});

it('prevents unverified users from creating channels', function (): void {
    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->call('createChannel', 'Laravel')
        ->assertRedirect(route('verification.notice'));
});

it('can update post channel in edit composer', function (): void {
    $user = User::factory()->create();
    $channel1 = Channel::factory()->create(['questions_count' => 1]);
    $channel2 = Channel::factory()->create(['questions_count' => 0]);

    $question = Question::factory()->sharedUpdate()->forChannel($channel1)->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer' => 'Original answer',
        'answer_created_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['questionId' => $question->id])
        ->assertSet('channelId', $channel1->id)
        ->call('selectChannel', $channel2->id)
        ->call('update')
        ->assertHasNoErrors();

    expect($question->fresh()->channel_id)->toBe($channel2->id)
        ->and($channel1->fresh()->questions_count)->toBe(0)
        ->and($channel2->fresh()->questions_count)->toBe(1);
});

it('can create and select channel from edit composer', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->sharedUpdate()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer' => 'Original answer',
        'answer_created_at' => now(),
        'channel_id' => null,
    ]);

    $test = Livewire::actingAs($user)
        ->test(Edit::class, ['questionId' => $question->id])
        ->call('createChannel', 'Dev Channel')
        ->assertHasNoErrors()
        ->assertSet('channelName', 'Dev Channel');

    expect(Channel::count())->toBe(0);

    $test->call('update')
        ->assertHasNoErrors();

    $channel = Channel::where('name', 'Dev Channel')->first();
    expect($channel)->not->toBeNull()
        ->and($question->fresh()->channel_id)->toBe($channel->id);
});

it('can search channels from edit composer', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer' => 'Original answer',
        'answer_created_at' => now(),
    ]);

    Channel::factory()->create(['name' => 'PHP']);
    Channel::factory()->create(['name' => 'Python']);

    $component = Livewire::actingAs($user)->test(Edit::class, ['questionId' => $question->id]);

    $results = $component->instance()->searchChannels('Py');
    expect($results)->toHaveCount(1)
        ->and($results[0]['name'])->toBe('Python');

    $all = $component->instance()->searchChannels('');
    expect($all)->toHaveCount(2);
});

it('ensures only root shared updates can have channels', function (): void {
    $user = User::factory()->create();
    $recipient = User::factory()->create();
    $channel = Channel::factory()->create();

    // 1. Direct Question (not shared update)
    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $recipient->id, 'channelId' => $channel->id])
        ->set('content', 'Direct question to someone')
        ->call('store')
        ->assertHasNoErrors();

    $question = Question::where('content', 'Direct question to someone')->first();
    expect($question->channel_id)->toBeNull();

    // 2. Comment / Reply
    $parent = Question::factory()->create(['to_id' => $user->id, 'from_id' => $user->id, 'answer' => 'Parent update', 'content' => '__UPDATE__']);
    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id, 'parentId' => $parent->id, 'channelId' => $channel->id])
        ->set('content', 'Comment on post')
        ->call('store')
        ->assertHasNoErrors();

    $comment = Question::where('answer', 'Comment on post')->first();
    expect($comment->channel_id)->toBeNull();
});
