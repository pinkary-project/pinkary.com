<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

test('a guest cannot publish a thread', function (): void {
    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Hello, Pinkary.',
    ])->assertUnauthorized();
});

test('an authenticated user can publish a shared update', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Hello, Pinkary.',
    ], [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.answer', 'Hello, Pinkary.');

    $question = Question::sole();

    expect($question->from_id)->toBe($user->id)
        ->and($question->to_id)->toBe($user->id)
        ->and($question->content)->toBe('__UPDATE__')
        ->and($question->answer)->toBe('Hello, Pinkary.')
        ->and($question->answer_created_at)->not->toBeNull();
});

test('thread follow-ups are chained to the main post', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.', 'Third post.'],
    ], [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated()
        ->assertJsonCount(3, 'data');

    $this->assertDatabaseCount('questions', 3);

    $questions = Question::orderBy('created_at')->get();

    expect($questions[1]->parent_id)->toBe($questions[0]->id)
        ->and($questions[1]->root_id)->toBe($questions[0]->id)
        ->and($questions[2]->parent_id)->toBe($questions[1]->id)
        ->and($questions[2]->root_id)->toBe($questions[0]->id)
        ->and($questions[2]->answer)->toBe('Third post.');
});

test('blank thread follow-ups are ignored', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['   ', null, 'Second post.'],
    ], [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated()
        ->assertJsonCount(2, 'data');

    $this->assertDatabaseCount('questions', 2);
});

test('publishing a thread validates its input', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $headers = ['Authorization' => 'Bearer '.$token];

    $this->postJson(route('api.v1.questions.store'), [], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');

    $this->postJson(route('api.v1.questions.store'), [
        'content' => str_repeat('a', 1001),
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => array_fill(0, 10, 'Extra post.'),
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('thread_posts');
});

test('a guest cannot comment on a question', function (): void {
    $question = Question::factory()->create();

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Nice post.',
    ])->assertUnauthorized();
});

test('commenting on a question you may not view is forbidden', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['answer' => null, 'answer_created_at' => null]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Nice post.',
    ], $headers)->assertForbidden();
});

test('an authenticated user can comment on a question', function (): void {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
        'content' => '__UPDATE__',
        'answer' => 'Hello, Pinkary.',
        'answer_created_at' => now(),
    ]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Great update!',
    ], $headers)
        ->assertCreated()
        // Stored as a shared update, like the web: the text is the answer
        // and the content column holds the __UPDATE__ sentinel.
        ->assertJsonPath('data.answer', 'Great update!')
        ->assertJsonPath('data.content', null)
        ->assertJsonPath('data.from.id', $user->id)
        ->assertJsonPath('data.to.id', $user->id);

    $comment = Question::query()->where('parent_id', $question->id)->sole();

    expect($comment)->not->toBeNull()
        ->and($comment->from_id)->toBe($user->id)
        ->and($comment->to_id)->toBe($user->id)
        ->and($comment->parent_id)->toBe($question->id)
        ->and($comment->root_id)->toBe($question->id);
});

test('commenting validates its input', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.comments.store', $question), [], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => str_repeat('a', 1001),
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');
});

test('replies nest under the thread root', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $commentId = $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'First comment.',
    ], $headers)->assertCreated()->json('data.id');

    $reply = Question::query()->whereKey($commentId)->sole();

    $this->postJson(route('api.v1.questions.comments.store', $reply), [
        'content' => 'A reply.',
    ], $headers)->assertCreated();

    $nested = Question::query()->where('answer', 'A reply.')->sole();

    expect($nested->parent_id)->toBe($reply->id)
        ->and($nested->root_id)->toBe($question->id);
});

test('a shared update never leaks the update sentinel over the API', function (): void {
    $user = User::factory()->create();

    $id = $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Hello, Pinkary.',
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])
        ->assertCreated()
        ->json('data.0.id');

    $expected = [
        'is_update' => true,
        'content' => null,
        'answer' => 'Hello, Pinkary.',
    ];

    $this->getJson(route('api.v1.questions.show', $id))
        ->assertOk()
        ->assertJsonPath('data.is_update', $expected['is_update'])
        ->assertJsonPath('data.content', $expected['content'])
        ->assertJsonPath('data.answer', $expected['answer']);

    // The feed and profile assertions for this sentinel live with the routes
    // that serve them, in the feed slice.
});

