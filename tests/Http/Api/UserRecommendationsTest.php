<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('guests receive verified generic recommendations in pages', function (): void {
    Cache::flush();

    $recommendedUsers = User::factory(6)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://github.com/{$user->username}"])
        ->create(['is_verified' => true]);
    User::factory()->unverified()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://github.com/{$user->username}"])
        ->create(['is_verified' => true]);

    $firstPage = $this->getJson(route('api.v1.users.recommendations', ['per_page' => 2]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.current_page', 1);
    $secondPage = $this->getJson(route('api.v1.users.recommendations', ['per_page' => 2, 'page' => 2]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.current_page', 2);
    $thirdPage = $this->getJson(route('api.v1.users.recommendations', ['per_page' => 2, 'page' => 3]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.current_page', 3)
        ->assertJsonPath('links.next', null);

    expect($firstPage->json('links.next'))->toBeString()->toContain('page=2')
        ->and($secondPage->json('links.next'))->toBeString()->toContain('page=3');

    $pageIds = array_merge(
        array_column($firstPage->json('data'), 'id'),
        array_column($secondPage->json('data'), 'id'),
        array_column($thirdPage->json('data'), 'id'),
    );

    expect(collect($pageIds)->sort()->values()->all())
        ->toBe($recommendedUsers->pluck('id')->sort()->values()->all());
});

test('authenticated recommendations exclude the viewer, users they follow, and unverified accounts', function (): void {
    Cache::flush();

    $viewer = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://github.com/{$user->username}"])
        ->create(['is_verified' => true]);
    $followed = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://github.com/{$user->username}"])
        ->create(['is_verified' => true]);
    $incoming = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://github.com/{$user->username}"])
        ->create(['is_verified' => true]);
    $other = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://github.com/{$user->username}"])
        ->create(['is_verified' => true]);
    User::factory()->unverified()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://github.com/{$user->username}"])
        ->create(['is_verified' => true]);

    $viewer->following()->attach($followed->id);
    $incoming->following()->attach($viewer->id);

    $response = $this->getJson(route('api.v1.users.recommendations', ['per_page' => 50]), [
        'Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken,
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $usersById = collect($response->json('data'))->keyBy('id');

    expect($usersById->keys()->sort()->values()->all())
        ->toBe(collect([$incoming->id, $other->id])->sort()->values()->all())
        ->and($usersById[$incoming->id]['follows_me'])->toBeTrue()
        ->and($usersById[$incoming->id]['followed_by_me'])->toBeFalse();
});

test('profile recommendations use recent interactions and the default page size', function (): void {
    Cache::flush();

    $profile = User::factory()->create();
    $olderInteraction = User::factory()->create();
    $newerInteraction = User::factory()->create();

    Question::factory()->create([
        'from_id' => $olderInteraction->id,
        'to_id' => $profile->id,
        'updated_at' => now()->subMinutes(10),
    ]);
    Question::factory()->create([
        'from_id' => $newerInteraction->id,
        'to_id' => $profile->id,
        'updated_at' => now(),
    ]);

    $this->getJson(route('api.v1.users.recommendations', [
        'context' => 'profile',
        'context_user_id' => (string) $profile->id,
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $newerInteraction->id)
        ->assertJsonPath('data.1.id', $olderInteraction->id)
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.current_page', 1);
});

test('question recommendations include users from a question that guests can view', function (): void {
    Cache::flush();

    $author = User::factory()->create();
    $recipient = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $author->id,
        'to_id' => $recipient->id,
    ]);

    $this->getJson(route('api.v1.users.recommendations', [
        'context' => 'question',
        'context_question_id' => $question->id,
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $recipient->id)
        ->assertJsonPath('data.1.id', $author->id);
});

test('question recommendations return forbidden for a question the guest cannot view', function (): void {
    Cache::flush();

    $question = Question::factory()->create([
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $this->getJson(route('api.v1.users.recommendations', [
        'context' => 'question',
        'context_question_id' => $question->id,
    ]))->assertForbidden();
});

test('recommendation requests reject invalid contexts and pagination values', function (array $query, array $errors): void {
    $this->getJson(route('api.v1.users.recommendations', $query))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'unknown context' => [['context' => 'network'], ['context']],
    'profile requires a user' => [['context' => 'profile'], ['context_user_id']],
    'profile user must exist' => [['context' => 'profile', 'context_user_id' => 999999999], ['context_user_id']],
    'generic context prohibits a user' => [['context' => 'generic', 'context_user_id' => 999999999], ['context_user_id']],
    'question requires an id' => [['context' => 'question'], ['context_question_id']],
    'question id must be a UUID' => [['context' => 'question', 'context_question_id' => 'invalid'], ['context_question_id']],
    'question must exist' => [['context' => 'question', 'context_question_id' => '00000000-0000-4000-8000-000000000000'], ['context_question_id']],
    'per page must be positive' => [['per_page' => 0], ['per_page']],
    'per page cannot exceed the maximum' => [['per_page' => 51], ['per_page']],
    'page must be positive' => [['page' => 0], ['page']],
]);
