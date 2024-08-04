<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonTimeZone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class ValidTimezone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = type($value)->asString();

        if (blank(CarbonTimeZone::create($value))) {
            $fail("The $attribute is not a valid timezone.");
        }
    }
}
