<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Rules\NoBlankCharacters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreCommentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['content' => ['required', 'string', 'min:1', 'max:1000', new NoBlankCharacters]];
    }
}
