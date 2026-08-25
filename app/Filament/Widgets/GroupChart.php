<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * 用户所在角色组分布饼状图
 */
class GroupChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('admin/dashboard.group_chart.heading');
    }

    protected function getData(): array
    {
        $trend = Group::withCount([
            'userGroups as user_count' => function (Builder $query) {
                $query->select(DB::raw('COUNT(DISTINCT user_id)'))
                    ->where(function ($q) {
                        $q->whereNull('expired_at')
                            ->orWhere('expired_at', '>', now());
                    });
            }
        ])->get()->pluck('user_count', 'name');

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
