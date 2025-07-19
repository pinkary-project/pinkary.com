<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('poll button is visible only for shared updates', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id]);

    $component->assertSee('Create a poll');

    $otherUser = User::factory()->create();
    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $otherUser->id]);

    $component->assertDontSee('Create a poll');
});

test('poll button is not visible for replies', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id, 'parentId' => $question->id]);

    $component->assertDontSee('Create a poll');
});

test('can toggle poll mode', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->assertSet('isPoll', false)
        ->call('togglePoll')
        ->assertSet('isPoll', true)
        ->call('togglePoll')
        ->assertSet('isPoll', false);
});

test('can add poll options', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('isPoll', true)
        ->assertCount('pollOptions', 2)
        ->call('addPollOption')
        ->assertCount('pollOptions', 3)
        ->call('addPollOption')
        ->assertCount('pollOptions', 4);

    $component->call('addPollOption')
        ->assertCount('pollOptions', 4);
});

test('can remove poll options', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('isPoll', true)
        ->call('addPollOption')
        ->call('addPollOption')
        ->assertCount('pollOptions', 4)
        ->call('removePollOption', 0)
        ->assertCount('pollOptions', 3)
        ->call('removePollOption', 0)
        ->assertCount('pollOptions', 2);

    $component->call('removePollOption', 0)
        ->assertCount('pollOptions', 2);
});

test('poll options reset when toggling off poll mode', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('isPoll', true)
        ->set('pollOptions', ['Option 1', 'Option 2', 'Option 3'])
        ->call('togglePoll')
        ->assertSet('isPoll', false)
        ->assertSet('pollOptions', ['', '']);
});

test('can create a poll with valid options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue', 'Green'])
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->where('is_poll', true)
        ->first();

    expect($question)->not->toBeNull();
    expect($question->pollOptions)->toHaveCount(3);
    expect($question->pollOptions->pluck('text')->toArray())->toBe(['Red', 'Blue', 'Green']);
});

test('validates poll requires at least 2 options', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', '   '])
        ->call('store');

    $questionExists = Question::where('content', '__UPDATE__')->where('is_poll', true)->exists();
    expect($questionExists)->toBeFalse();

    expect($component->instance()->getErrorBag()->isNotEmpty())->toBeTrue();
});

test('validates poll options are required when poll is enabled', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['', ''])
        ->call('store')
        ->assertHasErrors('pollOptions.0');
});

test('validates poll cannot have more than 4 options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue', 'Green', 'Yellow', 'Purple'])
        ->call('store')
        ->assertHasErrors(['pollOptions' => 'A poll can have maximum 4 options.']);
});

test('validates poll option length', function (): void {
    $user = User::factory()->create();
    $longOption = str_repeat('a', 101);

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', $longOption])
        ->call('store')
        ->assertHasErrors(['pollOptions.1']);
});

test('creates regular question when poll is disabled', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'This is a regular update')
        ->set('isPoll', false)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->where('is_poll', false)
        ->first();

    expect($question)->not->toBeNull();
    expect($question->pollOptions)->toHaveCount(0);
});

test('resets poll state after successful submission', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->call('store')
        ->assertSet('isPoll', false)
        ->assertSet('pollOptions', ['', '']);
});

test('trims whitespace from poll options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['  Red  ', '  Blue  ', '  Green  '])
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->where('is_poll', true)
        ->first();

    expect($question->pollOptions->pluck('text')->toArray())->toBe(['Red', 'Blue', 'Green']);
});
