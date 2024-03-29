<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class ValidUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        assert(is_string($value));

        try {
            $failed = Http::timeout(3)->get($value)->failed();
        } catch (ConnectionException) {
            $failed = true;
        }

        if ($failed) {
            $fail(__('The :attribute should be a valid URL.'));
        }
    }
}
