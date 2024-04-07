<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Requests;

use App\Rules\ValidTimezone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Stringable;

final class TimezoneUpdateRequest extends FormRequest
{
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
}
