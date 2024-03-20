<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Recaptcha;
use Illuminate\Support\ServiceProvider;

final class RecaptchaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(Recaptcha::class, fn (): Recaptcha => new Recaptcha(
            config()->string('services.recaptcha.secret'),
        ));
    }
}
