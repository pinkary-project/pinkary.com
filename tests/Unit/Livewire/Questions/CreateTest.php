<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;

beforeEach(function (): void {
    Storage::fake();
});

test('render', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->assertOk()->assertSee('Ask a question...');
});

test('refreshes when link settings changes', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->assertSeeHtml('text-blue-500');

    $user->update([
        'settings' => [
            'link_shape' => 'rounded-lg',
            'gradient' => 'from-red-500 to-purple-600',
        ],
    ]);

    $component->dispatch('link-settings.updated');

    $component->assertSeeHtml('text-red-500');
});

test('store', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', true);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');
    $component->assertDispatched('close-modal', 'post-create');

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue()
        ->and($question->parent_id)->toBeNull()
        ->and($question->root_id)->toBeNull();
});

test('accepts custom draft key for isolated drafting', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
        'customDraftKey' => 'post_modal',
    ]);

    $component->assertSet('customDraftKey', 'post_modal');
});

test('users with zero followers pass captcha and can store', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    Turnstile::fake();

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Ensure userA has zero followers.
    expect($userA->followers()->count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello from zero followers');
    $component->set('cfTurnstileResponse', Turnstile::dummy());

    $component->call('store');

    $component->assertSet('content', '');
    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello from zero followers');
});

test('renders the captcha on replies, whose parent id contains characters turnstile rejects', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    Turnstile::fake();

    $user = User::factory()->create();
    $parent = Question::factory()->create();

    expect($parent->id)->toContain('-');

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'parentId' => $parent->id,
    ]);

    $component->assertOk()
        ->assertSeeHtml('data-callback="reply_'.str_replace('-', '_', $parent->id).'_turnstile_globalCallback"');
});

test('store auth', function (): void {
    $user = User::factory()->create();

    expect(Question::count())->toBe(0);

    $component = Livewire::test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');

    $component->assertRedirect('login');

    expect(Question::count())->toBe(0);
});

test('store rate limit', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 3 questions per minute.',
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 3 questions per minute.',
    ]);
});

test('store comment', function (): void {
    $userA = User::factory()->create();

    $question = Question::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userA->id,
        'parentId' => $question->id,
    ]);

    $this->travel(1)->seconds(); // To avoid time conflicts
    $component->set('content', 'My comment');

    $component->call('store');
    $component->assertSet('content', '');

    $component->assertDispatched('notification.created', message: 'Comment sent.');
    $component->assertDispatched('question.created');

    $comment = Question::latest()->limit(1)->first();

    expect($comment->from_id)->toBe($userA->id)
        ->and($comment->to_id)->toBe($userA->id)
        ->and($comment->answer)->toBe('My comment')
        ->and($comment->parent_id)->toBe($question->id)
        ->and($comment->root_id)->toBe($question->id);
});

test('store comment on a comment', function (): void {
    $userA = User::factory()->create();

    $question = Question::factory()->create();

    $questionWithComment = Question::factory()->create([
        'to_id' => $userA->id,
        'parent_id' => $question->id,
        'root_id' => $question->id,
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userA->id,
        'parentId' => $questionWithComment->id,
    ]);

    $this->travel(1)->seconds(); // To avoid time conflicts

    $component->set('content', 'My comment');

    $component->call('store');
    $component->assertSet('content', '');

    $component->assertDispatched('notification.created', message: 'Comment sent.');
    $component->assertDispatched('question.created');

    $comment = Question::latest()->limit(1)->first();

    expect($comment->from_id)->toBe($userA->id)
        ->and($comment->to_id)->toBe($userA->id)
        ->and($comment->answer)->toBe('My comment')
        ->and($comment->parent_id)->toBe($questionWithComment->id)
        ->and($comment->root_id)->toBe($questionWithComment->root_id)
        ->and($comment->root_id)->toBe($question->id);
});

