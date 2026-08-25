<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    /** @var null|string 主题 */
    public ?string $theme = null;

    /** @var null|string 网站标题 */
    public ?string $title = null;

    /** @var null|string 网站副标题 */
    public ?string $subtitle = null;

    /** @var null|string 首页标题 */
    public ?string $homepage_title = null;

    /** @var null|string 首页描述 */
    public ?string $homepage_description = null;

    /** @var null|string 全局公告 */
    public ?string $notice = null;

    /** @var null|string 首页背景图片 */
    public ?string $homepage_background_image_url = null;

    /** @var null|array<string> 首页背景图片列表 */
    public ?array $homepage_background_images = null;

    /** @var null|string 授权页背景图片 */
    public ?string $auth_page_background_image_url = null;

    /** @var null|array<string> 授权页背景图片列表 */
    public ?array $auth_page_background_images = null;

    /** @var string|null 自定义css */
    public ?string $custom_css = null;

    /** @var string|null 自定义js */
    public ?string $custom_js = null;

    public static function group(): string
    {
        return 'site';
    }
}