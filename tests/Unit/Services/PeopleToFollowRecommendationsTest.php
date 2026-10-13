<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Services\PeopleToFollowRecommendations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::forget('top-50-users');
    Cache::forget('top-200-users');
    Cache::forget('verified-user-ids');
});

it('returns generic fallback users from the discovery pool', function (): void {
    User::factory(50)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    $outsideDiscoveryPool = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(1, ['answer' => 'answer'])
        ->create(['name' => 'Outside Discovery Pool']);

    $users = new PeopleToFollowRecommendations()->forContext(authenticatedUserId: null);

    expect($users)
        ->toHaveCount(5)
        ->and($users->contains(fn (User $user): bool => $user->is($outsideDiscoveryPool)))
        ->toBeFalse();
});

it('excludes users already followed by the authenticated user', function (): void {
    $user = User::factory()->create();

    $discoveryPool = User::factory(50)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    $followedUser = $discoveryPool->first();

    $user->following()->attach($followedUser);

    $users = new PeopleToFollowRecommendations()->forContext(authenticatedUserId: $user->id);

    expect($users->contains(fn (User $suggestedUser): bool => $suggestedUser->is($followedUser)))
        ->toBeFalse()
        ->and($users)
        ->toHaveCount(5);
});

it('shows the latest interacted users on profile pages before fallback users', function (): void {
    $profileUser = User::factory()->create();

    $interactedUsers = User::factory(6)->create();

    $interactedUsers->each(function (User $interactedUser, int $index) use ($profileUser): void {
        Question::factory()->create([
            'from_id' => $interactedUser->id,
            'to_id' => $profileUser->id,
            'answer' => "Answer {$index}",
            'updated_at' => now()->subMinutes($index),
        ]);
    });

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'profile',
        contextUserId: $profileUser->id,
    );

    expect($users->pluck('id')->all())
        ->toBe($interactedUsers->take(5)->pluck('id')->all());
});

it('falls back to generic suggestions when profile context has no user id', function (): void {
    $fallbackUser = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create(['is_verified' => true]);

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'profile',
        limit: 1,
    );

    expect($users->pluck('id')->all())
        ->toBe([$fallbackUser->id]);
});

it('tops up profile suggestions with fallback users when interactions are not enough', function (): void {
    $profileUser = User::factory()->create();

    $interactedUsers = User::factory(2)->create();

    $interactedUsers->each(function (User $interactedUser, int $index) use ($profileUser): void {
        Question::factory()->create([
            'from_id' => $interactedUser->id,
            'to_id' => $profileUser->id,
            'answer' => "Answer {$index}",
            'updated_at' => now()->subMinutes($index),
        ]);
    });

    $verifiedUsers = User::factory(2)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(3, ['answer' => 'answer'])
        ->create(['is_verified' => true]);

    $famousUser = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(3, ['answer' => 'answer'])
        ->create();

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'profile',
        contextUserId: $profileUser->id,
    );

    expect($users)
        ->toHaveCount(5)
        ->and($users->take(2)->pluck('id')->all())
        ->toBe($interactedUsers->pluck('id')->all())
        ->and($users->pluck('id')->all())
        ->toContain($verifiedUsers[0]->id, $verifiedUsers[1]->id, $famousUser->id);
});

