<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\MobileCaptcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use MrPunyapal\Turnstile\TurnstileWidget;

final class CaptchaController
{
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

    public function widget(Request $request, TurnstileWidget $widget): Response
    {
        $request->validate([
            'action' => ['bail', 'required', 'string', Rule::in(['register', 'post', 'comment'])],
            'state' => ['required', 'uuid'],
        ]);

        $siteKey = config('services.turnstile.key');
        $secret = config('services.turnstile.secret');
        abort_unless(is_string($siteKey) && $siteKey !== '' && is_string($secret) && $secret !== '', 503);

        return response($widget->html([
            'sitekey' => $siteKey,
            'size' => 'compact',
            'action' => $request->string('action')->toString(),
            'cdata' => $request->string('state')->toString(),
            'return_path' => route('api.v1.captcha.result', absolute: false),
        ]))->withHeaders([
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    public function result(): Response
    {
        return response('<!doctype html><html lang="en"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Security check</title><p>You can return to the app.</p></html>')
            ->withHeaders(['Content-Type' => 'text/html; charset=UTF-8', 'Cache-Control' => 'no-store', 'Referrer-Policy' => 'no-referrer']);
    }
}
