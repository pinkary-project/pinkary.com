<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Foundation\Http\FormRequest;
use Stringable;

final class AvatarUploadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidatorAwareRule|ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {

        return [
            'avatar' => ['required', 'image', 'max:8192'],
        ];
    }
}
