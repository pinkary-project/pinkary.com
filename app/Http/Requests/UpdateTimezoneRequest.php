<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ValidTimezone;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateTimezoneRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'timezone' => ['required', 'string', 'max:255', new ValidTimezone],
        ];
    }
}
