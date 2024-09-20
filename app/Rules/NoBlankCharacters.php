<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class NoBlankCharacters implements ValidationRule
{
    private const string BLANK_CHARACTERS_PATTERN = '/^[\s\x{2005}\x{2006}\x{2007}\x{2008}\x{2009}\x{200A}\x{2028}\x{205F}\x{3000}]*$/u';

    private const string FORMAT_CHARACTERS_PATTERN = '/\p{Cf}/u';

    /**
     * Validate the value of the given attribute.
     *
     * @param  string  $attribute  The name of the attribute being validated
     * @param  mixed  $value  The value of the attribute
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = type($value)->asString();

        if (preg_match(self::BLANK_CHARACTERS_PATTERN, $value) || preg_match(self::FORMAT_CHARACTERS_PATTERN, $value)) {
            $fail('The :attribute field cannot contain blank characters.');
        }
    }
}
