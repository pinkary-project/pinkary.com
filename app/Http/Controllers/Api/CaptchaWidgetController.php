<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use MrPunyapal\Turnstile\TurnstileWidget;

final readonly class CaptchaWidgetController
{
    /** Host the challenge on the configured API origin. */
    public function __invoke(Request $request, TurnstileWidget $widget): Response
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
}
