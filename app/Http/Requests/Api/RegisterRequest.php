<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Rules\NoEmailAlias;
use App\Rules\NotBlockedAccount;
use App\Rules\UnauthorizedEmailProviders;
use App\Rules\Username;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

final class RegisterRequest extends FormRequest
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
        return ['name' => ['required', 'string', 'max:255'], 'username' => ['required', 'string', 'min:4', 'max:50', 'unique:'.User::class, new Username], 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new NoEmailAlias, new UnauthorizedEmailProviders, new NotBlockedAccount], 'password' => ['required', 'confirmed', Rules\Password::defaults()], 'terms' => ['required', 'accepted']];
    }
}
