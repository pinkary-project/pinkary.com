<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\BlockedAccount;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class NotBlockedAccount implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && BlockedAccount::where('email', $value)->exists()) {
            $fail('This email has been blocked.');
        }
    }
}
