<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use MrPunyapal\Turnstile\Data\TurnstileResponse;
use Throwable;

final readonly class MobileTurnstile implements ValidationRule
{
    public function __construct(private string $action, private string $state) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->verify($value)) {
            $fail(__('Please complete the security check and try again.'));
        }
    }

    private function verify(mixed $token): bool
    {
        $secret = config('services.turnstile.secret');
        $hostname = config('services.turnstile.hostname') ?: parse_url(config()->string('app.url'), PHP_URL_HOST);

        if (! is_string($secret) || $secret === '' || ! is_string($hostname) || $hostname === ''
            || ! is_string($token) || $token === '' || mb_strlen($token) > 2048 || ! Str::isUuid($this->state)) {
            return false;
        }

        try {
            $response = Http::asForm()->acceptJson()->connectTimeout(3)->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                ]);

            $payload = $response->json();

            if (! $response->successful() || ! is_array($payload)) {
                return false;
            }

            $result = TurnstileResponse::fromArray([
                'success' => $payload['success'] ?? false,
                'hostname' => $payload['hostname'] ?? null,
                'action' => $payload['action'] ?? null,
                'cdata' => $payload['cdata'] ?? null,
            ]);

            return $result->isValidFor($hostname, $this->action) && $result->cdata === $this->state;
        } catch (Throwable) {
            return false;
        }
    }
}
