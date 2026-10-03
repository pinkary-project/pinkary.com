<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\MobileCaptcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final readonly class CaptchaController
{
    /** Return challenge requirements without exposing secrets. */
    public function show(Request $request, MobileCaptcha $captcha): JsonResponse
    {
        $request->validate(['action' => ['bail', 'required', 'string', Rule::in(['register', 'post', 'comment'])]]);
        /** @var User|null $user */
        $user = $request->user();

        return response()->json(['data' => [
            'required' => $captcha->required($request->string('action')->toString(), $user),
            'widget_path' => route('api.v1.captcha.widget', absolute: false),
            'return_path' => route('api.v1.captcha.result', absolute: false),
        ]])->header('Cache-Control', 'no-store');
    }
}
