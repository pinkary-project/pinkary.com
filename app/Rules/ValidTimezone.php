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

        if (empty($this->getTimezones()[$value])) {
            $fail(__('The :attribute must be a valid timezone.'));
        }
    }

    /**
     * Get the list of valid timezones as keys.
     *
     * @return array<string, int>
     */
    private function getTimezones(): array
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);

        return array_flip($timezones);
    }
}
