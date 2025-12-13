<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.app_name', 'Sự Kiện Tốt');
        $this->migrator->add('app.app_logo', 'images/logo.svg');
        $this->migrator->add('app.app_favicon', 'images/favicon.ico');
    }
};
