<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Rules\MobileTurnstile;
use App\Rules\NoBlankCharacters;
use App\Services\MobileCaptcha;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreCommentRequest extends FormRequest
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
    public function rules(MobileCaptcha $captcha): array
    {
        $rules = ['content' => ['required', 'string', 'min:1', 'max:1000', new NoBlankCharacters]];
        /** @var User|null $user */
        $user = $this->user();

        if ($captcha->required('comment', $user)) {
            $state = $this->input('captcha_state');
            $rules['captcha_state'] = ['bail', 'required', 'uuid'];
            $rules['cf-turnstile-response'] = ['bail', 'required', 'string', 'max:2048', new MobileTurnstile('comment', is_string($state) ? $state : '')];
        }

        return $rules;
    }
}