test('stores a thread with multiple posts', function (): void {
    $user = User::factory()->create();

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'First post');
    $component->set('threadPosts', ['Second post', 'Third post']);

    $component->call('store');

    expect(Question::count())->toBe(3);

    /** @var Question $root */
    $root = Question::query()->where('answer', 'First post')->firstOrFail();

    /** @var Question $second */
    $second = Question::query()->where('answer', 'Second post')->firstOrFail();

    /** @var Question $third */
    $third = Question::query()->where('answer', 'Third post')->firstOrFail();

    $this->assertDatabaseHas('questions', ['id' => $root->id, 'content' => '__UPDATE__']);

    expect($root->parent_id)->toBeNull()
        ->and($root->root_id)->toBeNull()
        ->and($second->from_id)->toBe($user->id)
        ->and($second->to_id)->toBe($user->id)
        ->and($second->parent_id)->toBe($root->id)
        ->and($second->root_id)->toBe($root->id)
        ->and($third->parent_id)->toBe($second->id)
        ->and($third->root_id)->toBe($root->id);

    $component->assertSet('threadPosts', []);
    $component->assertDispatched('notification.created', message: 'Thread sent.');
    $component->assertDispatched('question.created');
    $component->assertDispatched('close-modal', 'post-create');
});

test('drops empty thread posts when storing', function (): void {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Main update');
    $component->set('threadPosts', ['', '   ', 'Second real post']);

    $component->call('store');

    expect(Question::count())->toBe(2)
        ->and(Question::query()->where('answer', 'Second real post')->exists())->toBeTrue();
});

test('accepts single character thread posts', function (): void {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Main update');
    $component->set('threadPosts', ['x']);

    $component->call('store');

    $component->assertHasNoErrors();

    expect(Question::count())->toBe(2)
        ->and(Question::query()->where('answer', 'x')->exists())->toBeTrue();
});

test('rejects more than nine extra thread posts', function (): void {
    $user = User::factory()->create();

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Main update');
    $component->set('threadPosts', array_fill(0, 10, 'Another post'));

    $component->call('store');

    $component->assertHasErrors([
        'threadPosts' => 'A thread can have a maximum of '.(Create::MAX_THREAD_POSTS - 1).' extra posts.',
    ]);

    expect(Question::count())->toBe(0);
});

test('ignores extra thread posts when asking another user', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'What do you think?');
    $component->set('threadPosts', ['Sneaky extra']);

    $component->call('store');

    expect(Question::count())->toBe(1);

    $component->assertDispatched('notification.created', message: 'Question sent.');
});

test('ignores extra thread posts when commenting on a question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
        'parentId' => $question->id,
    ]);

    $this->travel(1)->seconds();

    $component->set('content', 'My comment');
    $component->set('threadPosts', ['Ignored extra']);

    $component->call('store');

    expect(Question::count())->toBe(2);

    $component->assertDispatched('notification.created', message: 'Comment sent.');
});

test('a whole thread counts against the per minute limit', function (): void {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    foreach ([1, 2] as $i) {
        $component->set('content', "Update {$i}");
        $component->set('threadPosts', ["Extra {$i}"]);

        $component->call('store');

        $component->assertHasNoErrors();
    }

    expect(Question::count())->toBe(4);

    $component->set('content', 'One thread too many');
    $component->set('threadPosts', ['Extra three']);

    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 3 questions per minute.',
    ]);

    expect(Question::count())->toBe(4);
});

test('each thread post counts towards the daily limit', function (): void {
    $user = User::factory()->create();

    Question::factory()
        ->count(29)
        ->sequence(fn (Illuminate\Database\Eloquent\Factories\Sequence $sequence): array => [
            'from_id' => $user->id,
            'created_at' => now()->subMinutes($sequence->index + 2),
        ])
        ->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Main update');
    $component->set('threadPosts', ['Extra one here', 'Extra two here']);

    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 30 questions per day.',
    ]);

    expect(Question::count())->toBe(29);
});

test('a thread fitting the remaining daily quota is allowed', function (): void {
    $user = User::factory()->create();

    Question::factory()
        ->count(27)
        ->sequence(fn (Illuminate\Database\Eloquent\Factories\Sequence $sequence): array => [
            'from_id' => $user->id,
            'created_at' => now()->subMinutes($sequence->index + 2),
        ])
        ->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Main update');
    $component->set('threadPosts', ['Extra one here', 'Extra two here']);

    $component->call('store');

    $component->assertHasNoErrors();

    expect(Question::count())->toBe(30);
});

