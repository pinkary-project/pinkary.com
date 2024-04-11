<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Changelog;
use Illuminate\Support\ServiceProvider;

final class ChangelogServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(Changelog::class, fn (): Changelog => new Changelog());
    }
}
