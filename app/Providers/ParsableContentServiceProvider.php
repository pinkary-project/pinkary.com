<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ParsableContent;
use Illuminate\Support\ServiceProvider;

final class ParsableContentServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void {}

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->app->terminating(static function (): void {
            ParsableContent::flush(all: true);
        });
    }
}
