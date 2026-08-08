<?php

declare(strict_types=1);

use App\Livewire\Questions\Show;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('render', function (): void {
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

test('refresh', function (): void {
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

test('listeners', function (): void {
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

test('redirect to profile', function (): void {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->dispatch('question.reported');

    $component->assertRedirect(route('profile.show', ['username' => $question->to->username]));
});

test('ignore', function (): void {
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

test('ignore auth', function (): void {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('ignore');

    $component->assertRedirect(route('login'));
});

test('ignore unverified user', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('ignore');

    $component->assertRedirect(route('verification.notice'));

    expect($question->fresh()->is_ignored)->toBeFalse();
});

test('bookmark', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('bookmark');
    $component->assertDispatched('notification.created', message: 'Bookmark added.');

    $component->call('bookmark');

    expect($question->bookmarks()->count())->toBe(1);
});

test('bookmark auth', function (): void {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('bookmark');

    $component->assertRedirect(route('login'));
});

test('bookmark unverified user', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('bookmark');

    $component->assertRedirect(route('verification.notice'));

    expect($question->bookmarks()->count())->toBe(0);
});

test('unbookmark', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('bookmark');

    expect($question->bookmarks()->count())->toBe(1);

    $component->call('unbookmark');

    $component->assertDispatched('question.unbookmarked');
    $component->assertDispatched('notification.created', message: 'Bookmark removed.');
    expect($question->bookmarks()->count())->toBe(0);
});

test('unbookmark auth', function (): void {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unbookmark');

    $component->assertRedirect(route('login'));
});

test('unbookmark unverified user', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unbookmark');

    $component->assertRedirect(route('verification.notice'));

    expect($question->bookmarks()->count())->toBe(0);
});

test('like', function (): void {
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

test('like auth', function (): void {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('like');

    $component->assertRedirect(route('login'));
});

test('like unverified user', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('like');

    $component->assertRedirect(route('verification.notice'));

    expect($question->likes()->count())->toBe(0);
});

test('unlike', function (): void {
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

test('unlike auth', function (): void {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unlike');

    $component->assertRedirect(route('login'));
});

test('unlike unverified user', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unlike');

    $component->assertRedirect(route('verification.notice'));

    expect($question->likes()->count())->toBe(0);
});

test('pin', function (): void {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertSeeHtml('<span>Pin</span>');

    $component->call('pin');

    expect($question->refresh()->pinned)->toBeTrue();
});

test('pin auth', function (): void {
    $question = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSeeHtml('<span>Pin</span>');

    $component->call('pin');

    $component->assertRedirect(route('login'));
});

test('pin unverified user', function (): void {
    $question = Question::factory()->create();

    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('pin');

    $component->assertRedirect(route('verification.notice'));

    expect($question->refresh()->pinned)->toBeFalse();
});

test('pin no answer', function (): void {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSeeHtml('<span>Pin</span>');

    $component->call('pin');

    $component->assertForbidden();
});

test('unpin', function (): void {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertSeeHtml('<span>Unpin</span>');

    $component->call('unpin');

    expect($question->refresh()->pinned)->toBeFalse();
});

test('unpin auth', function (): void {
    $question = Question::factory()->create([
        'pinned' => true,
    ]);

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSeeHtml('<span>Unpin</span>');

    $component->call('unpin');

    $component->assertRedirect(route('login'));
});

test('unpin unverified user', function (): void {
    $question = Question::factory()->create([
        'pinned' => true,
    ]);

    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('unpin');

    $component->assertRedirect(route('verification.notice'));

    expect($question->refresh()->pinned)->toBeTrue();
});

test('unpin visitor', function (): void {
    $user = User::factory()->create();
    $visitor = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $component = Livewire::actingAs($visitor)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertDontSeeHtml('<span>Unpin</span>');

    $component->call('unpin');

    $component->assertForbidden();
});

test('display pinned label only on profile.show route', function (): void {
    $user = User::factory()->create();

    Question::factory()->create([
        'to_id' => $user->id,
        'pinned' => true,
    ]);

    $response = $this->actingAs($user)->get(route('profile.show', [
        'username' => $user->username,
    ]));

    $response->assertSee('Pinned');

    $response = $this->actingAs($user)->get(route('home.feed'));

    $response->assertDontSee('Pinned');
});

test('pinnable', function (): void {
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

test('it has a likes component', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $user->id,
        'answer' => 'Sample answer',
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->assertSeeLivewire('likes');
});
