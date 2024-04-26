<?php

declare(strict_types=1);

use App\Exceptions\GitHubException;
use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('gets non-verified if does not have a GitHub username', function () {
    $user = User::factory()->create([
        'is_verified' => true,
        'github_username' => null,
    ]);

    SyncVerifiedUser::dispatchSync($user);

    expect($user->fresh()->is_verified)->toBeFalse();
});

it('gets non-verified if GitHub username is not sponsoring us', function () {
    $user = User::factory()->create([
        'is_verified' => true,
        'github_username' => 'test',
    ]);

    // $response->json('data.user.sponsorshipForViewerAsSponsorable')

    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'user' => [
                    'sponsorshipForViewerAsSponsorable' => [],
                ],
            ],
        ]),
    ]);

    SyncVerifiedUser::dispatchSync($user);

    expect($user->fresh()->is_verified)->toBeFalse();
});

it('does not touch on the verified status if GitHub call fails', function () {
    $user = User::factory()->create([
        'is_verified' => true,
        'github_username' => 'test',
    ]);

    Http::fake([
        'github.com/*' => Http::response([], 500),
    ]);

    SyncVerifiedUser::dispatchSync($user);
})->throws(GitHubException::class);

it('does not get verified if GitHub username is sponsoring us but the tier is less than $9', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'github_username' => 'test',
    ]);

    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'user' => [
                    'sponsorshipForViewerAsSponsorable' => [
                        [
                            'monthlyPriceInDollars' => 8,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    SyncVerifiedUser::dispatchSync($user);

    expect($user->fresh()->is_verified)->toBeFalse();
});

it('gets verified if GitHub username is sponsoring us', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'github_username' => 'test',
    ]);

    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'user' => [
                    'sponsorshipForViewerAsSponsorable' => [
                        [
                            'monthlyPriceInDollars' => 9,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    SyncVerifiedUser::dispatchSync($user);

    expect($user->fresh()->is_verified)->toBeTrue();
});
