<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

final class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::ignoreRoutes();
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));

        RateLimiter::for('login', function (Request $request) {
            $username = type($request->input(Fortify::username()))->asString();
            $throttleKey = Str::transliterate(Str::lower($username).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where(fn (Builder $q) => $q->where('email', $request->email)->orWhere('username', $request->email))
                ->whereNotNull('email_verified_at')
                ->first();

            if ($user &&
                Hash::check(type($request->password)->asString(), $user->password)) {
                return $user;
            }
        });
    }
}
