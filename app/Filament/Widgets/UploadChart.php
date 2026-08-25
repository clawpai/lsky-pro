<?php

namespace App\Filament\Widgets;

use App\Models\Photo;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Support\Htmlable;

/**
 * 图片上传折线图
 */
class UploadChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('admin/dashboard.upload_chart.heading');
    }

    protected function getData(): array
    {
        $trend = Trend::query(Photo::query()->whereNotNull('user_id'))
            ->between(
                start: now()->subDays(6)->startOfDay(),
                end: now()->endOfDay(),
            )
            ->perDay()
            ->count();

        $trendByGuest = Trend::query(Photo::query()->whereNull('user_id'))
            ->between(
                start: now()->subDays(6)->startOfDay(),
                end: now()->endOfDay(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => '用户上传',
                    'data' => $trend->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => 'rgb(106, 217, 158)',
                ],
                [
                    'label' => '游客上传',
                    'data' => $trendByGuest->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => 'rgb(238, 197, 31)',
                ],
            ],
            'labels' => $trend->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                    'ticks' => ['stepSize' => 1]
                ],
            ],
        ];
    }
}
