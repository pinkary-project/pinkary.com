<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Rules\ValidTimezone;
use Illuminate\Http\Request;

final readonly class UserTimezoneController
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
