<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Auth\Requests\TimezoneUpdateRequest;

final readonly class TimezoneController
{
    /**
     * Update the session's timezone.
     */
    public function update(TimezoneUpdateRequest $request): void
    {
        $timezone = $request->input('timezone', 'UTC');

        $request->session()->put('timezone', $timezone);
    }
}
