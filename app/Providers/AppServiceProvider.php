<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();
        $this->configurePasswordValidation();

        Route::bind('username', function (string $username): User {
            return User::where(DB::raw('LOWER(username)'), mb_strtolower($username))->firstOrFail();
        });
    }

    /**
     * Configure the models.
     */
    private function configureModels(): void
    {
        Model::preventsLazyLoading();
        // Model::shouldBeStrict();
        Model::unguard();
    }

    /**
     * Configure the password validation rules.
     */
    private function configurePasswordValidation(): void
    {
        Password::defaults(fn () => app()->isProduction() ? Password::min(8)->uncompromised() : null);
    }
}
