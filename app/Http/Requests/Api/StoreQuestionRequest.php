<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Rules\NoBlankCharacters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreQuestionRequest extends FormRequest
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
        $isAsking = $this->filled('to_username') && $this->input('to_username') !== $this->user()?->username;
        $maxContent = $isAsking ? 255 : 1000;

        return [
            'to_username' => ['sometimes', 'nullable', 'string', 'exists:users,username'],
            'anonymously' => ['sometimes', 'boolean'],
            'content' => ['required', 'string', 'min:1', "max:{$maxContent}", new NoBlankCharacters],
            'thread_posts' => ['sometimes', 'array', 'max:9'],
            'thread_posts.*' => ['nullable', 'string', 'min:1', 'max:1000', new NoBlankCharacters],
            'channel_id' => ['sometimes', 'nullable', 'integer', 'exists:channels,id'],
            'channel_name' => ['sometimes', 'nullable', 'string', 'min:2', 'max:50', 'regex:/^[\pL\pN\s\-_]+$/u'],
            'poll_options' => ['sometimes', 'array', 'min:2', 'max:4'],
            'poll_options.*' => ['required', 'string', 'min:1', 'max:40'],
            'poll_duration' => ['required_with:poll_options', 'integer', 'min:1', 'max:7'],
            'thread_polls' => ['sometimes', 'array', 'max:9'],
        ];
    }
}
