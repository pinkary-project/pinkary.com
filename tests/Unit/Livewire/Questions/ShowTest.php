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

    // Listeners are registered unconditionally, even within the index view.
    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
        'inIndex' => true,
    ]);

    $component->dispatch('question.reported', questionId: $question->id);

    $component->assertRedirect(route('profile.show', ['username' => $question->to->username]));

    $component = Livewire::actingAs($question->to)->test(Show::class, [
        'questionId' => $question->id,
        'inIndex' => true,
    ]);

    $component->dispatch('question.ignore', questionId: $question->id);

    expect($question->fresh()->is_ignored)->toBeTrue();
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

    // Embedded card (feed, listing, thread): stays in place.
    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->call('ignore');

    expect($question->fresh()->is_ignored)->toBeTrue();

    $component->assertDispatched('notification.created', message: 'Question ignored.')
        ->assertDispatched('question.ignored', questionId: $question->id)
        ->assertNoRedirect();

    // Main card on the question's own show page: stays in place as well.
    $ownQuestion = Question::factory()->create([
        'to_id' => $user->id,
        'from_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $ownQuestion->id,
        'commenting' => true,
    ]);

    $component->call('ignore');

    expect($ownQuestion->fresh()->is_ignored)->toBeTrue()
        ->and($component)->assertNoRedirect();
});

test('ignore event only affects the dispatched question', function (): void {
    $ownQuestion = Question::factory()->create();

    $otherQuestion = Question::factory()->create([
        'to_id' => $ownQuestion->to_id,
    ]);

    $component = Livewire::actingAs(User::find($ownQuestion->to_id))->test(Show::class, [
        'questionId' => $ownQuestion->id,
    ]);

    // A broadcast targeting another question must not ignore this component's question.
    $component->dispatch('question.ignore', questionId: $otherQuestion->id);

    expect($ownQuestion->fresh()->is_ignored)->toBeFalse()
        ->and($otherQuestion->fresh()->is_ignored)->toBeFalse();

    // A broadcast targeting its own question is handled.
    $component->dispatch('question.ignore', questionId: $ownQuestion->id);

    expect($ownQuestion->fresh()->is_ignored)->toBeTrue();

    // Directly calling ignore still ignores its own question.
    $component->call('ignore');

    expect($ownQuestion->fresh()->is_ignored)->toBeTrue();
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

test('ignoring a reply dispatches an event for every removed question', function (): void {
    $user = User::factory()->create();

    $root = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => 'SCENARIO root',
        'answer' => 'root answer',
        'answer_created_at' => now(),
    ]);

    $child = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'parent_id' => $root->id,
        'root_id' => $root->id,
        'content' => 'SCENARIO child',
        'answer' => 'child answer',
        'answer_created_at' => now(),
    ]);

    $grandChild = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'parent_id' => $child->id,
        'root_id' => $root->id,
        'content' => 'SCENARIO grandchild',
        'answer' => 'grandchild answer',
        'answer_created_at' => now(),
    ]);

    $sibling = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'parent_id' => $root->id,
        'root_id' => $root->id,
        'content' => 'SCENARIO sibling',
        'answer' => 'sibling answer',
        'answer_created_at' => now(),
    ]);

    $component = Livewire::actingAs($user)->test(Show::class, [
        'questionId' => $child->id,
    ]);

    $component->call('ignore');

    foreach ([$child->id, $grandChild->id] as $id) {
        $component->assertDispatched('question.ignored', questionId: $id);
    }

    $component->assertNotDispatched('question.ignored', questionId: $root->id)
        ->assertNotDispatched('question.ignored', questionId: $sibling->id);

    expect(Question::find($grandChild->id))->toBeNull()
        ->and(Question::find($child->id))->not->toBeNull()
        ->and($child->refresh()->is_ignored)->toBeTrue()
        ->and(Question::find($root->id))->not->toBeNull()
        ->and($root->refresh()->is_ignored)->toBeFalse()
        ->and(Question::find($sibling->id))->not->toBeNull()
        ->and($sibling->refresh()->is_ignored)->toBeFalse();
});

test('reported event for another question does not redirect', function (): void {
    $question = Question::factory()->create();
    $other = Question::factory()->create();

    $component = Livewire::test(Show::class, [
        'questionId' => $question->id,
    ]);

    $component->dispatch('question.reported', questionId: $other->id);

    $component->assertNoRedirect();
});
