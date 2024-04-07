<?php

declare(strict_types=1);

use App\Livewire\Feed;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('renders questions with answers', function () {
    Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $component = Livewire::test(Feed::class);

    $component->assertSee('This is the answer')
        ->assertDontSee('There are no questions to show.');
});

test('do not renders questions without answers', function () {
    Question::factory()->create([
        'answer' => null,
    ]);

    $component = Livewire::test(Feed::class);

    $component->assertSee('There are no questions to show.');
});

test('do not renders ignored questions', function () {
    Question::factory()->create([
        'answer' => 'This is the answer',
        'is_ignored' => true,
    ]);

    $component = Livewire::test(Feed::class);

    $component->assertSee('There are no questions to show.');
});

test('ignore', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Feed::class);

    $component->assertSee($question->content);

    $component->dispatch('question.ignore', $question->id);

    $component->assertDontSee($question->content);

    expect($question->fresh()->is_ignored)->toBeTrue();
});

test('ignore auth', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $userA->id,
        'to_id' => $userB->id,
    ]);

    $component = Livewire::actingAs($userA)->test(Feed::class);

    $component->dispatch('question.ignore', $question->id);

    $component->assertStatus(403);

    expect($question->fresh()->is_ignored)->not->toBeTrue();
});

it('can see the question in the feed', function () {
    Question::factory(15)->create();

    Livewire::test(Feed::class)
        ->assertViewHas('questions', function ($questions) {
            return count($questions) === 10;
        });

    Livewire::test(Feed::class, ['page' => 2])
        ->assertViewHas('questions', function ($questions) {
            return count($questions) === 5;
        });
});
