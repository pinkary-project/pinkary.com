<?php

declare(strict_types=1);

use App\Livewire\Answers\Edit;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->question = Question::factory()->create();
    $this->answer = Answer::factory()->create(['question_id' => $this->question->id]);

    $this->actingAs($this->question->to);
});

test('render', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->assertSee('Write your answer...');
});

test('update', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'Updated Answer');

    $component->call('update');

    expect($this->answer->fresh()->content)->toBe('Updated Answer');
});

test('update auth', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->actingAs(User::factory()->create());

    $component->set('content', 'Updated Answer');

    $component->call('update');

    expect($this->answer->fresh()->content)->not->toBe('Updated Answer');

    $component->assertStatus(403);
});

test('cannot update after 24 hours', function () {
    $this->answer->update(['created_at' => now()->subHours(25)]);

    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'Updated Answer');

    $component->call('update');

    $component->assertDispatched('notification.created', message: 'Answer cannot be edited after 24 hours.');
});

test('cannot update with blank characters', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', "\u{200E}");

    $component->call('update');

    $component->assertHasErrors([
        'content' => 'The content field cannot contain blank characters.',
    ]);
});

test('can edit a question to add a new answer', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'New Answer');

    $component->call('update');

    expect($this->question->fresh()->answer->content)->toBe('New Answer');
});

test('cannot edit a question that is an update', function () {
    $this->question->update(['is_update' => true]);

    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'New Answer');

    $component->call('update');

    expect($this->question->fresh()->answer)->not->toBeNull();
});

test('can edit an existing answer', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'New Answer');

    $component->call('update');

    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'Updated Answer');

    $component->call('update');

    expect($this->answer->fresh()->content)->toBe('Updated Answer');
});
