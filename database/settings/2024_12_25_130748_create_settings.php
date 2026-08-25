<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('app', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Lsky Pro+ - 2x.nz特供离线版');
            $blueprint->add('url', 'http://localhost');
            $blueprint->addEncrypted('license_key');
            $blueprint->add('timezone', 'Asia/Shanghai');
            $blueprint->add('locale', 'zh_CN');
            $blueprint->add('currency', 'CNY');
            $blueprint->add('icp_no', '');
            $blueprint->add('ip_gain_method', 'auto');
            $blueprint->add('enable_registration', true);
            $blueprint->add('guest_upload', true);
            $blueprint->add('user_email_verify', true);
            $blueprint->add('user_phone_verify', false);
            $blueprint->add('mail_from_address', 'hello@example.com');
            $blueprint->add('mail_from_name', 'Lsky Pro+ - 2x.nz特供离线版');
        });

        $this->migrator->inGroup('site', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('theme', 'default');
            $blueprint->add('title', 'Lsky Pro+ - 2x.nz特供离线版');
            $blueprint->add('subtitle', '您的云上相册。');
            $blueprint->add('homepage_title', 'Lsky Pro+ - 2x.nz特供离线版');
            $blueprint->add('homepage_description', 'Your photo album on the cloud.');
            $blueprint->add('notice', '');
            $blueprint->add('homepage_background_image_url', '');
            $blueprint->add('homepage_background_images', []);
            $blueprint->add('auth_page_background_image_url', '');
            $blueprint->add('auth_page_background_images', []);
        });

        $this->migrator->inGroup('admin', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('top_navigation', false);
            $blueprint->add('primary_color', 'sky');
            $blueprint->add('dark_mode', false);
            $blueprint->add('default_theme_mode', 'system');
        });

        $this->migrator->inGroup('user', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('initial_capacity', 5120);
        });
    }
};
