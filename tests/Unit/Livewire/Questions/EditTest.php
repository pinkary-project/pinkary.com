<?php

declare(strict_types=1);

use App\Livewire\Questions\Edit;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->question = Question::factory()->create([
        'answer' => null,
        'answered_at' => null,
    ]);

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

    $component->set('answer', 'Hello World');

    $component->call('update');

    expect($this->question->fresh()->answer)->toBe('Hello World');
});

test('update auth', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->actingAs(User::factory()->create());

    $component->set('answer', 'Hello World');

    $component->call('update');

    expect($this->question->fresh()->answer)->toBeNull();

    $component->assertStatus(403);
});

test('report', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->call('report');

    expect($this->question->fresh()->is_reported)->toBeTrue();
});

test('report auth', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->actingAs(User::factory()->create());

    $component->call('report');

    expect($this->question->fresh()->is_reported)->toBeFalse();

    $component->assertStatus(403);
});

test('destroy', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->call('destroy');

    $component->assertDispatched('notification.created', 'Question ignored.');
    $component->assertDispatched('question.destroy', questionId: $this->question->id);
});

test('cannot update with blank characters', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->actingAs(User::factory()->create());

    $component->set('answer', "\u{200E}");

    $component->call('update');

    $component->assertHasErrors([
        'answer' => 'The answer field cannot contain blank characters.',
    ]);
});
