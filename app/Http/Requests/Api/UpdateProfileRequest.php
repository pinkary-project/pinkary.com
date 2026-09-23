<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\UserDefaultFeed;
use App\Enums\UserMailPreference;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use App\Rules\NoEmailAlias;
use App\Rules\UnauthorizedEmailProviders;
use App\Rules\Username;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends FormRequest
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
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => ['sometimes', 'string', 'max:255', new NoBlankCharacters],
            'username' => [
                'sometimes', 'string', 'min:4', 'max:50', Rule::unique(User::class)->ignore($user->id),
                new Username($user),
            ],
            'email' => [
                'sometimes', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id),
                new NoEmailAlias(),
                new UnauthorizedEmailProviders(),
            ],
            'bio' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mail_preference_time' => ['sometimes', Rule::enum(UserMailPreference::class)],
            'default_feed' => ['sometimes', Rule::enum(UserDefaultFeed::class)],
            'prefers_anonymous_questions' => ['sometimes', 'boolean'],
            'avatar' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'link_shape' => ['sometimes', 'string', 'in:rounded-none,rounded-lg,rounded-full'],
            'gradient' => [
                'sometimes', 'string', 'in:from-blue-500 to-purple-600,from-blue-500 to-teal-700,from-red-500 to-orange-600,from-purple-500 to-pink-500,from-indigo-500 to-lime-700,from-yellow-600 to-blue-600',
            ],
        ];
    }
}
