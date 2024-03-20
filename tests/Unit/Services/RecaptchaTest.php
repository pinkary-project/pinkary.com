<?php

declare(strict_types=1);

use App\Services\Recaptcha;
use Illuminate\Support\Facades\Http;

it('verifies the recaptcha response', function () {
    $recaptcha = new Recaptcha('secret');

    $response = Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);

    expect($recaptcha->verify('127.0.0.1', 'response'))->toBeTrue();
});

it('does not verify the recaptcha response', function () {
    $recaptcha = new Recaptcha('secret');

    $response = Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => false,
        ]),
    ]);

    expect($recaptcha->verify('127.0.0.1', 'response'))->toBeFalse();
});
