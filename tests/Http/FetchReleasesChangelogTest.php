<?php

declare(strict_types=1);

use App\Services\GitHub;
use Illuminate\Support\Facades\Http;

it('fetches releases from GitHub', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            'data' => [
                'repository' => [
                    'releases' => [
                        'nodes' => [
                            [
                                'name' => 'Release 1',
                                'publishedAt' => '2022-01-01T00:00:00Z',
                                'description' => 'Description 1',
                            ],
                            [
                                'name' => 'Release 2',
                                'publishedAt' => '2022-02-01T00:00:00Z',
                                'description' => 'Description 2',
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $github = new GitHub('test-token');
    $releases = $github->getReleases();

    expect($releases)->toBeArray()->and($releases)->toHaveCount(2)
        ->and($releases[0])->toBeArray()->and($releases[0])->toHaveKeys(['name', 'published_at', 'items'])
        ->and($releases[0]['name'])->toBe('Release 1')
        ->and($releases[0]['published_at'])->toBe('January 1, 2022');
});
