<?php

namespace App\Filament\Widgets;

use App\Facades\StatService;
use App\Settings\AppSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $appSettings = app(AppSettings::class);
        return [
            Stat::make(__('admin/dashboard.stat.photo_count'), Number::abbreviate(StatService::getPhotoCount()))->icon('heroicon-o-photo'),
            Stat::make(__('admin/dashboard.stat.album_count'), Number::abbreviate(StatService::getAlbumCount()))->icon('heroicon-o-tag'),
            Stat::make(__('admin/dashboard.stat.user_count'), Number::abbreviate(StatService::getUserCount()))->icon('heroicon-o-users'),
            Stat::make(__('admin/dashboard.stat.share_count'), Number::abbreviate(StatService::getShareCount()))->icon('heroicon-o-share'),

            Stat::make(__('admin/dashboard.stat.today_order_amount'), Number::currency(StatService::getTodayOrderAmount(), in: $appSettings->currency))->icon('heroicon-o-shopping-cart'),
            Stat::make(__('admin/dashboard.stat.yesterday_order_amount'), Number::currency(StatService::getYesterdayOrderAmount(), in: $appSettings->currency))->icon('heroicon-o-shopping-cart'),
            Stat::make(__('admin/dashboard.stat.week_order_amount'), Number::currency(StatService::getWeekOrderAmount(), in: $appSettings->currency))->icon('heroicon-o-shopping-cart'),
            Stat::make(__('admin/dashboard.stat.month_order_amount'), Number::currency(StatService::getMonthOrderAmount(), in: $appSettings->currency))->icon('heroicon-o-shopping-cart'),
        ];
    }
}
