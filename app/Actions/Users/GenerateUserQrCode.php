<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use App\Services\QrCode;
use Illuminate\Support\HtmlString;

final readonly class GenerateUserQrCode
{
    public function handle(User $user, bool $lightMode): HtmlString
    {
        return new QrCode($lightMode)->generate(
            route('profile.show', [
                'username' => $user->username,
            ])
        );
    }
}
