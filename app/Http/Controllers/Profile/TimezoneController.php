<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Rules\ValidTimezone;
use Illuminate\Http\Request;

final readonly class TimezoneController
{
    /**
     * Update the session's timezone.
     */
    public function update(Request $request): void
    {
        $validated = $request->validate([
            'timezone' => ['required', 'string', 'max:255', new ValidTimezone],
        ]);

        $request->session()->put('timezone', $validated['timezone']);
    }
}
