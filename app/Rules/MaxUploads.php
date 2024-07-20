<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class MaxUploads implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(public readonly int $maxUploads = 1)
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_array($value) && count($value) > $this->maxUploads) {
            $fail("You can only upload $this->maxUploads images.");
        }
    }
}
