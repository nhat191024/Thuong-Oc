<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.announcement_is_active', false);
        $this->migrator->add('app.announcement_title', '');
        $this->migrator->add('app.announcement_content', '');
    }
};
