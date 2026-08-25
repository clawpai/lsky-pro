<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AdminSettings extends Settings
{
    /** @var bool|null 是否显示顶部导航 */
    public ?bool $top_navigation = null;

    /** @var null|string 主题色 */
    public ?string $primary_color = null;

    /** @var null|bool 是否启用暗黑模式 */
    public ?bool $dark_mode = null;

    /** @var null|string 默认主题模式 */
    public ?string $default_theme_mode = null;

    public static function group(): string
    {
        return 'admin';
    }
}