<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class LogoutController
{
    public function __invoke(Request $request): Response
    {
        $plainTextToken = $request->bearerToken();

        if (is_string($plainTextToken)) {
            PersonalAccessToken::findToken($plainTextToken)?->delete();
        }

        return response()->noContent();
    }
}
