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
     * Get the list of unsupported timezones in PHP.
     *
     * @var array<int, string>
     */
    private static array $unsupportedTimezones = [
        'Pacific/Johnston',
        'America/Santa_Isabel',
        'America/Shiprock',
        'America/Yellowknife',
        'America/Rainy_River',
        'America/Montreal',
        'America/Nipigon',
        'America/Pangnirtung',
        'America/Thunder_Bay',
        'America/Godthab',
        'Europe/Kiev',
        'Europe/Uzhgorod',
        'Europe/Zaporozhye',
        'Asia/Rangoon',
        'Asia/Chongqing',
        'Asia/Harbin',
        'Asia/Kashgar',
        'Australia/Currie',
        'Antarctica/South_Pole',
        'Pacific/Enderbury',
    ];

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

        foreach (array_merge($allTimezones, self::$unsupportedTimezones) as $zone) {

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
