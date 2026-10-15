<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\Repost;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuestionCreated;
use App\Notifications\QuestionReposted;
use App\Notifications\UserFollowed;
use App\Notifications\UserMentioned;
use Illuminate\Support\Str;

test('a guest cannot read notifications', function (): void {
    $this->getJson(route('api.v1.notifications.index'))->assertUnauthorized();
    $this->postJson(route('api.v1.notifications.read'))->assertUnauthorized();
    $this->postJson(route('api.v1.notifications.ignore'))->assertUnauthorized();
});

test('a user can ignore received questions and remove notifications up to a timestamp', function (): void {
    $this->freezeTime();

    $user = User::factory()->create();
    $sender = User::factory()->create();
    $oldQuestion = Question::factory()->create([
        'from_id' => $sender->id,
        'to_id' => $user->id,
        'answer' => null,
        'answer_created_at' => null,
        'is_ignored' => false,
    ]);
    $oldQuestionNotification = $user->notifications()
        ->where('data->question_id', $oldQuestion->id)
        ->firstOrFail();
    $oldQuestionNotification->update(['created_at' => now()->subDay()]);

    $user->notify(new UserFollowed(User::factory()->create()));
    $oldFollowNotification = $user->notifications()->where('type', UserFollowed::class)->firstOrFail();
    $oldFollowNotification->update(['created_at' => now()->subDay()]);

    $recentQuestion = Question::factory()->create([
        'from_id' => $sender->id,
        'to_id' => $user->id,
        'answer' => null,
        'answer_created_at' => null,
        'is_ignored' => false,
    ]);
    $recentQuestionNotification = $user->notifications()
        ->where('data->question_id', $recentQuestion->id)
        ->firstOrFail();
    $recentQuestionNotification->update(['created_at' => now()->addMinute()]);

    $this->postJson(route('api.v1.notifications.ignore'), [
        'until_datetime' => now()->toIso8601String(),
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])
        ->assertNoContent();

    expect($oldQuestion->fresh()->is_ignored)->toBeTrue()
        ->and($recentQuestion->fresh()->is_ignored)->toBeFalse()
        ->and($user->notifications()->count())->toBe(1)
        ->and($user->notifications()->first()->id)->toBe($recentQuestionNotification->id);
});

test('a new question notification names the person who asked it', function (): void {
    $recipient = User::factory()->create();
    $asker = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $question = Question::factory()->create([
        'from_id' => $asker->id,
        'to_id' => $recipient->id,
        'answer' => null,
        'answer_created_at' => null,
        'anonymously' => false,
    ]);
    $recipient->notify(new QuestionCreated($question));

    $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$recipient->createToken('test')->plainTextToken])
        ->assertOk()
        ->assertJsonPath('data.0.actor.username', 'ada')
        ->assertJsonPath('data.0.action', 'asked you:')
        ->assertJsonPath('data.0.is_pending_question', true);
});

test('a comment notification identifies the parent post or comment', function (): void {
    $recipient = User::factory()->create();
    $commenter = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $update = Question::factory()->sharedUpdate()->create([
        'from_id' => $recipient->id,
        'to_id' => $recipient->id,
    ]);
    $comment = Question::factory()->create([
        'from_id' => $commenter->id,
        'to_id' => $recipient->id,
        'parent_id' => $update->id,
        'root_id' => $update->id,
        'answer' => 'A comment on the update.',
        'answer_created_at' => now(),
    ]);
    $nestedComment = Question::factory()->create([
        'from_id' => $commenter->id,
        'to_id' => $recipient->id,
        'parent_id' => $comment->id,
        'root_id' => $update->id,
        'answer' => 'A reply to the comment.',
        'answer_created_at' => now(),
    ]);
    $recipient->notify(new QuestionAnswered($comment));
    $recipient->notify(new QuestionAnswered($nestedComment));

    $rows = $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$recipient->createToken('test')->plainTextToken])
        ->assertOk()
        ->json('data');

    expect(collect($rows)->pluck('action'))
        ->toContain('commented on your Update:', 'commented on your comment:')
        ->and(collect($rows)->pluck('actor.username')->unique()->values()->all())->toBe(['ada']);
});

test('unsupported or incomplete notifications are skipped', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();

    foreach ([
        ['type' => 'UnknownNotification', 'data' => []],
        ['type' => UserFollowed::class, 'data' => ['follower_id' => 'invalid']],
        ['type' => UserFollowed::class, 'data' => ['follower_id' => PHP_INT_MAX]],
        ['type' => QuestionCreated::class, 'data' => []],
        ['type' => QuestionReposted::class, 'data' => ['question_id' => $question->id, 'repost_id' => PHP_INT_MAX]],
    ] as $notification) {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            ...$notification,
        ]);
    }

    $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.total_count', 5);
});

test('question rows carry actor, action, snippet, and target', function (): void {
    $user = User::factory()->create();
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $ada->id,
        'content' => 'What are you building?',
        'answer' => 'A <strong>thoughtful</strong> answer.',
        'answer_created_at' => now(),
        'anonymously' => false,
    ]);
    $user->notify(new QuestionAnswered($question));
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $rows = $this->getJson(route('api.v1.notifications.index'), $headers)->assertOk()->json('data');

    $answered = collect($rows)->firstWhere('type', 'QuestionAnswered');

    expect($answered['read'])->toBeFalse()
        ->and($answered['actor']['username'])->toBe('ada')
        ->and($answered['action'])->toBe('answered your question:')
        ->and($answered['snippet'])->toBe('What are you building?')
        ->and($answered['target'])->toMatchArray(['kind' => 'question', 'id' => $question->id]);

    $this->getJson(route('api.v1.notifications.index'), $headers)->assertJsonPath('meta.unread_count', count($rows));
});

