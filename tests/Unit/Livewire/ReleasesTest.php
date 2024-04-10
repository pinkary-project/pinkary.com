<?php

declare(strict_types=1);

use App\Livewire\Changelog\Releases;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('fetches and caches release data when mounted', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            'data' => [
                'repository' => [
                    'releases' => [
                        'nodes' => [
                            [
                                'name' => 'Release 1',
                                'publishedAt' => '2022-01-01T00:00:00Z',
                                'description' => '## Description 1',
                            ],
                            [
                                'name' => 'Release 2',
                                'publishedAt' => '2022-02-01T00:00:00Z',
                                'description' => '## Description 2',
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    Cache::shouldReceive('remember')->once()->with('git-releases', 720, Mockery::type('Closure'))->andReturnUsing(function ($key, $minutes, $callback) {
        return $callback();
    });

    $component = Livewire::test(Releases::class);

    $expectedReleases = [
        [
            'name' => 'Release 1',
            'published_at' => 'January 1, 2022',
            'items' => [
                [
                    'title' => 'Description 1',
                    'changes' => [],
                ],
            ],
        ],
        [
            'name' => 'Release 2',
            'published_at' => 'February 1, 2022',
            'items' => [
                [
                    'title' => 'Description 2',
                    'changes' => [],
                ],
            ],
        ],
    ];

    $component->assertSet('releases', $expectedReleases);
});
