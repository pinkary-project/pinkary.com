<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Requests;

use App\Rules\ValidTimezone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stringable;

final class TimezoneUpdateRequest extends FormRequest
{
    public const int MAX_ATTEMPTS = 5;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        return [
            'timezone' => ['required', 'string', 'max:255', new ValidTimezone],
        ];
    }

    /**
     * Ensure the update timezone request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            RateLimiter::hit($this->throttleKey());

            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'timezone' => 'Too many timezone update attempts. Please try again in '.$seconds.' seconds.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate('timezone.update|'.$this->ip());
    }
}
