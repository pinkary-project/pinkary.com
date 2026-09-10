<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class NoEmailAlias implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $atPosition = mb_strrpos($value, '@');

        if ($atPosition !== false && str_contains(mb_substr($value, 0, $atPosition), '+')) {
            $fail('The :attribute cannot contain an email alias.');
        }
    }
}
