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
        'question.ignore' => 'ignore',
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

    $component->assertRedirect(route('profile.show', ['username' => $question->to->username]));
});

test('ignore', function () {
    $question = Question::factory()->create();

    $user = User::find($question->to_id);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
        'inIndex' => false,
    ]);

    $component->call('ignore');

    expect($question->fresh()->is_ignored)->toBeTrue();

    $component->assertRedirect(route('profile.show', ['username' => $question->to->username]));

    $question = Question::factory()->create();
    $user = User::find($question->to_id);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
        'inIndex' => true,
    ]);

    $component->call('ignore');
    $component->assertDispatched('notification.created', message: 'Question ignored.');
    $component->assertDispatched('question.ignore');
});

test('ignore auth', function () {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('ignore');

    $component->assertRedirect(route('login'));
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

    $question = Question::factory()->create([
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertSee('Pin');

    $component->call('pin');

    expect($question->refresh()->pinned)->toBe(true);
});

test('pin auth', function () {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSee('Pin');

    $component->call('pin');

    $component->assertRedirect(route('login'));
});

test('pin no answer', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'answer' => null,
        'answered_at' => null,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSee('Pin');

    $component->call('pin');

    $component->assertForbidden();
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

    $component->assertSee('Unpin');

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

    $component->assertDontSee('Unpin');

    $component->call('unpin');

    $component->assertRedirect(route('login'));
});

test('unpin visitor', function () {
    $user = User::factory()->create();
    $visitor = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $component = Livewire::actingAs($visitor)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSee('Unpin');

    $component->call('unpin');

    $component->assertForbidden();
});

test('display pinned label only on profile.show route', function () {
    $user = User::factory()->create();

    Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $response = $this->actingAs($user)->get(route('profile.show', [
        'username' => $user->username,
    ]));

    $response->assertSee('Pinned');

    $response = $this->actingAs($user)->get(route('feed'));

    $response->assertDontSee('Pinned');
});

test('pinnable', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'pinnable' => false,
        'questionId' => $question->id,
    ]);

    $component->assertDontSee('Pinned');

    $component = Livewire::actingAs($user)->test(Show::class, [
        'pinnable' => true,
        'questionId' => $question->id,
    ]);

    $component->assertSee('Pinned');
});
