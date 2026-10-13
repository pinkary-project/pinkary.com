<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;
use App\Models\User;

final readonly class CreateLink
{
    /**
     * Create a link for the user.
     *
     * @param  array<string, string>  $attributes
     */
    public function handle(User $user, array $attributes): Link
    {
        return $user->links()->create($attributes);
    }
}