test('repost rows carry the reposter and original question', function (): void {
    $owner = User::factory()->create();
    $reposter = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $question = Question::factory()->create([
        'to_id' => $owner->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful answer.',
        'answer_created_at' => now(),
    ]);
    $repost = Repost::factory()->create([
        'user_id' => $reposter->id,
        'question_id' => $question->id,
    ]);
    $rows = $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$owner->createToken('test')->plainTextToken])
        ->assertOk()
        ->json('data');

    $reposted = collect($rows)->firstWhere('type', 'QuestionReposted');

    expect($reposted['actor']['username'])->toBe('ada')
        ->and($reposted['action'])->toBe('reposted your question:')
        ->and($reposted['snippet'])->toBe('What are you building?')
        ->and($reposted['target']['id'])->toBe($question->id);
});

test('follow rows target the follower profile', function (): void {
    $user = User::factory()->create();
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $user->notify(new UserFollowed($ada));
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.notifications.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.action', 'followed you')
        ->assertJsonPath('data.0.snippet', null)
        ->assertJsonPath('data.0.target.kind', 'user')
        ->assertJsonPath('data.0.target.username', 'ada');
});

test('rows for deleted subjects are skipped like the web', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $user->notify(new QuestionAnswered($question));
    $question->delete();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.notifications.index'), $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.unread_count', 1);
});

test('a mention in a question is attributed to whoever asked it', function (): void {
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $bob = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $ada->id,
        'to_id' => $ada->id,
        'content' => 'What are you building?',
        'answer' => null,
        'answer_created_at' => null,
        'anonymously' => false,
    ]);

    $bob->notify(new UserMentioned($question));

    $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$bob->createToken('test')->plainTextToken])
        ->assertOk()
        ->assertJsonPath('data.0.type', 'UserMentioned')
        ->assertJsonPath('data.0.actor.username', 'ada')
        ->assertJsonPath('data.0.action', 'mentioned you in a question:')
        ->assertJsonPath('data.0.snippet', 'What are you building?')
        ->assertJsonPath('data.0.target.kind', 'question');
});

test('a mention in a comment names the commenter', function (): void {
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $bob = User::factory()->create();

    $root = Question::factory()->create([
        'from_id' => $ada->id,
        'to_id' => $ada->id,
        'content' => 'First post.',
        'answer' => 'First post.',
        'answer_created_at' => now(),
    ]);

    $comment = Question::factory()->create([
        'from_id' => $ada->id,
        'to_id' => $ada->id,
        'parent_id' => $root->id,
        'root_id' => $root->id,
        'content' => '__UPDATE__',
        'answer' => 'Adding to this.',
        'answer_created_at' => now(),
        'anonymously' => false,
    ]);

    $bob->notify(new UserMentioned($comment));

    $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$bob->createToken('test')->plainTextToken])
        ->assertOk()
        ->assertJsonPath('data.0.action', 'mentioned you in a comment:')
        ->assertJsonPath('data.0.actor.username', 'ada')
        ->assertJsonPath('data.0.snippet', 'Adding to this.')
        ->assertJsonPath('data.0.target.id', $comment->id);
});

test('a mention in a shared update is reported as an update', function (): void {
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $bob = User::factory()->create();

    $update = Question::factory()->sharedUpdate()->create([
        'from_id' => $ada->id,
        'to_id' => $ada->id,
        'answer' => 'Shipping today.',
        'answer_created_at' => now(),
        'anonymously' => false,
    ]);

    $bob->notify(new UserMentioned($update));

    $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$bob->createToken('test')->plainTextToken])
        ->assertOk()
        ->assertJsonPath('data.0.action', 'mentioned you in an update:')
        ->assertJsonPath('data.0.actor.username', 'ada')
        ->assertJsonPath('data.0.snippet', 'Shipping today.');
});

test('a mention in a question that was later answered credits the answerer', function (): void {
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $carol = User::factory()->create(['name' => 'Carol Reed', 'username' => 'carol']);
    $bob = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $ada->id,
        'to_id' => $carol->id,
        'content' => 'What are you building?',
        'answer' => 'Here is my take.',
        'answer_created_at' => now(),
        'anonymously' => false,
    ]);

    $bob->notify(new UserMentioned($question));

    $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$bob->createToken('test')->plainTextToken])
        ->assertOk()
        ->assertJsonPath('data.0.action', 'mentioned you in a question:')
        ->assertJsonPath('data.0.actor.username', 'carol');
});

test('a mention row for a deleted post is skipped', function (): void {
    $bob = User::factory()->create();
    $question = Question::factory()->create();

    $bob->notify(new UserMentioned($question));
    $question->delete();

    $this->getJson(route('api.v1.notifications.index'), ['Authorization' => 'Bearer '.$bob->createToken('test')->plainTextToken])
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('notifications can be marked as read', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $user->notify(new QuestionAnswered($question));
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.notifications.read'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.unread_count', 0);

    $this->getJson(route('api.v1.notifications.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.read', true)
        ->assertJsonPath('meta.unread_count', 0);
});

test('a user can delete their notification', function (): void {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $question = Question::factory()->create();
    $user->notify(new QuestionAnswered($question));

    $notification = $user->notifications()->first();

    $userHeaders = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];

    $this->deleteJson(route('api.v1.notifications.destroy', $notification->id), [], $strangerHeaders)
        ->assertNotFound();

    auth()->forgetGuards();

    $this->deleteJson(route('api.v1.notifications.destroy', $notification->id), [], $userHeaders)
        ->assertNoContent();

    expect($user->notifications()->count())->toBe(0);
});
