<?php

declare(strict_types=1);

use App\Rules\Recaptcha;
use Illuminate\Support\Facades\Http;

it('verifies the recaptcha response', function () {
    $rule = new Recaptcha('127.0.0.1');

    $response = Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('g-recaptcha-response', 'valid', $fail);

    expect(true)->toBeTrue();
});

it('does not verify the recaptcha response', function () {
    $rule = new Recaptcha('127.0.0.1');

    $response = Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => false,
        ]),
    ]);

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('g-recaptcha-response', 'valid', $fail);

    expect(true)->toBeFalse();
})->fails();
