<?php

namespace App\Providers;

use Inertia\Inertia;

use App\Settings\AppSettings;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
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
        try {
            $settings = app(AppSettings::class);
            $settingsArray = $this->getSettingsArray($settings);
        } catch (\Exception $e) {
            $settingsArray = [
                'app_name'    => config('app.name'),
                'app_logo'    => '/logo.svg',
                'app_favicon' => '/favicon.ico',
            ];
        }

        Inertia::share([
            'app_settings'  => fn() => $settingsArray
        ]);

        View::share('settings', $settingsArray);
    }

    private function getSettingsArray(AppSettings $settings)
    {
        try {
            return [
                'app_name'    => $settings->app_name,
                'app_logo'    => secure_asset($settings->app_logo),
                'app_favicon' => secure_asset($settings->app_favicon),
            ];
        } catch (\Exception $e) {
            return [
                'app_name'    => config('app.name'),
                'app_logo'    => null,
                'app_favicon' => null,
            ];
        }
    }
}
