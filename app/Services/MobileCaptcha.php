<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Rules\MobileTurnstile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

final class MobileCaptcha
{
    public function required(string $action, ?User $user): bool
    {
        if ($action === 'register') {
            return app()->environment(['production', 'testing']);
        }

        return app()->isProduction() && $user instanceof User && $user->followers()->doesntExist();
    }

    /** @return array<string, list<string|ValidationRule>> */
    public function rules(string $action, Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $this->required($action, $user)) {
            return [];
        }

        $state = $request->input('captcha_state');

        return [
            'captcha_state' => ['bail', 'required', 'uuid'],
            'cf-turnstile-response' => [
                'bail', 'required', 'string', 'max:2048',
                new MobileTurnstile($action, is_string($state) ? $state : ''),
            ],
        ];
    }
}
