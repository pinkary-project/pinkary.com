<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class MaxUploads implements ValidationRule
{
    public function __construct(private readonly int $maxUploads = 2)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_array($value) && count($value) > $this->maxUploads) {
            $fail("You can only upload $this->maxUploads images.");
        }
    }
}