test('thread posts are dropped when sharing a poll', function (): void {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'What is your favorite color?');
    $component->set('isPoll', true);
    $component->set('pollOptions', ['Red', 'Blue']);
    $component->set('threadPosts', ['Should be ignored']);

    $component->call('store');

    expect(Question::count())->toBe(1)
        ->and(Question::first()->pollOptions)->toHaveCount(2);

    $component->assertDispatched('notification.created', message: 'Update sent.');
});

test('continues an inline thread inside the post modal', function (): void {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
        'customDraftKey' => 'post_modal',
    ]);

    $component->dispatch('thread.continue-in-modal', content: 'Inline thoughts', threadPosts: ['More here', '   ']);

    $component->assertSet('content', 'Inline thoughts')
        ->assertSet('threadPosts', ['More here']);
});

test('inline composers ignore the continue-in-modal event', function (): void {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Existing draft');
    $component->dispatch('thread.continue-in-modal', content: 'Incoming', threadPosts: ['Incoming extra']);

    $component->assertSet('content', 'Existing draft')
        ->assertSet('threadPosts', []);
});

test('max 30 questions per day', function (): void {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    for ($i = 0; $i <= 29; $i++) {
        $component->set('content', 'Hello World');
        $component->call('store');
        $this->travelTo(now()->addMinutes($i));
        $component->assertHasNoErrors();
    }

    expect(Question::count())->toBe(30);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 30 questions per day.',
    ]);

    expect(Question::count())->toBe(30);
});

test('cannot store with blank characters', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', "\u{200E}");
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'The content field cannot contain blank characters.',
    ]);
});

test('shows validation error when content is missing', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', '');
    $component->call('store');

    $component->assertHasErrors(['content' => 'required']);

    expect(Question::count())->toBe(0);
});

test('poll should have at least 2 options', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('isPoll', true);
    $component->set('pollOptions', ['Option 1']);
    $component->set('content', 'What is your favorite color?');

    $component->call('store');

    $component->assertHasErrors([
        'pollOptions' => 'A poll must have at least 2 options.',
    ]);
});

test('poll should have at most 4 options', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('isPoll', true);
    $component->set('pollOptions', ['Option 1', 'Option 2', 'Option 3', 'Option 4', 'Option 5']);
    $component->set('content', 'What is your favorite color?');

    $component->call('store');

    $component->assertHasErrors([
        'pollOptions' => 'A poll can have maximum 4 options.',
    ]);
});

test('poll button is visible only for shared updates', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id]);

    $component->assertSee('Create a poll');

    $otherUser = User::factory()->create();
    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $otherUser->id]);

    $component->assertDontSee('Create a poll');
});

test('poll button is not visible for replies', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id, 'parentId' => $question->id]);

    $component->assertDontSee('Create a poll');
});

test('can create a poll with valid options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue', 'Green'])
        ->set('pollDuration', 3)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNotNull('poll_expires_at')
        ->first();

    expect($question)->not->toBeNull()
        ->and($question->pollOptions)->toHaveCount(3)
        ->and($question->pollOptions->pluck('text')->toArray())->toBe(['Red', 'Blue', 'Green'])
        ->and($question->poll_expires_at)->not->toBeNull()
        ->and((int) $question->created_at->diffInDays($question->poll_expires_at, false))->toBe(3);
});

test('validates poll requires at least 2 options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', ''])
        ->set('pollDuration', 3)
        ->call('store')
        ->assertHasErrors('pollOptions');
});

test('validates poll cannot have more than 4 options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue', 'Green', 'Yellow', 'Purple'])
        ->set('pollDuration', 3)
        ->call('store')
        ->assertHasErrors(['pollOptions' => 'A poll can have maximum 4 options.']);
});

test('validates poll options are required when poll is enabled', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['', ''])
        ->call('store')
        ->assertHasErrors('pollOptions');
});

test('validates poll option length', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', str_repeat('a', 41)])
        ->call('store')
        ->assertHasErrors('pollOptions');
});

test('creates regular question when poll is disabled', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'This is a regular update')
        ->set('isPoll', false)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNull('poll_expires_at')
        ->first();

    expect($question)->not->toBeNull()
        ->and($question->pollOptions)->toBeEmpty();
});

