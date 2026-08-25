<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

/**
 * 图片所在储存分布饼状图
 */
class StorageChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('admin/dashboard.storage_chart.heading');
    }

    protected function getData(): array
    {
        $trend = DB::table('storages')
            ->leftJoin('photos', 'storages.id', '=', 'photos.storage_id')
            ->select('storages.*', DB::raw('COUNT(photos.id) as photo_count'))
            ->groupBy('storages.id')
            ->get()
            ->pluck('photo_count', 'name');

        return [
            'datasets' => [
                [
                    'data' => $trend->values(),
                    'backgroundColor' => $trend->keys()->map(fn($key): string => $this->generateColor($key))->toArray(),
                ],
            ],
            'labels' => $trend->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
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

    protected function generateColor(string $key): string
    {
        $hash = md5($key); // 生成组名的 MD5 哈希
        return 'rgb(' . hexdec(substr($hash, 0, 2)) . ', ' . hexdec(substr($hash, 2, 2)) . ', ' . hexdec(substr($hash, 4, 2)) . ')';
    }
}
