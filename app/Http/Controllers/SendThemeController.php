<?php

namespace App\Http\Controllers;

use App\Settings\AppSettings;
use App\Settings\SiteSettings;
use Illuminate\View\View;

/**
 * 输出主题文件
 */
class SendThemeController extends Controller
{
    public function __invoke(): View
    {
        if (! app(AppSettings::class)->enable_site) {
            abort(404, '系统已关闭站点');
        }

        $theme = app(SiteSettings::class)->theme;

        return \view("themes/{$theme}/index");
    }
}
