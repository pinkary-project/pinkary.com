<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class CitadelPanelProvider extends PanelProvider
{
    /**
     * Configure the Filament admin panel.
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('citadel')
            ->path('citadel')
            ->spa()
            ->colors(['primary' => Color::Pink])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->paginationPageOptions([10, 25, 50]);
        });

        $this->addFavicon();
    }

    /**
     * Add favicon to the admin panel.
     */
    private function addFavicon(): void
    {
        FilamentView::registerRenderHook(
            name: PanelsRenderHook::HEAD_END,
            hook: fn () =>  <<<EOT
            <!-- Favicon -->
            <link
                rel="shortcut icon"
                href="https://pinkary.com/img/ico.svg"
                type="image/x-icon"
            />
            EOT,
        );
    }
}
