<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class LogoutController
{
    /** Revoke the token this request authenticated with. */
    public function destroy(Request $request): Response
    {
        $plainTextToken = $request->bearerToken();

        if (is_string($plainTextToken)) {
            PersonalAccessToken::findToken($plainTextToken)?->delete();
        }

        return response()->noContent();
    }
}
