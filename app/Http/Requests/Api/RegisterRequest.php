<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Rules\MobileTurnstile;
use App\Rules\NoEmailAlias;
use App\Rules\NotBlockedAccount;
use App\Rules\UnauthorizedEmailProviders;
use App\Rules\Username;
use App\Services\MobileCaptcha;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

final class RegisterRequest extends FormRequest
{
    /** Authorize this request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(MobileCaptcha $captcha): array
    {
        $rules = ['name' => ['required', 'string', 'max:255'], 'username' => ['required', 'string', 'min:4', 'max:50', 'unique:'.User::class, new Username], 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new NoEmailAlias, new UnauthorizedEmailProviders, new NotBlockedAccount], 'password' => ['required', 'confirmed', Rules\Password::defaults()], 'terms' => ['required', 'accepted']];

        if ($captcha->required('register', null)) {
            $state = $this->input('captcha_state');
            $rules['captcha_state'] = ['bail', 'required', 'uuid'];
            $rules['cf-turnstile-response'] = ['bail', 'required', 'string', 'max:2048', new MobileTurnstile('register', is_string($state) ? $state : '')];
        }

        return $rules;
    }

    /**
     * @return array{name: string, username: string, email: string, password: string}
     */
    public function attributes(): array
    {
        return [
            'name' => $this->string('name')->toString(),
            'username' => $this->string('username')->toString(),
            'email' => $this->string('email')->toString(),
            'password' => $this->string('password')->toString(),
        ];
    }
}
