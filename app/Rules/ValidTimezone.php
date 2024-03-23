<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidTimezone implements ValidationRule
{
    /**
     * Get the list of countries.
     *
     * @return array<string, string>
     *
     * @throws Exception
     */
    public static function timezones(): array
    {
        $timezones = [
            null => 'Select a timezone',
        ];

        $allTimezones = DateTimeZone::listIdentifiers();

        foreach ($allTimezones as $zone) {
            $offset = (new DateTimeImmutable('now', new DateTimeZone($zone)))->format('P');
            $timezones[$zone] = $zone === 'UTC' ? 'UTC (UTC+00:00)' : str($zone)->replace('_', ' ')->explode('/')->last().' (UTC'.$offset.')';
        }

        return $timezones;

    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     *
     * @throws Exception
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        assert(is_string($value));

        if (! array_key_exists($value, self::timezones())) {
            $fail(__('The :attribute must be a valid timezone.'));
        }
    }
}
