<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\OrderStatus;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Support\Htmlable;

/**
 * 数量折线图(新订单数、新用户数)
 */
class NumberChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('admin/dashboard.number_chart.heading');
    }

    protected function getData(): array
    {
        $orderTrend = Trend::query(Order::query()->where('status', OrderStatus::Paid))
            ->between(
                start: now()->subDays(6)->startOfDay(),
                end: now()->endOfDay(),
            )
            ->perDay()
            ->count();

        $userTrend = Trend::model(User::class)
            ->between(
                start: now()->subDays(6)->startOfDay(),
                end: now()->endOfDay(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => '新订单数',
                    'data' => $orderTrend->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                ],
                [
                    'label' => '新用户数',
                    'data' => $userTrend->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => 'rgb(106, 106, 217)',
                ],
            ],
            'labels' => $orderTrend->map(fn (TrendValue $value) => $value->date),
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
