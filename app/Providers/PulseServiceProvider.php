<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

final class PulseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', fn (User $user): bool => $user->email === 'enunomaduro@gmail.com' || $user->email === 'mrpunyapal@gmail.com');

        Pulse::user(fn (User $user): array => [
            'name' => $user->name,
            'extra' => $user->email,
            'avatar' => $user->avatar_url,
        ]);
    }
}