test('resets poll state after successful submission', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->call('store')
        ->assertSet('isPoll', false)
        ->assertSet('pollOptions', ['', '']);
});

test('trims whitespace from poll options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['  Red  ', '  Blue  '])
        ->set('pollDuration', 1)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNotNull('poll_expires_at')
        ->first();

    expect($question->pollOptions->pluck('text')->toArray())->toBe(['Red', 'Blue']);
});

test('validates poll duration is required when creating a poll', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->set('pollDuration', 0)
        ->call('store')
        ->assertHasErrors(['pollDuration']);
});

test('validates poll duration maximum value', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->set('pollDuration', 8)
        ->call('store')
        ->assertHasErrors(['pollDuration']);
});

test('stores poll expiration date correctly', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->set('pollDuration', 5)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNotNull('poll_expires_at')
        ->first();

    expect($question->poll_expires_at)->not->toBeNull()
        ->and((int) $question->created_at->diffInDays($question->poll_expires_at, false))->toBe(5);
});

test('does not set poll expiration for non-poll questions', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'This is a regular update')
        ->set('isPoll', false)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNull('poll_expires_at')
        ->first();

    expect($question->poll_expires_at)->toBeNull();
});

test('store with user questions_preference set to public', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => false]);

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', false);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeFalse();
});

test('store with user questions_preference set to anonymously', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => true]);

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', true);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue();
});

test('anonymous set back to user\'s preference after sending a question', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => false]);

    expect(Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');
    $component->toggle('anonymously');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', false);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue();
});

test('show "Share an update..." if user is viewing his own profile', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->assertSee('Share an update...');

    $user2 = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user2->id,
    ]);

    $component->assertSee('Ask a question...');
});

test('user don\'t see the anonymous checkbox if the user is viewing his own profile', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->assertDontSeeHtml('for="anonymously"');
});

test('user cannot share update anonymously', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');
    $component->set('anonymously', true);

    $component->call('store');

    $this->assertDatabaseHas('questions', [
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer' => 'Hello World',
        'content' => '__UPDATE__', // This is the content for an update
        'anonymously' => false,
    ]);
});

it('has a property for storing the images', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, [
            'toId' => $user->id,
        ]);

    expect($component->images)->toBeArray();
});

test('updated lifecycle method', function (): void {
    $user = User::factory()->create(['is_verified' => true]);

    $component = Livewire::actingAs($user)
        ->test(Create::class, [
            'toId' => $user->id,
        ]);
    expect($component->invade()->updated('images'))->toBeNull();
});

test('updated method invokes handleUploads', function (): void {
    $user = User::factory()->create(['is_verified' => true]);
    $file = UploadedFile::fake()->image('photo1.jpg');
    $date = now()->format('Y-m-d');
    $path = $file->store("images/{$date}", ['disk' => Create::IMAGE_DISK]);

    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('images', [$file]);

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->invoke($component->instance());

    $sessionKey = 'images.'.$component->instance()->draftKey();

    expect(session($sessionKey))->toBeArray()
        ->and(session($sessionKey))->toContain($path);

    $component->assertDispatched('image.uploaded');

    $component->assertSet('images', []);
});

test('unused image cleanup when store is called', function (): void {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo1.jpg');
    $date = now()->format('Y-m-d');
    $path = $file->store("images/{$date}", ['disk' => Create::IMAGE_DISK]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);
    $component->set('images', [$file]);

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->invoke($component->instance());

    Storage::disk()->assertExists($path);

    $sessionKey = 'images.'.$component->instance()->draftKey();

    expect(session($sessionKey))->toBeArray()
        ->and(session($sessionKey))->toContain($path);

    $component->set('content', 'Hello World');
    $component->call('store');

    Storage::disk()->assertMissing($path);
    expect(session($sessionKey))->toBeNull();
});

test('used images are NOT cleanup when store is called', function (): void {
    $user = User::factory()->create(['is_verified' => true]);
    $file = UploadedFile::fake()->image('photo1.jpg');
    $name = $file->hashName();
    $date = now()->format('Y-m-d');
    $path = 'images/'.$date.'/'.$name;

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);
    $component->set('images', [$file]);

    Storage::disk()->assertExists($path);

    $sessionKey = 'images.'.$component->instance()->draftKey();

    expect(session($sessionKey))->toBeArray()
        ->and(session($sessionKey))->toContain($path);

    $url = Storage::disk()->url($path);

    $component->set('content', "![Image Alt Text]({$url})");
    $component->call('store');

    Storage::disk()->assertExists($path);
    expect(session($sessionKey))->toBeNull();
});

