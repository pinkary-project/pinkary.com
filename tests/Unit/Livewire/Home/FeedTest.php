<?php

declare(strict_types=1);

use App\Livewire\Home\Feed;
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
    $user = User::factory()->create();

    Question::factory()->create([
        'answer' => null,
    ]);

    $component = Livewire::actingAs($user)->test(Feed::class);

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

test('load more', function () {
    $user = User::factory()->create();

    $questions = Question::factory(120)->create();

    $component = Livewire::actingAs($user)->test(Feed::class);

    $component->call('loadMore');
    $component->assertSet('perPage', 10);

    $component->call('loadMore');
    $component->assertSet('perPage', 15);

    foreach (range(1, 25) as $i) {
        $component->call('loadMore');
    }

    $component->assertSet('perPage', 100);
});
