<?php

declare(strict_types=1);

use App\Livewire\Questions\Show;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('render', function () {
    $question = Question::factory()->create([
        'content' => 'Hello World',
        'answer' => 'Hello World Answer',
    ]);

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertSee([
        $question->content,
        $question->answer,
    ]);
});

test('refresh', function () {
    $question = Question::factory()->create([
        'content' => 'Hello World',
    ]);

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $question->update([
        'answer' => 'Hello World Answer Updated',
    ]);

    $component->assertDontSee('Hello World Answer Updated');

    $component->dispatch('question.updated');

    $component->assertSee('Hello World Answer Updated');
});

test('listeners', function () {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    expect($component->instance()->getListeners())->toBe([
        'question.destroy' => 'destroy',
        'question.reported' => 'redirectToProfile',
    ]);

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
        'inIndex' => true,
    ]);

    expect($component->instance()->getListeners())->toBe([]);
});

test('redirect to profile', function () {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->dispatch('question.reported');

    $component->assertRedirect(route('profile.show', ['user' => $question->to->username]));
});

test('destroy', function () {
    $question = Question::factory()->create();

    $user = User::find($question->to_id);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->dispatch('question.destroy', $question->id);

    expect($question->fresh())->toBeNull();

    $component->assertRedirect(route('profile.show', ['user' => $question->to->username]));
});

test('destroy auth', function () {
    $question = Question::factory()->create();

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->dispatch('question.destroy', $question->id);

    $component->assertStatus(403);
});

test('like', function () {
    $question = Question::factory()->create();

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('like');
    $component->call('like');
    $component->call('like');

    expect($question->likes()->count())->toBe(1);
});

test('like auth', function () {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('like');

    $component->assertRedirect(route('login'));
});

test('unlike', function () {
    $question = Question::factory()->create();

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('like');

    expect($question->likes()->count())->toBe(1);

    $component->call('unlike');

    expect($question->likes()->count())->toBe(0);
});

test('unlike auth', function () {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unlike');

    $component->assertRedirect(route('login'));
});

test('pin', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create(['to_id' => $user->id]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('pin');

    expect($question->refresh()->pinned)->toBe(true);
});

test('pin auth', function () {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('pin');

    $component->assertRedirect(route('login'));
});

test('unpin', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unpin');

    expect($question->refresh()->pinned)->toBe(false);
});

test('unpin auth', function () {
    $question = Question::factory()->create([
        'pinned' => true,
    ]);

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unpin');

    $component->assertRedirect(route('login'));
});

test('only show pin/unpin buttons to the user who received the question', function () {
    $user = User::factory()->create();
    $visitor = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $component = Livewire::actingAs($visitor)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSee('Pin');
    $component->assertDontSee('Unpin');
});
