<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('a guest cannot publish a thread', function (): void {
    postJson(route('api.v1.questions.store'), [
        'content' => 'Hello, Pinkary.',
    ])->assertUnauthorized();
});

test('an authenticated user can publish a shared update', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    postJson(route('api.v1.questions.store'), [
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

    postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.', 'Third post.'],
    ], [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated()
        ->assertJsonCount(3, 'data');

    assertDatabaseCount('questions', 3);

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

    postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['   ', null, 'Second post.'],
    ], [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated()
        ->assertJsonCount(2, 'data');

    assertDatabaseCount('questions', 2);
});

test('publishing a thread validates its input', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $headers = ['Authorization' => 'Bearer '.$token];

    postJson(route('api.v1.questions.store'), [], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');

    postJson(route('api.v1.questions.store'), [
        'content' => str_repeat('a', 1001),
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');

    postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => array_fill(0, 10, 'Extra post.'),
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('thread_posts');
});

test('a guest cannot like or bookmark questions', function (): void {
    $question = Question::factory()->create();

    postJson(route('api.v1.questions.like', $question))->assertUnauthorized();
    deleteJson(route('api.v1.questions.unlike', $question))->assertUnauthorized();
    postJson(route('api.v1.questions.bookmark', $question))->assertUnauthorized();
    deleteJson(route('api.v1.questions.unbookmark', $question))->assertUnauthorized();
});

test('an authenticated user can like and unlike a question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    postJson(route('api.v1.questions.like', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.liked', true)
        ->assertJsonPath('data.likes', 1);

    // Liking twice stays idempotent.
    postJson(route('api.v1.questions.like', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.likes', 1);

    assertDatabaseCount('likes', 1);

    deleteJson(route('api.v1.questions.unlike', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.liked', false)
        ->assertJsonPath('data.likes', 0);

    // Unliking twice stays a no-op.
    deleteJson(route('api.v1.questions.unlike', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.likes', 0);

    assertDatabaseCount('likes', 0);
});

test('an authenticated user can bookmark and unbookmark a question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    postJson(route('api.v1.questions.bookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarked', true)
        ->assertJsonPath('data.bookmarks', 1);

    postJson(route('api.v1.questions.bookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarks', 1);

    assertDatabaseCount('bookmarks', 1);

    deleteJson(route('api.v1.questions.unbookmark', $question), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.bookmarked', false)
        ->assertJsonPath('data.bookmarks', 0);

    assertDatabaseCount('bookmarks', 0);
});

test('liking a missing question returns not found', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    postJson('/api/v1/questions/does-not-exist/like', [], $headers)->assertNotFound();
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

    postJson(route('api.v1.questions.like', $question), [], $headers)->assertOk();
    postJson(route('api.v1.questions.bookmark', $question), [], $headers)->assertOk();

    getJson(route('api.v1.feed.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.metrics.liked', true)
        ->assertJsonPath('data.0.metrics.likes', 1)
        ->assertJsonPath('data.0.metrics.bookmarked', true);
});

test('a guest cannot view questions or comments', function (): void {
    $question = Question::factory()->create();

    getJson(route('api.v1.questions.show', $question))->assertUnauthorized();
    getJson(route('api.v1.questions.comments.index', $question))->assertUnauthorized();
    postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Nice post.',
    ])->assertUnauthorized();
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

    getJson(route('api.v1.questions.show', $question), $headers)
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

    getJson('/api/v1/questions/not-a-valid-uuid', $headers)->assertNotFound();
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

    postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Great update!',
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.content', 'Great update!')
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

    $commentId = postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'First comment.',
    ], $headers)->assertCreated()->json('data.id');

    $reply = Question::query()->whereKey($commentId)->sole();

    postJson(route('api.v1.questions.comments.store', $reply), [
        'content' => 'A reply.',
    ], $headers)->assertCreated();

    $nested = Question::query()->where('content', 'A reply.')->sole();

    expect($nested->parent_id)->toBe($reply->id)
        ->and($nested->root_id)->toBe($question->id);
});

test('commenting validates its input', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    postJson(route('api.v1.questions.comments.store', $question), [], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');

    postJson(route('api.v1.questions.comments.store', $question), [
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
        postJson(route('api.v1.questions.comments.store', $question), [
            'content' => $content,
        ], $headers)->assertCreated();
    }

    getJson(route('api.v1.questions.comments.index', $question), $headers)
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.content', 'First comment.')
        ->assertJsonPath('data.1.content', 'Second comment.')
        ->assertJsonPath('data.2.content', 'Third comment.');

    getJson(route('api.v1.questions.show', $question), $headers)
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

    getJson(route('api.v1.feed.index'), $headers)
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
    $timeRemaining = getJson(route('api.v1.feed.index'), $headers)->json('data.0.poll.time_remaining');
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

    postJson(route('api.v1.questions.store'), [
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

    postJson(route('api.v1.questions.store'), [
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

    postJson(route('api.v1.questions.store'), [
        'content' => 'Bad channel.',
        'channel_name' => 'x',
    ], $headers)->assertUnprocessable();
});

test('poll publishing validates its input', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    postJson(route('api.v1.questions.store'), [
        'content' => 'Bad poll.',
        'poll_options' => ['Only one'],
        'poll_duration' => 3,
    ], $headers)->assertUnprocessable();

    postJson(route('api.v1.questions.store'), [
        'content' => 'Bad poll.',
        'poll_options' => ['Laravel', 'Rails'],
    ], $headers)->assertUnprocessable();

    postJson(route('api.v1.questions.store'), [
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

    postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $yes->id], $headers)
        ->assertOk()
        ->assertJsonPath('data.total_votes', 1)
        ->assertJsonPath('data.user_vote_option_id', $yes->id);

    // Voting the same option again removes the vote.
    postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $yes->id], $headers)
        ->assertOk()
        ->assertJsonPath('data.total_votes', 0)
        ->assertJsonPath('data.user_vote_option_id', null);

    postJson(route('api.v1.questions.poll.vote', $question), ['option_id' => $no->id], $headers)
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

    postJson(route('api.v1.questions.poll.vote', $expired), ['option_id' => $option->id], $headers)
        ->assertStatus(422);

    postJson(route('api.v1.questions.poll.vote', $other), ['option_id' => $foreign->id], $headers)
        ->assertStatus(422);

    $plain = Question::factory()->create();
    postJson(route('api.v1.questions.poll.vote', $plain), ['option_id' => $option->id], $headers)
        ->assertStatus(422);
});

test('feed rows carry their visible thread context like the web', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $ids = postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.', 'Third post.'],
    ], $headers)->assertCreated()->json('data.*.id');

    $feed = getJson(route('api.v1.feed.index'), $headers)->assertOk()->json('data');

    $third = collect($feed)->firstWhere('id', $ids[2]);

    expect($third['thread']['more'])->toBeFalse()
        ->and(collect($third['thread']['posts'])->pluck('answer')->all())->toBe(['First post.', 'Second post.']);
});

test('showing a thread post includes its ancestors oldest first', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $ids = postJson(route('api.v1.questions.store'), [
        'content' => 'First post.',
        'thread_posts' => ['Second post.', 'Third post.'],
    ], $headers)->assertCreated()->json('data.*.id');

    getJson(route('api.v1.questions.show', $ids[2]), $headers)
        ->assertOk()
        ->assertJsonPath('data.answer', 'Third post.')
        ->assertJsonPath('data.thread.parent_id', $ids[1])
        ->assertJsonPath('data.thread.root_id', $ids[0])
        ->assertJsonCount(2, 'thread')
        ->assertJsonPath('thread.0.answer', 'First post.')
        ->assertJsonPath('thread.1.answer', 'Second post.');
});
