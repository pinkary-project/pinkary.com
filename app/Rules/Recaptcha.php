<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\IpUtils;

final readonly class Recaptcha implements ValidationRule
{
    /**
     * The recaptcha URL.
     */
    private const string URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Create a new rule instance.
     */
    public function __construct(private ?string $ip)
    {
        //
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = type($value)->asString();
        assert(is_string($this->ip));

        if (! $this->verify($this->ip, $value)) {
            $fail(__('The recaptcha response was invalid.'));
        }
    }

    /**
     * Verify the recaptcha response.
     */
    public function verify(string $ipAddress, string $response): bool
    {
        $payload = [
            'secret' => config()->string('services.recaptcha.secret'),
            'response' => $response,
            'remoteip' => IpUtils::anonymize($ipAddress),
        ];

        $response = Http::asForm()->post(self::URL, $payload);

        /** @var array{success: bool}|null $result */
        $result = $response->json();

        return $response->successful() && ! is_null($result) && $result['success'] === true;
    }
}
