<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Requests;

use App\Models\User;
use App\Rules\NoBlankCharacters;
use App\Rules\Username;
use App\Rules\ValidTimezone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

final class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        $user = $this->user();
        assert($user instanceof User);

        return [
            'name' => ['required', 'string', 'max:255', new NoBlankCharacters],
            'username' => [
                'required', 'string', 'min:4', 'max:50', Rule::unique(User::class)->ignore($user->id),
                new Username($user),
            ],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id),
            ],
            'timezone' => ['required', 'string', 'max:255', new ValidTimezone],
            'mail_preference_time' => ['required', 'string', 'max:255', 'in:daily,weekly,never'],
            'bio' => ['nullable', 'string', 'max:255'],
            'settings' => ['array'],
            'settings.questions_preference' => ['required', 'string', 'in:anonymously,public'],
        ];
    }
}
