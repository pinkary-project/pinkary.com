<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\IpUtils;

final readonly class Recaptcha
{
    /**
     * The recaptcha URL.
     */
    private const string URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Create a new recaptcha instance.
     */
    public function __construct(
        private string $secret,
    ) {
        //
    }

    /**
     * Verify the recaptcha response.
     */
    public function verify(string $ipAddress, string $response): bool
    {
        $payload = [
            'secret' => $this->secret,
            'response' => $response,
            'remoteip' => IpUtils::anonymize($ipAddress),
        ];

        $response = Http::asForm()->post(self::URL, $payload);

        /** @var array{success: bool}|null $result */
        $result = $response->json();

        return $response->successful() && ! is_null($result) && $result['success'] === true;
    }
}