test('a question asked to someone else is not flagged as an update', function (): void {
    $sender = User::factory()->create();
    $receiver = User::factory()->create(['username' => 'bob']);

    $this->postJson(route('api.v1.questions.store'), [
        'to_username' => 'bob',
        'content' => 'What is your favorite PHP feature?',
    ], ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken])
        ->assertCreated()
        ->assertJsonPath('data.0.is_update', false)
        ->assertJsonPath('data.0.content', 'What is your favorite PHP feature?');
});

test('an authenticated user can publish a poll with a channel', function (): void {
    $user = User::factory()->create();
    $channel = App\Models\Channel::factory()->create(['name' => 'Laravel']);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Which framework?',
        'channel_id' => $channel->id,
        'poll_options' => ['Laravel', 'Rails'],
        'poll_duration' => 3,
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.0.channel.name', 'Laravel')
        ->assertJsonPath('data.0.poll.total_votes', 0)
        ->assertJsonCount(2, 'data.0.poll.options');

    $question = Question::sole();

    expect($question->channel_id)->toBe($channel->id)
        ->and($question->poll_expires_at)->not->toBeNull()
        ->and($question->pollOptions)->toHaveCount(2);
});

test('a new channel name is staged and created at publish', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'A new channel post.',
        'channel_name' => 'New Channel',
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.0.channel.name', 'New Channel');

    $channel = App\Models\Channel::where('slug', 'new-channel')->sole();

    expect(Question::sole()->channel_id)->toBe($channel->id);
});

test('channel names validate like the web composer', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Bad channel.',
        'channel_name' => 'x',
    ], $headers)->assertUnprocessable();
});

test('poll publishing validates its input', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Bad poll.',
        'poll_options' => ['Only one'],
        'poll_duration' => 3,
    ], $headers)->assertUnprocessable();

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Bad poll.',
        'poll_options' => ['Laravel', 'Rails'],
    ], $headers)->assertUnprocessable();

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Bad poll.',
        'poll_options' => ['Laravel', '   '],
        'poll_duration' => 3,
    ], $headers)->assertStatus(422);
});

test('an authenticated user can vote in a poll and toggle the vote', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['poll_expires_at' => now()->addDay()]);
    $yes = App\Models\PollOption::factory()->create(['question_id' => $question->id, 'text' => 'Yes']);
    $no = App\Models\PollOption::factory()->create(['question_id' => $question->id, 'text' => 'No']);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $yes->id], $headers)
        ->assertOk()
        ->assertJsonPath('data.total_votes', 1)
        ->assertJsonPath('data.user_vote_option_id', $yes->id);

    // Voting the same option again removes the vote.
    $this->postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $yes->id], $headers)
        ->assertOk()
        ->assertJsonPath('data.total_votes', 0)
        ->assertJsonPath('data.user_vote_option_id', null);

    $this->postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $no->id], $headers)
        ->assertOk()
        ->assertJsonPath('data.user_vote_option_id', $no->id);
});

test('poll voting rejects expired polls and foreign options', function (): void {
    $user = User::factory()->create();
    $expired = Question::factory()->create(['poll_expires_at' => now()->subDay()]);
    $option = App\Models\PollOption::factory()->create(['question_id' => $expired->id]);
    $other = Question::factory()->create(['poll_expires_at' => now()->addDay()]);
    $foreign = App\Models\PollOption::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.poll.vote', $expired), ['option_id' => $option->id], $headers)
        ->assertStatus(422);

    $this->postJson(route('api.v1.questions.poll.vote', $other), ['option_id' => $foreign->id], $headers)
        ->assertStatus(422);

    $plain = Question::factory()->create();
    $this->postJson(route('api.v1.questions.poll.vote', $plain), ['option_id' => $option->id], $headers)
        ->assertStatus(422);
});

test('an authenticated user can ask a question to another user', function (): void {
    Illuminate\Support\Facades\Notification::fake();

    $sender = User::factory()->create();
    $receiver = User::factory()->create(['username' => 'bob']);
    $headers = ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];

    $response = $this->postJson(route('api.v1.questions.store'), [
        'to_username' => 'bob',
        'content' => 'What is your favorite PHP feature?',
        'anonymously' => false,
    ], $headers);

    $response->assertCreated()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content', 'What is your favorite PHP feature?')
        ->assertJsonPath('data.0.to.username', 'bob');

    Illuminate\Support\Facades\Notification::assertSentTo(
        $receiver,
        App\Notifications\QuestionCreated::class,
    );
});

test('asking a question validates 255 character limit', function (): void {
    $sender = User::factory()->create();
    User::factory()->create(['username' => 'bob']);
    $headers = ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'to_username' => 'bob',
        'content' => str_repeat('a', 256),
    ], $headers)->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

