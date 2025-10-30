<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Rules\ValidTimezone;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final readonly class UserTimezoneController
{
    /**
     * Update the session's timezone.
     */
    public function update(Request $request): Response
    {
        /** @var array<string, string> $validated */
        $validated = $request->validate([
            'timezone' => ['required', 'string', 'max:255', new ValidTimezone],
        ]);

        $request->session()->put('timezone', $validated['timezone']);

        return response()->noContent();
    }
}
