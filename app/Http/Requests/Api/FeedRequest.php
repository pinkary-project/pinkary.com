<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FeedRequest extends FormRequest
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

    /**
     * The tab the user's own home screen opens on.
     *
     * The web redirects `/` to the user's default_feed, and the column
     * defaults to "following". Hardcoding "recent" gave the same person a
     * different home feed on the app than on the site.
     */
    private function defaultTab(): string
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->default_feed->value ?? 'recent';
    }
}
