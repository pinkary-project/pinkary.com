<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\GitHub;
use Illuminate\Support\ServiceProvider;

final class GitHubServiceProvider extends ServiceProvider
{
    /**
     * Register the GitHub service.
     */
    public function register(): void
    {
        $this->app->singleton(GitHub::class, fn (): GitHub => new GitHub(config()->string('services.github.token')));
    }
}
