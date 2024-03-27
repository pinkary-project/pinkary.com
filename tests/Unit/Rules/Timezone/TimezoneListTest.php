<?php

declare(strict_types=1);

use App\Rules\ValidTimezone;

it('check if current time zones is the same as previous timezone list', function () {

    $allTimezone = ValidTimezone::timezones();

    $previousTimezone = json_decode((string) file_get_contents(__DIR__.'/Fixture/previous-timezone.json'), true);

    assert(is_array($previousTimezone));

    foreach (array_filter($previousTimezone) as $timezone => $zone) {
        expect(in_array($timezone, array_keys($allTimezone)))->toBeTrue();
    }

});
