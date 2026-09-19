<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\UserFollowed;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('a guest cannot read notifications', function (): void {
    getJson(route('api.v1.notifications.index'))->assertUnauthorized();
    postJson(route('api.v1.notifications.read'))->assertUnauthorized();
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

    $rows = getJson(route('api.v1.notifications.index'), $headers)->assertOk()->json('data');

    $answered = collect($rows)->firstWhere('type', 'QuestionAnswered');

    expect($answered['read'])->toBeFalse()
        ->and($answered['actor']['username'])->toBe('ada')
        ->and($answered['action'])->toBe('answered your question:')
        ->and($answered['snippet'])->toBe('What are you building?')
        ->and($answered['target'])->toMatchArray(['kind' => 'question', 'id' => $question->id]);

    getJson(route('api.v1.notifications.index'), $headers)->assertJsonPath('meta.unread_count', count($rows));
});

test('follow rows target the follower profile', function (): void {
    $user = User::factory()->create();
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $user->notify(new UserFollowed($ada));
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    getJson(route('api.v1.notifications.index'), $headers)
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

    getJson(route('api.v1.notifications.index'), $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.unread_count', 1);
});

test('notifications can be marked as read', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $user->notify(new QuestionAnswered($question));
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    postJson(route('api.v1.notifications.read'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.unread_count', 0);

    getJson(route('api.v1.notifications.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.read', true)
        ->assertJsonPath('meta.unread_count', 0);
});
