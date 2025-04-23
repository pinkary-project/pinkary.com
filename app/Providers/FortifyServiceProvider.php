<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
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

        Fortify::authenticateUsing(function (Request $request) {

            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $accounts = session()->get('accounts', []);
                $accounts = is_array($accounts) ? $accounts : [];

                if (auth()->check()) {
                    if ($user->id === auth()->id()) {
                        return $user;
                    }

                    $accounts = array_unique([
                        ...$accounts,
                        auth()->id(),
                    ]);
                }

                $accounts = array_unique([
                    ...$accounts,
                    $user->id,
                ]);

                session()->put('accounts', $accounts);

                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $username = $request->string(Fortify::username())->toString();
            $throttleKey = Str::transliterate(Str::lower($username).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
