<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserMailPreference;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use App\Rules\UnauthorizedEmailProviders;
use App\Rules\Username;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

final class UserUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidatorAwareRule|ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        $user = type($this->user())->as(User::class);

        return [
            'name' => ['required', 'string', 'max:255', new NoBlankCharacters],
            'username' => [
                'required', 'string', 'min:4', 'max:50', Rule::unique(User::class)->ignore($user->id),
                new Username($user),
            ],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id),
                new UnauthorizedEmailProviders(),
            ],
            'mail_preference_time' => [Rule::enum(UserMailPreference::class)],
            'bio' => ['nullable', 'string', 'max:255'],
            'prefers_anonymous_questions' => ['required', 'boolean'],
        ];
    }
}
