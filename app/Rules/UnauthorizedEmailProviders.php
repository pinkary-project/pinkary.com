<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\File;

final readonly class UnauthorizedEmailProviders implements ValidationRule
{
    /**
     * The location where the unauthorized email providers is stored.
     */
    public const string STORAGE_FILE_PATH = 'app/disposable_email_blocklist.conf';

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = type($value)->asString();

        if (mb_strpos($value, '@') !== false) {
            [$emailAccount, $emailProvider] = explode('@', $value);

            if (in_array($emailProvider, $this->getUnauthorizedEmailProviders(), true)) {
                $fail('The :attribute belongs to an unauthorized email provider.');

                return;
            }
        } else {
            $fail('The :attribute doesn\'t have an @.');

            return;
        }
    }

    /**
     * Gets the unauthorized email providers from the stored file.
     *
     * @return array<int, string>
     */
    private function getUnauthorizedEmailProviders(): array
    {
        return explode("\n", trim(
            File::exists($filePath = storage_path(self::STORAGE_FILE_PATH))
                ? File::get($filePath)
                : ''
        ));
    }
}
