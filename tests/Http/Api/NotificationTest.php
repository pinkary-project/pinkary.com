<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\UserFollowed;
use App\Notifications\UserMentioned;

test('a guest cannot read notifications', function (): void {
    $this->getJson(route('api.v1.notifications.index'))->assertUnauthorized();
    $this->postJson(route('api.v1.notifications.read'))->assertUnauthorized();
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
