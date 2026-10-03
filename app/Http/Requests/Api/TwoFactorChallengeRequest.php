<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class TwoFactorChallengeRequest extends FormRequest
{
    /** Authorize this request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'challenge' => ['required', 'string'],
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'challenge.required' => 'The two factor challenge is required.',
        ];
    }

    /** Require a one-time password or recovery code. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('code') || $this->filled('recovery_code')) {
                return;
            }

            $validator->errors()->add('code', 'A two factor code or recovery code is required.');
        });
    }

    /** Read the validated challenge token. */
    public function challenge(): string
    {
        $challenge = $this->validated('challenge');

        return is_string($challenge) ? $challenge : '';
    }

    /** The one-time password, if one was sent. */
    public function code(): ?string
    {
        $code = $this->validated('code');

        return is_string($code) && $code !== '' ? $code : null;
    }

    /** The recovery code, if one was sent. */
    public function recoveryCode(): ?string
    {
        $recoveryCode = $this->validated('recovery_code');

        return is_string($recoveryCode) && $recoveryCode !== '' ? $recoveryCode : null;
    }
}