it('shows the post user and thread users on question pages before falling back to recent interactions', function (): void {
    $postUser = User::factory()->create();
    $rootParticipant = User::factory()->create();
    $middleParticipant = User::factory()->create();
    $currentParticipant = User::factory()->create();
    $recentInteractionUser = User::factory()->create();

    Question::factory()->create([
        'from_id' => $recentInteractionUser->id,
        'to_id' => $postUser->id,
        'answer' => 'Recent interaction',
        'updated_at' => now()->subMinutes(10),
    ]);

    $rootQuestion = Question::factory()->create([
        'from_id' => $rootParticipant->id,
        'to_id' => $postUser->id,
        'created_at' => now()->subMinutes(3),
    ]);

    $middleQuestion = Question::factory()->create([
        'from_id' => $middleParticipant->id,
        'to_id' => $postUser->id,
        'parent_id' => $rootQuestion->id,
        'root_id' => $rootQuestion->id,
        'created_at' => now()->subMinutes(2),
    ]);

    $question = Question::factory()->create([
        'from_id' => $currentParticipant->id,
        'to_id' => $postUser->id,
        'parent_id' => $middleQuestion->id,
        'root_id' => $rootQuestion->id,
        'created_at' => now()->subMinute(),
    ]);

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'question',
        contextQuestionId: (string) $question->id,
    );

    expect($users->pluck('id')->all())
        ->toBe([
            $postUser->id,
            $currentParticipant->id,
            $middleParticipant->id,
            $rootParticipant->id,
            $recentInteractionUser->id,
        ]);
});

it('falls back to generic suggestions when question context has no question id', function (): void {
    $fallbackUser = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create(['is_verified' => true]);

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'question',
        limit: 1,
    );

    expect($users->pluck('id')->all())
        ->toBe([$fallbackUser->id]);
});

it('collects thread participants with a single query instead of a recursive CTE', function (): void {
    $postUser = User::factory()->create();
    $rootParticipant = User::factory()->create();
    $currentParticipant = User::factory()->create();

    $rootQuestion = Question::factory()->create([
        'from_id' => $rootParticipant->id,
        'to_id' => $postUser->id,
        'created_at' => now()->subMinutes(2),
    ]);

    $question = Question::factory()->create([
        'from_id' => $currentParticipant->id,
        'to_id' => $postUser->id,
        'parent_id' => $rootQuestion->id,
        'root_id' => $rootQuestion->id,
        'created_at' => now()->subMinute(),
    ]);

    DB::enableQueryLog();

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'question',
        contextQuestionId: (string) $question->id,
        limit: 3,
    );

    $queries = collect(DB::getQueryLog())->pluck('query');

    DB::disableQueryLog();

    // Only the thread query filters on root_id; the question lookup merely selects it.
    $threadQueries = $queries->filter(
        fn (string $query): bool => str_contains(str_replace('`', '', $query), 'root_id =')
    );

    expect($queries->implode(' '))->not->toContain('WITH RECURSIVE')
        ->and($threadQueries)->toHaveCount(1)
        ->and($users->pluck('id')->all())->toBe([
            $postUser->id,
            $currentParticipant->id,
            $rootParticipant->id,
        ]);
});

it('collects participants from every branch of the thread', function (): void {
    $postUser = User::factory()->create();
    $rootParticipant = User::factory()->create();
    $firstReplyParticipant = User::factory()->create();
    $siblingParticipant = User::factory()->create();

    $rootQuestion = Question::factory()->create([
        'from_id' => $rootParticipant->id,
        'to_id' => $postUser->id,
        'created_at' => now()->subMinutes(3),
    ]);

    $firstReply = Question::factory()->create([
        'from_id' => $firstReplyParticipant->id,
        'to_id' => $postUser->id,
        'parent_id' => $rootQuestion->id,
        'root_id' => $rootQuestion->id,
        'created_at' => now()->subMinutes(2),
    ]);

    $siblingReply = Question::factory()->create([
        'from_id' => $siblingParticipant->id,
        'to_id' => $postUser->id,
        'parent_id' => $rootQuestion->id,
        'root_id' => $rootQuestion->id,
        'created_at' => now()->subMinute(),
    ]);

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'question',
        contextQuestionId: (string) $firstReply->id,
        limit: 5,
    );

    expect($users->pluck('id')->all())->toBe([
        $postUser->id,
        $siblingParticipant->id,
        $firstReplyParticipant->id,
        $rootParticipant->id,
    ]);
});

