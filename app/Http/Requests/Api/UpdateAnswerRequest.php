<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Rules\NoBlankCharacters;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'answer' => ['required', 'string', 'max:1000', new NoBlankCharacters],
        ];
    }
}
