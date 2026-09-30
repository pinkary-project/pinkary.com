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

test('a guest cannot like or bookmark questions', function (): void {
    $question = Question::factory()->create();

    $this->postJson(route('api.v1.questions.like', $question))->assertUnauthorized();
    $this->deleteJson(route('api.v1.questions.unlike', $question))->assertUnauthorized();
    $this->postJson(route('api.v1.questions.bookmark', $question))->assertUnauthorized();
    $this->deleteJson(route('api.v1.questions.unbookmark', $question))->assertUnauthorized();
});

test('an authenticated user can like and unlike a question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.like', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.liked', true)
        ->assertJsonPath('data.likes', 1);

    // Liking twice stays idempotent.
    $this->postJson(route('api.v1.questions.like', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.likes', 1);

    $this->assertDatabaseCount('likes', 1);

    $this->deleteJson(route('api.v1.questions.unlike', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.liked', false)
        ->assertJsonPath('data.likes', 0);

    // Unliking twice stays a no-op.
    $this->deleteJson(route('api.v1.questions.unlike', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.likes', 0);

    $this->assertDatabaseCount('likes', 0);
});

test('an authenticated user can bookmark and unbookmark a question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarked', true)
        ->assertJsonPath('data.bookmarks', 1);

    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarks', 1);

    $this->assertDatabaseCount('bookmarks', 1);

    $this->deleteJson(route('api.v1.questions.unbookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarked', false)
        ->assertJsonPath('data.bookmarks', 0);

    $this->assertDatabaseCount('bookmarks', 0);
});

test('liking a missing question returns not found', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson('/api/v1/questions/does-not-exist/like', [], $headers)->assertNotFound();
});

test('the API feed reflects likes and bookmarks', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => User::factory(),
        'to_id' => $user->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
    ]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.like', $question), [], $headers)->assertOk();
    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)->assertOk();

    $this->getJson(route('api.v1.feed.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.metrics.liked', true)
        ->assertJsonPath('data.0.metrics.likes', 1)
        ->assertJsonPath('data.0.metrics.bookmarked', true);
});

test('a guest cannot comment on a question', function (): void {
    $question = Question::factory()->create();

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Nice post.',
    ])->assertUnauthorized();
});

test('a guest can read a question with neutral viewer state', function (): void {
    $viewer = User::factory()->create();
    $author = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $recipient = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $author->id,
        'to_id' => $recipient->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
    ]);

    $viewer->bookmarks()->create(['question_id' => $question->id]);
    App\Models\Like::factory()->create(['user_id' => $viewer->id, 'question_id' => $question->id]);

    $this->getJson(route('api.v1.questions.show', $question))
        ->assertOk()
        ->assertJsonPath('data.id', $question->id)
        ->assertJsonPath('data.content', 'What are you building?')
        ->assertJsonPath('data.answer', 'A thoughtful mobile experience.')
        ->assertJsonPath('data.from.username', 'ada')
        ->assertJsonPath('data.metrics.likes', 1)
        ->assertJsonPath('data.metrics.bookmarks', 1)
        // Viewer-specific state must be false, never leak or error.
        ->assertJsonPath('data.metrics.liked', false)
        ->assertJsonPath('data.metrics.bookmarked', false)
        ->assertJsonPath('data.poll.user_vote_option_id', null);
});

test('a bearer token still fills in viewer state on a public read', function (): void {
    $viewer = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => User::factory(),
        'to_id' => User::factory(),
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
    ]);
    App\Models\Like::factory()->create(['user_id' => $viewer->id, 'question_id' => $question->id]);

    $this->getJson(route('api.v1.questions.show', $question), [
        'Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken,
    ])
        ->assertOk()
        ->assertJsonPath('data.metrics.liked', true)
        ->assertJsonPath('data.metrics.bookmarked', false);
});

test('a guest cannot read an unanswered question', function (): void {
    $question = Question::factory()->create([
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $this->getJson(route('api.v1.questions.show', $question))->assertForbidden();
});

test('a guest cannot read an ignored or reported question', function (): void {
    $ignored = Question::factory()->create(['is_ignored' => true]);
    $reported = Question::factory()->create(['is_reported' => true]);

    $this->getJson(route('api.v1.questions.show', $ignored))->assertForbidden();
    $this->getJson(route('api.v1.questions.show', $reported))->assertForbidden();
});

test('a guest can read a question thread without leaking moderated ancestors', function (): void {
    $user = User::factory()->create();

    $ids = $this->postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.'],
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])
        ->assertCreated()
        ->json('data.*.id');

    Question::query()->whereKey($ids[0])->update(['is_ignored' => true]);

    $this->getJson(route('api.v1.questions.show', $ids[1]))
        ->assertOk()
        ->assertJsonPath('data.answer', 'Second post.')
        ->assertJsonPath('data.thread.root_id', $ids[0])
        ->assertJsonCount(0, 'thread');
});

