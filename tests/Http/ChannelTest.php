<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Livewire\Channels\Show;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('renders the channel show page', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'PHP Development',
        'slug' => 'php-development',
    ]);

    $response = $this->get('/channels/php-development');

    $response->assertOk()
        ->assertSee('PHP Development')
        ->assertSee('0 posts')
        ->assertSee('data-current-channel-id="'.$channel->id.'"', false);
});

it('keeps the channel selected after posting on the channel page', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Questions\Create::class, [
            'toId' => $user->id,
            'channelId' => $channel->id,
        ])
        ->set('content', 'First post in Laravel channel')
        ->call('store');

    expect($component->get('channelId'))->toBe($channel->id);
});

it('resets the channel after posting when not in a channel context', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create([
        'name' => 'General',
        'slug' => 'general',
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Questions\Create::class, [
            'toId' => $user->id,
        ])
        ->set('channelId', $channel->id)
        ->set('content', 'First post with temporary channel selection')
        ->call('store');

    expect($component->get('channelId'))->toBeNull();
});

it('dispatches channel-count-updated with incremented count when post is created in channel', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
        'questions_count' => 3,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Questions\Create::class, [
            'toId' => $user->id,
            'channelId' => $channel->id,
        ])
        ->set('content', 'A brand new post in channel')
        ->call('store')
        ->assertDispatched('channel-count-updated', channelId: $channel->id, count: 4);

    expect($channel->fresh()->questions_count)->toBe(4);
});

it('refreshes the channel and dispatches channel-count-updated when channel show component receives question.created', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
        'questions_count' => 3,
    ]);

    $channel->increment('questions_count');

    Livewire::test(Show::class, ['channel' => $channel])
        ->dispatch('question.created')
        ->assertDispatched('channel-count-updated', channelId: $channel->id, count: 4);
});

it('renders channel questions in the show component', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create(['name' => 'Design']);
    Question::factory()->for($channel)->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => '__UPDATE__',
        'answer' => 'A post specifically about UI design',
        'answer_created_at' => now(),
    ]);

    Livewire::test(Show::class, ['channel' => $channel])
        ->assertSee('A post specifically about UI design');
});

it('omits channel badge link on post cards when in channel context', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create(['name' => 'Design', 'slug' => 'design']);
    $question = Question::factory()->for($channel)->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => '__UPDATE__',
        'answer' => 'A post specifically about UI design',
        'answer_created_at' => now(),
    ]);

    // On regular views (inChannel = false), the channel badge link is visible
    Livewire::test(\App\Livewire\Questions\Show::class, [
        'questionId' => $question->id,
        'inChannel' => false,
    ])->assertSee(route('channels.show', $channel));

    // In a channel feed (inChannel = true), the channel badge link is omitted
    Livewire::test(\App\Livewire\Questions\Show::class, [
        'questionId' => $question->id,
        'inChannel' => true,
    ])->assertDontSee(route('channels.show', $channel));
});

it('omits channel badge on post cards on the channel page', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create([
        'name' => 'DevOps',
        'slug' => 'devops',
    ]);
    Question::factory()->for($channel)->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => '__UPDATE__',
        'answer' => 'Kubernetes post in DevOps channel',
        'answer_created_at' => now(),
    ]);

    $response = $this->get('/channels/devops');

    $response->assertOk()
        ->assertSee('Kubernetes post in DevOps channel')
        ->assertDontSee('text-[0.72rem] font-medium whitespace-nowrap text-slate-600', false);
});
