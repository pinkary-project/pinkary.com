<?php

declare(strict_types=1);

use App\Livewire\Questions\Edit;
use App\Livewire\Questions\Show;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->question = Question::factory()->create();

    $this->actingAs($this->question->to);
});

test('render', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->assertSee('Edit your update...');
});

test('update', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('update');

    expect($this->question->fresh()->content)->toBe('Hello World');
});

test('update auth', function () {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->actingAs(User::factory()->create());

    $component->set('content', 'Hello World');

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

    $component->set('content', "\u{200E}");

    $component->call('update');

    $component->assertHasErrors([
        'content' => 'The content field cannot contain blank characters.',
    ]);
});

test('can edit a question that is an update', function () {
    $this->question->update([
        'content' => 'foo',
        'is_update' => true,
        'from_id' => $this->question->to->id,
        'updated_at' => now(),
        'created_at' => now()->subHour(),
    ]);

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->set('content', 'Hello World')
        ->call('update')
        ->assertDispatched('notification.created', message: 'Update Edited.')
        ->assertDispatched('close-modal', "question.edit.update.{$this->question->id}")
        ->assertDispatched('question.updated');

    expect($this->question->fresh()->content)->toBe('Hello World');

    Livewire::test(Show::class, [
        'questionId' => $this->question->id,
    ])
        ->assertSee('Hello World')
        ->assertSee('Edited');
});

test('edited questions display raw content in the form', function () {
    $this->question->update([
        'content' => "Hello @{$this->question->from->username} - How are you doing?",
        'is_update' => true,
    ]);

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->assertSeeHtml("Hello @{$this->question->from->username} - How are you doing?")
        ->assertSet('content', "Hello @{$this->question->from->username} - How are you doing?");
});

test('likes are reset when an content is updated', function () {
    $this->question->update([
        'content' => 'foo',
        'from_id' => $this->question->to->id,
        'is_update' => true,
    ]);

    $this->question->likes()->create([
        'user_id' => $this->question->to->id,
    ]);

    expect($this->question->likes()->count())->toBe(1);

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->set('content', 'Hello World')
        ->call('update');

    expect($this->question->likes()->count())->toBe(0);
});

test('cannot edit an answer after 24 hours', function () {
    $this->question->update([
        'content' => 'foo',
        'is_update' => true,
        'from_id' => $this->question->to_id,
        'created_at' => now()->subHours(25),
    ]);

    $this->question->fresh();

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->set('content', 'Hello World')
        ->call('update')
        ->assertDispatched('notification.created', message: 'Posts cannot be edited after 24 hours.');
});

test('cannot answer a question that has been reported or ignored', function () {
    $this->question->update([
        'is_reported' => true,
    ]);

    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('content', 'Hello World');

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
