<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;

final readonly class CaptchaResultController
{
    /** Finish native navigation without reflecting callback parameters. */
    public function __invoke(): Response
    {
        return response('<!doctype html><html lang="en"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Security check</title><p>You can return to the app.</p></html>')
            ->withHeaders(['Content-Type' => 'text/html; charset=UTF-8', 'Cache-Control' => 'no-store', 'Referrer-Policy' => 'no-referrer']);
    }
}
