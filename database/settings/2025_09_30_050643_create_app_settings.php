<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.app_name', 'Thương Ốc');
        $this->migrator->add('app.app_logo', 'logo.svg');
        $this->migrator->add('app.app_favicon', 'favicon.ico');
    }
};