test('a guest can read comments with neutral viewer state', function (): void {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
        'content' => '__UPDATE__',
        'answer' => 'Hello, Pinkary.',
        'answer_created_at' => now(),
    ]);

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Great update!',
    ], ['Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken])->assertCreated();

    $this->getJson(route('api.v1.questions.comments.index', $question))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        // A comment is a shared update: the text lives in `answer` and
        // `content` is the __UPDATE__ sentinel, exactly as the web stores it
        // (Livewire\Questions\Create:471-475). That is what lets it become
        // the thread's representative in the feed.
        ->assertJsonPath('data.0.answer', 'Great update!')
        ->assertJsonPath('data.0.content', null)
        ->assertJsonPath('data.0.is_update', true)
        ->assertJsonPath('data.0.metrics.liked', false)
        ->assertJsonPath('data.0.metrics.bookmarked', false);
});

test('a guest cannot read the comments of an unanswered question', function (): void {
    $question = Question::factory()->create(['answer' => null, 'answer_created_at' => null]);

    $this->getJson(route('api.v1.questions.comments.index', $question))->assertForbidden();
});

test('commenting on a question you may not view is forbidden', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['answer' => null, 'answer_created_at' => null]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Nice post.',
    ], $headers)->assertForbidden();
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

    $this->getJson(route('api.v1.feed.index'))
        ->assertOk()
        ->assertJsonPath('data.0.is_update', $expected['is_update'])
        ->assertJsonPath('data.0.content', $expected['content'])
        ->assertJsonPath('data.0.answer', $expected['answer']);

    $this->getJson(route('api.v1.users.questions.index', $user->username))
        ->assertOk()
        ->assertJsonPath('data.0.is_update', $expected['is_update'])
        ->assertJsonPath('data.0.content', $expected['content'])
        ->assertJsonPath('data.0.answer', $expected['answer']);
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

test('an authenticated user can view a single question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => User::factory(),
        'to_id' => $user->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
    ]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.questions.show', $question), $headers)
        ->assertOk()
        ->assertJsonPath('data.id', $question->id)
        ->assertJsonPath('data.content', 'What are you building?')
        ->assertJsonPath('data.answer', 'A thoughtful mobile experience.')
        ->assertJsonStructure([
            'data' => [
                'from' => ['id', 'name', 'username', 'avatar'],
                'to' => ['id', 'name', 'username', 'avatar'],
                'metrics' => ['likes', 'comments', 'liked', 'bookmarked'],
            ],
        ]);
});

test('viewing a missing question returns not found', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson('/api/v1/questions/not-a-valid-uuid', $headers)->assertNotFound();
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

test('comments list oldest first', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    foreach (['First comment.', 'Second comment.', 'Third comment.'] as $content) {
        $this->postJson(route('api.v1.questions.comments.store', $question), [
            'content' => $content,
        ], $headers)->assertCreated();
    }

    $this->getJson(route('api.v1.questions.comments.index', $question), $headers)
        ->assertOk()
        ->assertJsonCount(3, 'data')
        // Comments are shared updates, so their text is the answer.
        ->assertJsonPath('data.0.answer', 'First comment.')
        ->assertJsonPath('data.1.answer', 'Second comment.')
        ->assertJsonPath('data.2.answer', 'Third comment.');

    $this->getJson(route('api.v1.questions.show', $question), $headers)
        ->assertOk()
        ->assertJsonPath('data.metrics.comments', 3);
});

