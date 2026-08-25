<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('app', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('enable_site', true);
            $blueprint->add('enable_stat_api', false);
            $blueprint->add('enable_stat_api_key', '');
        });

        $this->migrator->inGroup('site', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('custom_css', '');
            $blueprint->add('custom_js', '');
        });
    }
};
