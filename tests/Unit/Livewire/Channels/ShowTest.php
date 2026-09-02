<?php

declare(strict_types=1);

use App\Livewire\Channels\Show;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('render', function (): void {
    $channel = Channel::factory()->create(['name' => 'Testing Channel']);

    Livewire::test(Show::class, ['channel' => $channel])
        ->assertOk()
        ->assertSee('No posts in this channel yet');
});

test('ignore question', function (): void {
    $user = User::factory()->create();
    $channel = Channel::factory()->create();
    $question = Question::factory()->forChannel($channel)->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_ignored' => false,
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['channel' => $channel])
        ->call('ignore', $question->id)
        ->assertDispatched('question.ignored');

    expect($question->fresh()->is_ignored)->toBeTrue();
});

test('load more', function (): void {
    $channel = Channel::factory()->create();

    $component = Livewire::test(Show::class, ['channel' => $channel]);

    expect($component->get('perPage'))->toBe(5);

    $component->call('loadMore');

    expect($component->get('perPage'))->toBe(10);
});

test('refresh', function (): void {
    $channel = Channel::factory()->create();

    Livewire::test(Show::class, ['channel' => $channel])
        ->call('refresh')
        ->assertOk();
});
