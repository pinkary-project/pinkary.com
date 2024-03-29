<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

final class ActiveUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        assert(is_string($value));

        if (! $this->isLinkActive($value)) {
            $fail(__('The link appears to be broken.'));
        }

    }

    /**
     * Check if the link is active.
     */
    public function isLinkActive(string $url): bool
    {
        try {
            $response = Http::head($url);

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
