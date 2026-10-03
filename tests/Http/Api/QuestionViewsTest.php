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

it('records a batch of ten guest impressions with two hour deduplication', function (): void {
    $this->freezeTime();
    $questions = Question::factory()->count(10)->create(['views' => 0]);
    $payload = ['question_ids' => $questions->modelKeys(), 'viewer_id' => (string) Str::uuid()];

    $this->postJson('/api/v1/questions/views', $payload)->assertNoContent();
    $this->postJson('/api/v1/questions/views', $payload)->assertNoContent();

    foreach ($questions as $question) {
        expect($question->fresh()->views)->toBe(1);
    }

    $this->travel(121)->minutes();
    $this->postJson('/api/v1/questions/views', $payload)->assertNoContent();
    foreach ($questions as $question) {
        expect($question->fresh()->views)->toBe(2);
    }
});

it('deduplicates batch views against single views with the authenticated web identity', function (): void {
    $user = User::factory()->create();
    $questions = Question::factory()->count(2)->create(['views' => 0]);
    $token = $user->createToken('Mobile')->plainTextToken;
    $this->withToken($token)->postJson("/api/v1/questions/{$questions[0]->id}/views")->assertNoContent();

    $this->withToken($token)->postJson('/api/v1/questions/views', ['question_ids' => $questions->modelKeys()])->assertNoContent();
    $this->withToken($token)->postJson('/api/v1/questions/views', [
        'question_ids' => $questions->modelKeys(), 'viewer_id' => (string) Str::uuid(),
    ])->assertNoContent();

    foreach ($questions as $question) {
        expect($question->fresh()->views)->toBe(1)
            ->and(Cache::has("viewed:question:{$question->id}:{$user->id}"))->toBeTrue();
    }
});

it('counts duplicate ids within a batch only once', function (): void {
    $question = Question::factory()->create(['views' => 0]);

    $this->postJson('/api/v1/questions/views', [
        'question_ids' => [$question->id, $question->id], 'viewer_id' => (string) Str::uuid(),
    ])->assertNoContent();

    expect($question->fresh()->views)->toBe(1);
});

it('skips protected moderated and missing posts without dropping valid batch views', function (): void {
    $visible = Question::factory()->create(['views' => 0]);
    $unanswered = Question::factory()->create(['answer' => null, 'answer_created_at' => null, 'views' => 0]);
    $ignored = Question::factory()->create(['is_ignored' => true, 'views' => 0]);
    $reported = Question::factory()->create(['is_reported' => true, 'views' => 0]);

    $this->postJson('/api/v1/questions/views', [
        'question_ids' => [$visible->id, $unanswered->id, $ignored->id, $reported->id, (string) Str::uuid()],
        'viewer_id' => (string) Str::uuid(),
    ])->assertNoContent();

    expect($visible->fresh()->views)->toBe(1)
        ->and($unanswered->fresh()->views)->toBe(0)
        ->and($ignored->fresh()->views)->toBe(0)
        ->and($reported->fresh()->views)->toBe(0);
});

it('acknowledges a batch with no viewable posts without recording impressions', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id, 'answer' => null, 'answer_created_at' => null, 'views' => 0]);

    $this->withToken($user->createToken('Mobile')->plainTextToken)->postJson('/api/v1/questions/views', [
        'question_ids' => [$question->id, (string) Str::uuid()],
    ])->assertNoContent();

    expect($question->fresh()->views)->toBe(0);
});

it('ignores bot batches', function (): void {
    $questions = Question::factory()->count(2)->create(['views' => 0]);

    $this->withHeaders(['User-Agent' => 'Storebot-Google'])->postJson('/api/v1/questions/views', [
        'question_ids' => $questions->modelKeys(), 'viewer_id' => (string) Str::uuid(),
    ])->assertNoContent();

    foreach ($questions as $question) {
        expect($question->fresh()->views)->toBe(0);
    }
});

it('returns 422 for invalid batch payloads without counting posts', function (array $input, string $field, string $message): void {
    $question = Question::factory()->create(['views' => 0]);

    $this->postJson('/api/v1/questions/views', [...$input, 'viewer_id' => (string) Str::uuid()])
        ->assertUnprocessable()->assertJsonValidationErrors([$field => $message]);

    expect($question->fresh()->views)->toBe(0);
})->with([
    'missing ids' => [[], 'question_ids', 'The question ids field is required.'],
    'empty batch' => [['question_ids' => []], 'question_ids', 'The question ids field is required.'],
    'not an array' => [['question_ids' => 'not-an-array'], 'question_ids', 'The question ids field must be an array.'],
    'not a list' => [['question_ids' => ['post' => '00000000-0000-4000-8000-000000000001']], 'question_ids', 'The question ids field must be a list.'],
    'over ten ids' => [['question_ids' => array_fill(0, 11, '00000000-0000-4000-8000-000000000001')], 'question_ids', 'The question ids field must not have more than 10 items.'],
    'invalid uuid' => [['question_ids' => ['arbitrary']], 'question_ids.0', 'The question_ids.0 field must be a valid UUID.'],
    'missing item' => [['question_ids' => [null]], 'question_ids.0', 'The question_ids.0 field is required.'],
]);

it('returns 422 for missing or invalid guest batch identities', function (array $input, string $message): void {
    $question = Question::factory()->create(['views' => 0]);

    $this->postJson('/api/v1/questions/views', [...$input, 'question_ids' => [$question->id]])
        ->assertUnprocessable()->assertJsonValidationErrors(['viewer_id' => $message]);

    expect($question->fresh()->views)->toBe(0);
})->with([
    'missing viewer' => [[], 'The viewer id field is required.'],
    'null viewer' => [['viewer_id' => null], 'The viewer id field is required.'],
    'invalid viewer' => [['viewer_id' => 'arbitrary'], 'The viewer id field must be a valid UUID.'],
]);
