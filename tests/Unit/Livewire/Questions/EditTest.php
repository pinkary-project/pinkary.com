<?php

declare(strict_types=1);

use App\Livewire\Questions\Edit;
use App\Livewire\Questions\Show;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->question = Question::factory()->create([
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $this->actingAs($this->question->to);
});

test('render', function (): void {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->assertSee('Write your answer...');
});

test('update', function (): void {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->set('answer', 'Hello World');

    $component->call('update');

    expect($this->question->fresh()->answer)->toBe('Hello World');
});

test('update auth', function (): void {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->actingAs(User::factory()->create());

    $component->set('answer', 'Hello World');

    $component->call('update');

    expect($this->question->fresh()->answer)->toBeNull();

    $component->assertStatus(403);
});

test('update unverified user', function (): void {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->question->to->update([
        'email_verified_at' => null,
    ]);

    $component->set('answer', 'Hello World');

    $component->call('update');

    $component->assertRedirect(route('verification.notice'));

    expect($this->question->fresh()->answer)->toBeNull();
});

test('report', function (): void {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->call('report');

    expect($this->question->fresh()->is_reported)->toBeTrue();
});

test('report auth', function (): void {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $this->actingAs(User::factory()->create());

    $component->call('report');

    expect($this->question->fresh()->is_reported)->toBeFalse();

    $component->assertStatus(403);
});

test('ignore', function (): void {
    $component = Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ]);

    $component->call('ignore');

    $component->assertDispatched('notification.created', message: 'Question ignored.');
    $component->assertDispatched('question.ignore', questionId: $this->question->id);
});

test('cannot update with blank characters', function (): void {
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

test('can edit a question that has an answer', function (): void {
    $this->question->update([
        'answer' => 'foo',
        'answer_created_at' => now(),
    ]);

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->set('answer', 'Hello World')
        ->call('update')
        ->assertDispatched('notification.created', message: 'Answer updated.')
        ->assertDispatched('close-modal', "question.edit.answer.{$this->question->id}")
        ->assertDispatched('question.updated');

    expect($this->question->fresh()->answer)->toBe('Hello World')
        ->and($this->question->fresh()->answer_updated_at)->not->toBeNull();

    Livewire::test(Show::class, [
        'questionId' => $this->question->id,
    ])
        ->assertSee('Hello World')
        ->assertSee('Edited');
});

test('edited questions display raw answers in the form', function (): void {
    $this->question->update([
        'answer' => "Hello @{$this->question->from->username} - How are you doing?",
        'answer_created_at' => now(),
    ]);

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->assertSeeHtml("Hello @{$this->question->from->username} - How are you doing?")
        ->assertSet('answer', "Hello @{$this->question->from->username} - How are you doing?");
});

test('likes are reset when an answer is updated', function (): void {
    $this->question->update([
        'answer' => 'foo',
        'answer_created_at' => now(),
    ]);

    $this->question->likes()->create([
        'user_id' => $this->question->to->id,
    ]);

    expect($this->question->likes()->count())->toBe(1);

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->set('answer', 'Hello World')
        ->call('update');

    expect($this->question->likes()->count())->toBe(0);
});

test('cannot edit an answer after 24 hours', function (): void {
    $this->question->update([
        'answer' => 'foo',
        'answer_created_at' => now()->subHours(25),
    ]);

    Livewire::test(Edit::class, [
        'questionId' => $this->question->id,
    ])
        ->set('answer', 'Hello World')
        ->call('update')
        ->assertDispatched('notification.created', message: 'Answer cannot be edited after 24 hours.');
});

test('cannot answer a question that has been reported or ignored', function (): void {
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

test('create channel validation in edit', function (): void {
    Livewire::test(Edit::class, ['questionId' => $this->question->id])
        ->call('createChannel', '')
        ->assertHasErrors(['newChannel'])
        ->call('createChannel', 'A')
        ->assertHasErrors(['newChannel'])
        ->call('createChannel', 'Invalid @ Name!')
        ->assertHasErrors(['newChannel'])
        ->call('createChannel', '---')
        ->assertHasErrors(['newChannel']);
});

test('create channel reuses existing in edit', function (): void {
    $existing = Channel::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    Livewire::test(Edit::class, ['questionId' => $this->question->id])
        ->call('createChannel', 'laravel')
        ->assertHasNoErrors()
        ->assertSet('channelId', $existing->id);
});

test('create channel is deferred in edit', function (): void {
    expect(Channel::where('slug', 'brand-new-channel')->count())->toBe(0);

    Livewire::test(Edit::class, ['questionId' => $this->question->id])
        ->call('createChannel', 'Brand New Channel')
        ->assertHasNoErrors()
        ->assertSet('channelName', 'Brand New Channel');

    expect(Channel::where('slug', 'brand-new-channel')->count())->toBe(0);
});

test('create channel unverified in edit', function (): void {
    $this->question->to->update(['email_verified_at' => null]);

    Livewire::test(Edit::class, ['questionId' => $this->question->id])
        ->call('createChannel', 'Valid Name')
        ->assertRedirect(route('verification.notice'));
});