it('cuts the thread at the requested number of suggestions', function (): void {
    $postUser = User::factory()->create();

    $rootQuestion = Question::factory()->create([
        'from_id' => $postUser->id,
        'to_id' => $postUser->id,
        'created_at' => now()->subDay(),
    ]);

    $participants = User::factory(25)->create();

    foreach ($participants as $offset => $participant) {
        Question::factory()->create([
            'from_id' => $participant->id,
            'to_id' => $postUser->id,
            'parent_id' => $rootQuestion->id,
            'root_id' => $rootQuestion->id,
            'created_at' => now()->subDays(25 - $offset),
        ]);
    }

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'question',
        contextQuestionId: (string) $rootQuestion->id,
        limit: 5,
    );

    $ids = $users->pluck('id')->all();

    expect($ids)->toBe([
        $postUser->id,
        $participants[24]->id,
        $participants[23]->id,
        $participants[22]->id,
        $participants[21]->id,
    ]);
});

it('falls back to generic suggestions when the question context cannot be found', function (): void {
    $fallbackUser = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create(['is_verified' => true]);

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        context: 'question',
        contextQuestionId: 'missing-question-id',
        limit: 1,
    );

    expect($users->pluck('id')->all())
        ->toBe([$fallbackUser->id]);
});

it('widens the famous user search when the top fifty are unavailable', function (): void {
    $user = User::factory()->create();

    $topFiftyUsers = User::factory(50)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    $overflowUsers = User::factory(5)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(1, ['answer' => 'answer'])
        ->create();

    $user->following()->attach($topFiftyUsers);

    $users = new PeopleToFollowRecommendations()->forContext(authenticatedUserId: $user->id);

    expect($users->pluck('id')->all())
        ->toEqualCanonicalizing($overflowUsers->pluck('id')->all());
});

it('returns empty arrays from helper methods when the requested limit is zero', function (): void {
    $recommendations = invade(new PeopleToFollowRecommendations());

    expect($recommendations->latestInteractedUserIds(1, null, [], 0))->toBeEmpty()
        ->and($recommendations->genericFallbackUserIds(null, [], 0))->toBeEmpty()
        ->and($recommendations->verifiedUserIds(null, [], 0))->toBeEmpty()
        ->and($recommendations->famousUserIds(null, [], 0, 50))->toBeEmpty()
        ->and($recommendations->availableUserIds([1], null, [], 0))->toBeEmpty();
});

it('returns empty results when helper inputs collapse to no user ids', function (): void {
    $recommendations = invade(new PeopleToFollowRecommendations());

    expect($recommendations->usersForIds([]))
        ->toBeEmpty()
        ->and($recommendations->availableUserIds([0, -1, 0], null, [], 1))->toBeEmpty();
});

it('returns only available users when fewer qualify than the requested limit', function (): void {
    // Only two users exist in the discovery pool — fewer than the default limit of
    // five — so the post-fetch count check fires but finds nothing extra to top up.
    User::factory(2)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    $users = new PeopleToFollowRecommendations()->forContext(
        authenticatedUserId: null,
        limit: 5,
    );

    expect($users)->toHaveCount(2);
});

it('caches the top 50 users', function (): void {
    User::factory(50)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    new PeopleToFollowRecommendations()->forContext(authenticatedUserId: null);

    expect(Cache::has('top-50-users'))->toBeTrue();
});

it('caches the verified users pool', function (): void {
    $verified = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->create(['is_verified' => true]);

    new PeopleToFollowRecommendations()->forContext(authenticatedUserId: null);

    expect(Cache::has('verified-user-ids:'))->toBeTrue()
        ->and(Cache::get('verified-user-ids:'))->toEqual([$verified->id]);

    $lateVerified = User::factory()
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->create(['is_verified' => true]);

    $users = new PeopleToFollowRecommendations()->forContext(authenticatedUserId: null);

    expect($users->contains(fn (User $user): bool => $user->is($lateVerified)))->toBeFalse();

    Cache::forget('verified-user-ids:');

    $users = new PeopleToFollowRecommendations()->forContext(authenticatedUserId: null);

    expect($users->contains(fn (User $user): bool => $user->is($lateVerified)))->toBeTrue();
});
