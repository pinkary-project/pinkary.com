<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

final readonly class CreateUser
{
    /**
     * Create and register a new user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): User
    {
        $user = User::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'username' => $attributes['username'],
            'password' => Hash::make((string) $attributes['password']),
        ]);

        event(new Registered($user));

        UpdateUserAvatar::dispatchFor($user);

        return $user;
    }
}
