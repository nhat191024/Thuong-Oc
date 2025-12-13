<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    public string $app_name;
    public ?string $app_logo = null;
    public ?string $app_favicon = null;

    public static function group(): string
    {
        return 'app';
    }
}
