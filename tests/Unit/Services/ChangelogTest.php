<?php

declare(strict_types=1);

use App\Services\Changelog;

it('gets releases', function () {
    $changelog = new Changelog();

    $releases = $changelog->getReleases();

    expect($releases)->toBeArray();

    foreach ($releases as $version => $release) {
        expect($version)->toBeString()
            ->and($release)->toBeArray()
            ->and($release)->toHaveKey('publishedAt')
            ->and($release)->toHaveKey('changes')
            ->and($release['changes'])->toBeArray();
    }
});
