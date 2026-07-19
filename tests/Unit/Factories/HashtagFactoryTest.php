<?php

declare(strict_types=1);

use App\Models\Hashtag;

it('creates a hashtag with a unique name', function (): void {
    $hashtags = Hashtag::factory(40)->create();

    expect($hashtags)->toHaveCount(40)
        ->and($hashtags->unique('name')->count())->toEqual(40);
});
