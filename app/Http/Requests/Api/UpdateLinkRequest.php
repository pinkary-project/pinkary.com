<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateLinkRequest extends FormRequest
{
    /** The link's policy authorizes the update. */
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
            'description' => ['sometimes', 'required', 'string', 'max:100'],
            'url' => ['sometimes', 'required', 'string', 'max:100', 'url', 'starts_with:https'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }

    /** Assume https for a URL that arrived without a scheme. */
    protected function prepareForValidation(): void
    {
        if ($this->has('url') && is_string($this->input('url'))) {
            $url = $this->input('url');
            if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                $this->merge(['url' => "https://{$url}"]);
            }
        }
    }
}
