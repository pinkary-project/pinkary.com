<?php

declare(strict_types=1);

use App\Models\Hashtag;
use App\Models\Like;
use App\Models\Question;
use App\Models\User;

test('a guest can read only answered visible posts for a hashtag', function (): void {
    $hashtag = Hashtag::factory()->create(['name' => 'laravel']);
    $visible = Question::factory()->create([
        'content' => 'A public post',
        'answer' => 'Visible answer',
    ]);
    $visible->hashtags()->attach($hashtag);

    $unanswered = Question::factory()->create([
        'content' => 'Not public yet',
        'answer' => null,
    ]);
    $unanswered->hashtags()->attach($hashtag);

    $ignored = Question::factory()->create([
        'content' => 'Ignored post',
        'is_ignored' => true,
    ]);
    $ignored->hashtags()->attach($hashtag);

    $reported = Question::factory()->create([
        'content' => 'Reported post',
        'is_reported' => true,
    ]);
    $reported->hashtags()->attach($hashtag);

    $otherHashtag = Hashtag::factory()->create(['name' => 'php']);
    $otherPost = Question::factory()->create(['content' => 'Another topic']);
    $otherPost->hashtags()->attach($otherHashtag);

    $this->getJson(route('api.v1.hashtags.questions.index', $hashtag->id))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visible->id)
        ->assertJsonPath('data.0.answer', 'Visible answer')
        ->assertJsonPath('data.0.metrics.liked', false);
});

test('an authenticated viewer gets their reaction state in hashtag posts', function (): void {
    $hashtag = Hashtag::factory()->create(['name' => 'laravel']);
    $viewer = User::factory()->create();
    $question = Question::factory()->create();
    $question->hashtags()->attach($hashtag);
    Like::factory()->create(['user_id' => $viewer->id, 'question_id' => $question->id]);
    $token = $viewer->createToken('test')->plainTextToken;

    $this->getJson(route('api.v1.hashtags.questions.index', $hashtag->id), [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.metrics.liked', true)
        ->assertJsonPath('data.0.metrics.likes', 1);
});

test('hashtag posts are paginated newest first', function (): void {
    $hashtag = Hashtag::factory()->create(['name' => 'laravel']);
    $old = Question::factory()->create(['updated_at' => now()->subDay()]);
    $old->hashtags()->attach($hashtag);
    $new = Question::factory()->create(['updated_at' => now()]);
    $new->hashtags()->attach($hashtag);

    $response = $this->getJson(route('api.v1.hashtags.questions.index', [
        'hashtag' => $hashtag->id,
        'per_page' => 1,
    ]))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $new->id);

    expect($response->json('links.next'))->toBeString()->toContain('page=2');

    $this->getJson(route('api.v1.hashtags.questions.index', [
        'hashtag' => $hashtag->id,
        'per_page' => 1,
        'page' => 2,
    ]))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $old->id)
        ->assertJsonPath('links.next', null);
});

test('a missing hashtag returns 404', function (): void {
    $this->getJson(route('api.v1.hashtags.questions.index', 999999))->assertNotFound();
});
