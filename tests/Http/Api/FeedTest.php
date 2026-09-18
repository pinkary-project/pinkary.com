<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest can read the recent feed without logging in', function (): void {
    $user = User::factory()->create();
    $liker = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
    ]);
    App\Models\Like::factory()->create(['user_id' => $liker->id, 'question_id' => $question->id]);

    getJson(route('api.v1.feed.index'))
        ->assertOk()
        ->assertJsonPath('data.0.id', $question->id)
        ->assertJsonPath('data.0.answer', 'A thoughtful mobile experience.')
        ->assertJsonPath('data.0.metrics.liked', false)
        ->assertJsonPath('data.0.metrics.bookmarked', false)
        ->assertJsonPath('data.0.poll.user_vote_option_id', null);
});

test('a guest gets an empty following feed', function (): void {
    Question::factory()->create(['answer' => 'Hello.']);

    getJson(route('api.v1.feed.index', ['tab' => 'following']))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('an authenticated user can view the following API feed', function (): void {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $stranger = User::factory()->create();

    $user->following()->attach($author->id);

    $followed = Question::factory()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
    ]);

    Question::factory()->create([
        'from_id' => $stranger->id,
        'to_id' => $stranger->id,
        'anonymously' => false,
    ]);

    $token = $user->createToken('test')->plainTextToken;

    getJson(route('api.v1.feed.index', ['tab' => 'following']), [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $followed->id)
        ->assertJsonPath('data.0.answer', 'A thoughtful mobile experience.');
});

test('an authenticated user can view the trending API feed', function (): void {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
        'answer_created_at' => now(),
    ]);

    $token = $user->createToken('test')->plainTextToken;

    getJson(route('api.v1.feed.index', ['tab' => 'trending']), [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonPath('data.0.id', $question->id)
        ->assertJsonPath('data.0.answer', 'A thoughtful mobile experience.');
});

test('the API feed rejects an unknown tab', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    getJson(route('api.v1.feed.index', ['tab' => 'popular']), [
        'Authorization' => 'Bearer '.$token,
    ])->assertUnprocessable();
});
test('an authenticated user can view the API feed', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => User::factory(),
        'to_id' => $user->id,
        'content' => 'What are you building?',
        'answer' => 'A thoughtful mobile experience.',
        'anonymously' => false,
    ]);
    $token = $user->createToken('test')->plainTextToken;

    getJson(route('api.v1.feed.index'), [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonPath('data.0.id', $question->id)
        ->assertJsonPath('data.0.content', 'What are you building?')
        ->assertJsonPath('data.0.answer', 'A thoughtful mobile experience.')
        ->assertJsonStructure([
            'data' => [[
                'from' => ['id', 'name', 'username', 'avatar'],
                'to' => ['id', 'name', 'username', 'avatar'],
                'metrics' => ['likes', 'comments', 'liked', 'bookmarked'],
            ]],
        ]);
});
