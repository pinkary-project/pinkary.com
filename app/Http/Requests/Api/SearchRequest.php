<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class SearchRequest extends FormRequest
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
        return ['q' => ['required', 'string', 'min:1', 'max:50']];
    }

    /** The term to search for, stripped of any sigil. */
    public function term(): string
    {
        return mb_trim($this->string('q')->toString(), "@# \t\n\r\0\x0B");
    }
}
