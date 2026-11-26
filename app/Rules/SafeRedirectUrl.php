<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class SafeRedirectUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Str::isUrl($value, ['https'])) {
            $fail('The redirect destination must be a valid HTTPS URL.');

            return;
        }

        $parts = parse_url($value);

        if ($parts === false || ! isset($parts['host'])) {
            $fail('The redirect destination must be a valid HTTPS URL.');

            return;
        }

        if (array_key_exists('user', $parts) || array_key_exists('pass', $parts)) {
            $fail('The redirect destination must not contain credentials.');

            return;
        }

        $host = $this->normalizeHost($parts['host']);

        if ($host === null) {
            $fail('The redirect destination must use an ASCII (punycode) hostname.');

            return;
        }

        $applicationHost = parse_url(config()->string('app.url'), PHP_URL_HOST);

        if (! is_string($applicationHost)) {
            return;
        }

        $applicationHost = $this->normalizeHost($applicationHost);

        if ($applicationHost === null) {
            return;
        }

        if ($host === $applicationHost || str_ends_with($host, '.'.$applicationHost)) {
            $fail('The redirect destination cannot point to Pinkary.');
        }
    }

    /**
     * Normalize a hostname so IDN and percent-encoded variants cannot bypass the host check.
     */
    private function normalizeHost(string $host): ?string
    {
        $host = mb_strtolower(mb_rtrim(rawurldecode($host), '.'));

        if (! function_exists('idn_to_ascii')) {
            return preg_match('/[^\x00-\x7F]/', $host) === 1 ? null : $host;
        }

        $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        return is_string($asciiHost) ? mb_strtolower(mb_rtrim($asciiHost, '.')) : $host;
    }
}
