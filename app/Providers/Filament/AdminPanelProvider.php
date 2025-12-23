<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;

use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;

use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Illuminate\Support\Facades\Gate;

use Hugomyb\FilamentErrorMailer\FilamentErrorMailerPlugin;

use App\Settings\AppSettings;
use App\Filament\Pages\Login;
use App\Enums\FilamentNavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        try {
            $settings = app(AppSettings::class);
            $favicon = asset($settings->app_favicon);
            $appName = $settings->app_name;
        } catch (\Exception $e) {
            $favicon = asset('favicon.ico');
            $appName = 'Thương Ốc';
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Red,
            ])
            ->brandName($appName)
            ->favicon($favicon)
            ->maxContentWidth(Width::Full)
            ->navigationGroups(FilamentNavigationGroup::class)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')

            ->viteTheme('resources/css/filament/admin/theme.css')

            ->pages([
                Dashboard::class,
            ])

            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])

            ->plugins([
                FilamentErrorMailerPlugin::make(),
            ])

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
}
