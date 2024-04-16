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

test('ignore', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->call('ignore');

    $component->assertDispatched('notification.created', message: 'Question ignored.');
    $component->assertDispatched('question.ignore', questionId: $this->question->id);
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

test('cannot answer a question that has already been answered', function () {
    $this->question->update([
        'answer' => 'Hello World',
        'answered_at' => now(),
    ]);

    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('answer', 'Hello World');

    $component->call('update');

    $component->assertDispatched('notification.created', message: 'Sorry, something unexpected happened. Please try again.');

    $component->assertRedirect(route('profile.show', ['username' => $this->question->to->username]));
});

test('cannot answer a question that has been reported or ignored', function () {
    $this->question->update([
        'is_reported' => true,
    ]);

    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('answer', 'Hello World');

    $component->call('update');

    $component->assertDispatched('notification.created', message: 'Sorry, something unexpected happened. Please try again.');

    $this->question->update([
        'is_reported' => false,
        'is_ignored' => true,
    ]);

    $component->call('update');

    $component->assertDispatched('notification.created', message: 'Sorry, something unexpected happened. Please try again.');

    $component->assertRedirect(route('profile.show', ['username' => $this->question->to->username]));
});
