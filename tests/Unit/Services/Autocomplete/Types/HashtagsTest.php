<?php

declare(strict_types=1);

use App\Services\Autocomplete\Types\Hashtags;

it('has the correct delimiter', function () {
    expect((new Hashtags())->delimiter())->toBe('#');
});

it('has the correct match expression', function () {
    expect((new Hashtags())->matchExpression())->toBe('[a-zA-Z0-9]+');
});

it('returns the correct search results', function () {
    App\Models\Question::factory()
        ->sharedUpdate()
        ->count(7)
        ->sequence(
            ['answer' => '#foo'],
            ['answer' => '#Foo'], // should show #Foo, not #foo, because Foo has more questions_count.
            ['answer' => '#Foo'],
            ['answer' => '#fooz'], // #fooz should come first, most questions_count.
            ['answer' => '#fooz'],
            ['answer' => '#fooz'],
            ['answer' => '#bar'], // #bar should not be present in results.
        )
        ->create();

    $results = (new Hashtags())->search('f');

    expect($results->pluck('replacement')->all())
        ->toBe([
            '#fooz',
            '#Foo',
        ]);

    expect((new Hashtags())->search('doesnt_exist'))->toHaveCount(0);
});
