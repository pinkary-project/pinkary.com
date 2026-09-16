<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest cannot view the API feed', function (): void {
    getJson(route('api.v1.feed.index'))->assertUnauthorized();
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
