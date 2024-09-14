<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use DateTimeZone;
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

        if (! in_array($value, $this->getTimezones())) {
            $fail(__('The :attribute must be a valid timezone.'));
        }
    }

    /**
     * Get the list of valid timezones.
     *
     * @return array<int, string>
     */
    private function getTimezones(): array
    {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
    }
}
