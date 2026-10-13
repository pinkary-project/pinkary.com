<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ChannelRequest extends FormRequest
{
    /** The channel list is readable by everyone. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['q' => ['sometimes', 'string', 'max:50']];
    }

    /** The term to match channel names against. */
    public function search(): string
    {
        return $this->string('q')->toString();
    }
}