test('posting one form does not delete another draft\'s tracked images', function (): void {
    $user = User::factory()->create(['is_verified' => true]);
    $parentQuestion = Question::factory()->create();

    /** @var Testable $componentA */
    $componentA = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
        'parentId' => $parentQuestion->id,
    ]);

    /** @var Testable $componentB */
    $componentB = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
        'customDraftKey' => 'post_modal',
    ]);

    $method = new ReflectionMethod(Create::class, 'uploadImages');

    $componentA->set('images', [UploadedFile::fake()->image('photo-a.jpg')]);
    $method->invoke($componentA->instance());

    $componentB->set('images', [UploadedFile::fake()->image('photo-b.jpg')]);
    $method->invoke($componentB->instance());

    $sessionKeyA = 'images.reply_'.$parentQuestion->id;
    $sessionKeyB = 'images.'.$componentB->instance()->draftKey();

    expect(session($sessionKeyA))->toBeArray()->not->toBeEmpty()
        ->and(session($sessionKeyB))->toBeArray()->not->toBeEmpty();

    $pathA = session($sessionKeyA)[0];
    $pathB = session($sessionKeyB)[0];

    Storage::disk()->assertExists($pathA);
    Storage::disk()->assertExists($pathB);

    // Posting from draft A deletes its own unused image and clears only its own session key.
    $componentA->set('content', 'Hello World');
    $componentA->call('store');

    Storage::disk()->assertMissing($pathA);
    expect(session($sessionKeyA))->toBeNull()
        // Draft B keeps its tracked image and its session entry intact.
        ->and(session($sessionKeyB))->toContain($pathB);

    Storage::disk()->assertExists($pathB);
});

test('delete image', function (): void {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo1.jpg');
    $path = $file->store('images', ['disk' => Create::IMAGE_DISK]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    Storage::disk()->assertExists($path);

    $method = new ReflectionMethod(Create::class, 'deleteImage');
    $method->invoke($component->instance(), $path);

    $pathAgain = $file->store('images', ['disk' => Create::IMAGE_DISK]);
    Storage::disk()->assertExists($pathAgain);

    $method->invoke($component->instance(), $pathAgain);

    Storage::disk()->assertMissing($pathAgain);
});

test('optimizeImage method resizes and saves the image', function (): void {

    $user = User::factory()->create();
    $testImage = UploadedFile::fake()->image('test.jpg', 1200, 1200); // Larger than 1000x1000

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $method = new ReflectionMethod(Create::class, 'optimizeImage');
    $path = $method->invoke($component->instance(), $testImage);

    Storage::disk()->assertExists($path);

    $optimizedImagePath = Storage::disk()->path($path);

    $originalImageSize = filesize($testImage->getPathname());
    $optimizedImageSize = filesize($optimizedImagePath);

    expect($optimizedImageSize)->toBeLessThan($originalImageSize);

    $manager = ImageManager::imagick();
    $image = $manager->read($optimizedImagePath);

    expect($image->width())->toBeLessThanOrEqual(1000)
        ->and($image->height())->toBeLessThanOrEqual(1000);
});

test('it skips the optimization for gif', function (): void {

    $user = User::factory()->create();

    $testImage = UploadedFile::fake()->image('test.gif', 1200, 1200); // Larger than 1000x1000

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $method = new ReflectionMethod(Create::class, 'optimizeImage');
    $path = $method->invoke($component->instance(), $testImage);

    Storage::disk()->assertExists($path);

    // cross check the image
    $optimizedImagePath = Storage::disk()->path($path);
    $originalImageSize = filesize($testImage->getPathname());
    $optimizedImageSize = filesize($optimizedImagePath);
    expect($optimizedImageSize)->toBe($originalImageSize);

    $manager = ImageManager::imagick();
    $image = $manager->read($optimizedImagePath);
    expect($image->width())->toBe(1200)
        ->and($image->height())->toBe(1200);
});

test('maxFileSize and maxImages', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    expect($component->maxFileSize)->toBe(1024 * 8)
        ->and($component->uploadLimit)->toBe(3);
});

