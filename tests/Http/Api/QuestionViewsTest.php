<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

it('records one guest view per post in two hours and accepts another viewer', function (): void {
    $question = Question::factory()->create(['views' => 0]);
    $viewer = (string) Str::uuid();
    $url = "/api/v1/questions/{$question->id}/views";

    $this->postJson($url, ['viewer_id' => $viewer])->assertNoContent();
    $this->postJson($url, ['viewer_id' => $viewer])->assertNoContent();
    expect($question->fresh()->views)->toBe(1);
    $this->postJson($url, ['viewer_id' => (string) Str::uuid()])->assertNoContent();
    expect($question->fresh()->views)->toBe(2);
    $this->travel(121)->minutes();
    $this->postJson($url, ['viewer_id' => $viewer])->assertNoContent();
    expect($question->fresh()->views)->toBe(3);
});

it('deduplicates authenticated views using the same user identity as the web', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['views' => 0]);
    $token = $user->createToken('Mobile')->plainTextToken;
    $url = "/api/v1/questions/{$question->id}/views";

    $this->withToken($token)->postJson($url)->assertNoContent();
    $this->withToken($token)->postJson($url, ['viewer_id' => (string) Str::uuid()])->assertNoContent();
    expect($question->fresh()->views)->toBe(1)
        ->and(Cache::has("viewed:question:{$question->id}:{$user->id}"))->toBeTrue();
});

it('does not record impressions just because the API fetched a post', function (): void {
    $question = Question::factory()->create(['views' => 0]);
    $this->getJson("/api/v1/questions/{$question->id}")->assertOk();
    expect($question->fresh()->views)->toBe(0);
});

it('requires a valid guest viewer uuid', function (array $payload): void {
    $question = Question::factory()->create(['views' => 0]);
    $this->postJson("/api/v1/questions/{$question->id}/views", $payload)->assertUnprocessable()->assertJsonValidationErrors('viewer_id');
    expect($question->fresh()->views)->toBe(0);
})->with([[[]], [['viewer_id' => 'arbitrary']], [['viewer_id' => null]]]);

it('does not count protected or moderated posts', function (array $attributes): void {
    $question = Question::factory()->create([...$attributes, 'views' => 0]);
    $this->postJson("/api/v1/questions/{$question->id}/views", ['viewer_id' => (string) Str::uuid()])->assertForbidden();
    expect($question->fresh()->views)->toBe(0);
})->with([
    'unanswered' => [['answer' => null, 'answer_created_at' => null]],
    'ignored' => [['is_ignored' => true]],
    'reported' => [['is_reported' => true]],
]);

it('ignores bot impressions', function (): void {
    $question = Question::factory()->create(['views' => 0]);
    $this->withHeaders(['User-Agent' => 'Storebot-Google'])->postJson("/api/v1/questions/{$question->id}/views", ['viewer_id' => (string) Str::uuid()])->assertNoContent();
    expect($question->fresh()->views)->toBe(0);
});

it('does not count an owners unanswered question', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id, 'answer' => null, 'answer_created_at' => null, 'views' => 0]);
    $this->withToken($user->createToken('Mobile')->plainTextToken)->postJson("/api/v1/questions/{$question->id}/views")->assertNoContent();
    expect($question->fresh()->views)->toBe(0);
});

it('returns not found for missing posts', function (): void {
    $this->postJson('/api/v1/questions/'.Str::uuid().'/views', ['viewer_id' => (string) Str::uuid()])->assertNotFound();
});
