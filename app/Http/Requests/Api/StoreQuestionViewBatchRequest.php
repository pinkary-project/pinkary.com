<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreQuestionViewBatchRequest extends FormRequest
{
    /** Authorize this request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_ids' => ['required', 'array', 'list', 'max:10'],
            'question_ids.*' => ['required', 'uuid'],
            'viewer_id' => [Rule::requiredIf($this->user() === null), 'nullable', 'uuid'],
        ];
    }
}
