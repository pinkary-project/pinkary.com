<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FeedRequest extends FormRequest
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
        return ['tab' => ['sometimes', 'string', Rule::in(['recent', 'following', 'trending'])], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:50']];
    }

    /** The feed tab to return. */
    public function tab(): string
    {
        return $this->string('tab', $this->defaultTab())->toString();
    }

    /** How many posts to return. */
    public function perPage(): int
    {
        return $this->integer('per_page', 20);
    }

    /** Read the user's preferred home feed. */
    private function defaultTab(): string
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->default_feed->value ?? 'recent';
    }
}
