<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        $count = $user->links()->count();
        $max = $user->is_verified ? 20 : 10;

        if ($count >= $max) {
            abort(422, "You can only have {$max} links at a time.");
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:100', 'url', 'starts_with:https'],
        ];
    }

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