test('non verified users can upload images', function (): void {
    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->invoke($component->instance());

    $component->assertHasNoErrors();
});

test('company verified users can upload images', function (): void {
    $user = User::factory()->create([
        'is_company_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->invoke($component->instance());

    $component->assertHasNoErrors();
});

test('upload must be an image', function (): void {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->create('test.pdf')]);
    $component->call('runImageValidation');

    $component->assertHasErrors([
        'images.0' => 'The file must be an image.',
    ]);
});

test('upload must be correct type of image', function (): void {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.png')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.gif')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.webp')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.jpeg')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.bmp')]);
    $component->call('runImageValidation');

    expect($component->errors()->get('images.0'))->toBeArray()
        ->and($component->errors()->get('images.0'))->toContain('The image must be a file of type: jpeg, png, gif, webp, jpg.');
});

test('max file size error', function (): void {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $maxFileSize = 1024 * 8;

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $largeFile = UploadedFile::fake()->image('test.jpg')->size(1024 * 9);

    $component->set('images', [$largeFile]);
    $component->call('runImageValidation');

    expect($component->get('images'))
        ->toBeArray()
        ->and($component->get('images'))
        ->not()->toContain($largeFile);

    $component->assertHasErrors([
        'images.0' => "The image may not be greater than {$maxFileSize} kilobytes.",
    ]);
});

test('max size & ratio validation', function (): void {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [
        UploadedFile::fake()->image('test.jpg', 4005, 4005),
    ]);
    $component->call('runImageValidation');

    $component->assertHasErrors([
        'images.0' => 'The image must be less than 4000 x 4000 pixels.',
    ]);

    // after livewire v4 settings image again merge the array
    // instead of replacing the array with old array therefore
    // we need to set it with empty array to reset the images
    $component->set('images', []);

    $component->set('images', [
        UploadedFile::fake()->image('test.jpg', 429, 1100),
    ]);
    $component->call('runImageValidation');

    $component->assertHasErrors([
        'images.0' => 'The image aspect ratio must be less than 2/5.',
    ]);
});

test('only verified users can upload images', function (): void {
    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);
    $component->call('runImageValidation');

    $component->assertRedirect(route('verification.notice'));
});

test('only verified users can create questions', function (): void {
    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertRedirect(route('verification.notice'));
});

test('delete image after validation ignores untracked or missing images', function (): void {
    $user = User::factory()->create();
    $untrackedPath = UploadedFile::fake()->image('untracked.jpg')->store('images', ['disk' => Create::IMAGE_DISK]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    Storage::disk()->assertExists($untrackedPath);

    $method = new ReflectionMethod(Create::class, 'deleteImageAfterValidation');

    // An untracked path is never deleted.
    $method->invoke($component->instance(), $untrackedPath);

    Storage::disk()->assertExists($untrackedPath);

    // A tracked but missing path is silently ignored.
    $sessionKey = 'images.'.$component->instance()->draftKey();
    session([$sessionKey => ['images/missing.png']]);

    $method->invoke($component->instance(), 'images/missing.png');

    expect(session($sessionKey))->toContain('images/missing.png');
});

test('delete image after validation removes tracked images and guards other folders', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $sessionKey = 'images.'.$component->instance()->draftKey();

    $trackedPath = UploadedFile::fake()->image('tracked.png')->store('images', ['disk' => Create::IMAGE_DISK]);
    session([$sessionKey => [$trackedPath]]);

    new ReflectionMethod(Create::class, 'deleteImageAfterValidation')
        ->invoke($component->instance(), $trackedPath);

    Storage::disk()->assertMissing($trackedPath);

    // Files outside the images folder are kept even when tracked.
    $outsidePath = UploadedFile::fake()->image('outside.png')->store('uploads', ['disk' => Create::IMAGE_DISK]);
    session([$sessionKey => [$outsidePath]]);

    new ReflectionMethod(Create::class, 'deleteImage')
        ->invoke($component->instance(), $outsidePath);

    Storage::disk()->assertExists($outsidePath);
});
