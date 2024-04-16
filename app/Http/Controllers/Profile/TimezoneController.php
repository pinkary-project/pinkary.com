<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Requests\UpdateTimezoneRequest;

final readonly class TimezoneController
{
    /**
     * Update the session's timezone.
     */
    public function update(UpdateTimezoneRequest $request): void
    {
        $request->session()->put('timezone', $request->validated(['timezone']));
    }
}
