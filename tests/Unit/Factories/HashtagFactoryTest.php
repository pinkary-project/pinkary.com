<?php

declare(strict_types=1);

use App\Models\Hashtag;

it('creates a hashtag with a unique name', function () {
    $hashtags = Hashtag::factory(40)->create();

    $this->assertCount(40, $hashtags);
    $this->assertEquals(40, $hashtags->unique('name')->count());
});