test('the feed exposes thread, channel, poll, and edited state', function (): void {
    $user = User::factory()->create();
    $channel = App\Models\Channel::factory()->create(['name' => 'Laravel']);
    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => '__UPDATE__',
        'answer' => 'A channel post.',
        'answer_created_at' => now(),
        'channel_id' => $channel->id,
        'poll_expires_at' => now()->addDay(),
    ]);
    App\Models\PollOption::factory()->create(['question_id' => $question->id, 'text' => 'Yes', 'votes_count' => 2]);
    App\Models\PollOption::factory()->create(['question_id' => $question->id, 'text' => 'No', 'votes_count' => 1]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.feed.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.thread.root_id', null)
        ->assertJsonPath('data.0.channel.name', 'Laravel')
        ->assertJsonPath('data.0.poll.total_votes', 3)
        ->assertJsonPath('data.0.poll.user_vote_option_id', null)
        ->assertJsonPath('data.0.poll.expired', false)
        ->assertJsonPath('data.0.edited', false)
        ->assertJsonPath('data.0.preview', null)
        ->assertJsonPath('data.0.images', [])
        ->assertJsonPath('data.0.metrics.bookmarks', 0);

    expect($question->fresh()->poll_expires_at)->not->toBeNull();
    $timeRemaining = $this->getJson(route('api.v1.feed.index'), $headers)->json('data.0.poll.time_remaining');
    expect($timeRemaining)->toBeString()->not->toBe('');
});

test('avatars resolve to hosts the requesting client can reach', function (): void {
    $user = User::factory()->create();
    Question::factory()->create(['to_id' => $user->id, 'answer' => 'Hello.']);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    // Asset URLs are built from APP_URL (pinkary.test), which a device
    // on the network cannot resolve — the API must rewrite them to the
    // request host instead — hit an explicit foreign host, like a
    // phone on the LAN, rather than route() which reuses APP_URL.
    $response = $this->getJson('http://192.168.31.59:8001/api/v1/feed', $headers)->assertOk();

    expect($response->json('data.0.to.avatar'))->toStartWith('http://192.168.31.59:8001');
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

test('feed rows carry their visible thread context like the web', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $ids = $this->postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.', 'Third post.'],
    ], $headers)->assertCreated()->json('data.*.id');

    $feed = $this->getJson(route('api.v1.feed.index'), $headers)->assertOk()->json('data');

    $third = collect($feed)->firstWhere('id', $ids[2]);

    expect($third['thread']['more'])->toBeFalse()
        ->and(collect($third['thread']['posts'])->pluck('answer')->all())->toBe(['First post.', 'Second post.']);
});

test('reading the feed does not re-parse and rewrite every row', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.'],
    ], $headers)->assertCreated();

    // Prime the parsed payload, then watch for writes on a pure read.
    $this->getJson(route('api.v1.feed.index'), $headers)->assertOk();

    $writes = [];

    Illuminate\Support\Facades\DB::listen(function ($query) use (&$writes): void {
        if (str_starts_with(mb_strtolower(mb_trim($query->sql)), 'update')) {
            $writes[] = $query->sql;
        }
    });

    $this->getJson(route('api.v1.feed.index'), $headers)->assertOk();

    expect($writes)->toBeEmpty();
});

test('showing a thread post includes its ancestors oldest first', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $ids = $this->postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.', 'Third post.'],
    ], $headers)->assertCreated()->json('data.*.id');

    $this->getJson(route('api.v1.questions.show', $ids[2]), $headers)
        ->assertOk()
        ->assertJsonPath('data.answer', 'Third post.')
        ->assertJsonPath('data.thread.parent_id', $ids[1])
        ->assertJsonPath('data.thread.root_id', $ids[0])
        ->assertJsonCount(2, 'thread')
        ->assertJsonPath('thread.0.answer', 'First post.')
        ->assertJsonPath('thread.1.answer', 'Second post.');
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

test('liking, bookmarking and voting require a viewable question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'answer' => null,
        'answer_created_at' => null,
        'poll_expires_at' => now()->addDay(),
    ]);
    $option = App\Models\PollOption::factory()->create(['question_id' => $question->id]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.like', $question), [], $headers)->assertForbidden();
    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)->assertForbidden();
    $this->postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $option->id], $headers)
        ->assertForbidden();

    expect($question->likes()->count())->toBe(0)
        ->and($question->bookmarks()->count())->toBe(0)
        ->and($question->pollVotes()->count())->toBe(0);
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

test('a guest cannot view who liked a question', function (): void {
    $question = Question::factory()->create();

    $this->getJson(route('api.v1.questions.likes.index', $question))->assertUnauthorized();
});

test('a question owner can view who liked the question', function (): void {
    $owner = User::factory()->create();
    $liker = User::factory()->create(['name' => 'Liker User']);
    $stranger = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $owner->id]);

    App\Models\Like::create([
        'user_id' => $liker->id,
        'question_id' => $question->id,
    ]);

    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];
    $ownerHeaders = ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.questions.likes.index', $question), $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();

    $this->getJson(route('api.v1.questions.likes.index', $question), $ownerHeaders)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $liker->id)
        ->assertJsonPath('data.0.name', 'Liker User');
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
