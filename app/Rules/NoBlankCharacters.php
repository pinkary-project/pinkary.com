<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class NoBlankCharacters implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = type($value)->asString();

        if (preg_match("/\p{Cf}/u", $value)) {
            $fail('The :attribute field cannot contain blank characters.');
        }
    }
}