test('a user can pin and unpin their answered questions', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $owner->id,
        'answer' => 'An answer to pin.',
        'pinned' => false,
    ]);

    $ownerHeaders = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];
    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.pin', $question), [], $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();

    $this->postJson(route('api.v1.questions.pin', $question), [], $ownerHeaders)
        ->assertOk()
        ->assertJsonPath('data.pinned', true);

    expect($question->fresh()->pinned)->toBeTrue();

    auth()->forgetGuards();

    $this->deleteJson(route('api.v1.questions.unpin', $question), [], $ownerHeaders)
        ->assertOk()
        ->assertJsonPath('data.pinned', false);

    expect($question->fresh()->pinned)->toBeFalse();
});

test('a user can answer or update an answer within 24 hours', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $owner->id,
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $ownerHeaders = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];
    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];

    $this->putJson(route('api.v1.questions.answer.update', $question), [
        'answer' => 'First answer.',
    ], $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();

    $this->putJson(route('api.v1.questions.answer.update', $question), [
        'answer' => 'First answer.',
    ], $ownerHeaders)->assertOk()
        ->assertJsonPath('data.answer', 'First answer.');

    $question->refresh();
    expect($question->answer)->toBe('First answer.')
        ->and($question->answer_created_at)->not->toBeNull();

    auth()->forgetGuards();

    $this->putJson(route('api.v1.questions.answer.update', $question), [
        'answer' => 'Updated answer.',
    ], $ownerHeaders)->assertOk()
        ->assertJsonPath('data.answer', 'Updated answer.');

    expect($question->fresh()->answer)->toBe('Updated answer.');
});

test('editing an answer clears the likes it had collected', function (): void {
    $owner = User::factory()->create();
    $liker = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $owner->id,
        'answer' => 'First answer.',
        'answer_created_at' => now(),
    ]);

    App\Models\Like::factory()->create(['user_id' => $liker->id, 'question_id' => $question->id]);

    $headers = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->putJson(route('api.v1.questions.answer.update', $question), [
        'answer' => 'Rewritten answer.',
    ], $headers)->assertOk()
        ->assertJsonPath('data.answer', 'Rewritten answer.')
        ->assertJsonPath('data.metrics.likes', 0);

    // Rewriting an answer drops its likes, the same way editing a question
    // does (UpdateQuestion::handle, $clearLikes). A like is a judgement about
    // particular words, and carrying it across a rewrite is a judgement
    // nobody made.
    expect($question->fresh()->likes()->count())->toBe(0);
});

test('answering a question for the first time is not treated as an edit', function (): void {
    $owner = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $owner->id,
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $headers = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->putJson(route('api.v1.questions.answer.update', $question), [
        'answer' => 'First answer.',
    ], $headers)->assertOk()
        ->assertJsonPath('data.answer', 'First answer.');

    // Pins the branch the clearing keys off: an answer that did not exist
    // before is not an edit, so the like delete must not even be considered.
    expect($question->fresh()->answer_created_at)->not->toBeNull();
});

test('answer cannot be updated after 24 hours', function (): void {
    $owner = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $owner->id,
        'answer' => 'Old answer',
        'answer_created_at' => now()->subHours(25),
    ]);
    $headers = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->putJson(route('api.v1.questions.answer.update', $question), [
        'answer' => 'Attempted edit.',
    ], $headers)->assertStatus(422);
});

test('a user can delete their questions', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $owner->id]);

    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];
    $ownerHeaders = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->deleteJson(route('api.v1.questions.destroy', $question), [], $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();

    $this->deleteJson(route('api.v1.questions.destroy', $question), [], $ownerHeaders)->assertNoContent();

    expect(Question::find($question->id))->toBeNull();
});

test('a user can ignore received questions', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $owner->id]);

    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];
    $ownerHeaders = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.ignore', $question), [], $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();

    $this->postJson(route('api.v1.questions.ignore', $question), [], $ownerHeaders)
        ->assertOk()
        ->assertJsonPath('data.ignored', true);

    expect($question->fresh()->is_ignored)->toBeTrue();
});

test('an anonymous question hides the author identity but preserves content', function (): void {
    $sender = User::factory()->create(['name' => 'Secret User', 'username' => 'secret']);
    $recipient = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $sender->id,
        'to_id' => $recipient->id,
        'content' => 'What is your favorite color?',
        'anonymously' => true,
    ]);

    $token = $recipient->createToken('test')->plainTextToken;

    $this->getJson(route('api.v1.questions.show', $question), [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonPath('data.content', 'What is your favorite color?')
        ->assertJsonPath('data.anonymously', true)
        ->assertJsonPath('data.from', null);
});
