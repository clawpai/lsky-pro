<?php

namespace App\Filament\Traits;

use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;

trait HasTableFilters
{
    /**
     * 获取通用过滤器
     * @return array
     */
    protected function getCommonFilters(): array
    {
        return [
            Filter::make('created_at')
                ->label(__('admin.common.filters.created_at'))
                ->form([
                    DatePicker::make('created_from')
                        ->label(__('admin.common.filters.created_from'))
                        ->placeholder(__('admin.common.filters.created_from_placeholder')),
                    DatePicker::make('created_until')
                        ->label(__('admin.common.filters.created_until'))
                        ->placeholder(__('admin.common.filters.created_until_placeholder')),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['created_from'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['created_until'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
                }),
        ];
    }

    /**
     * 获取刷新操作
     * @return Actions\Action
     */
    protected function getRefreshAction(): Actions\Action
    {
        return Actions\Action::make('refresh')
            ->icon('heroicon-o-arrow-path')
            ->hiddenLabel()
            ->action(function () {
                $this->resetTable();
            });
    }

    /**
     * 获取状态过滤器
     * @param string $field
     * @param string $labelKey
     * @param array $statusMapping
     * @return TernaryFilter
     */
    protected function getStatusFilter(string $field, string $labelKey, array $statusMapping = []): TernaryFilter
    {
        return TernaryFilter::make($field)
            ->label(__($labelKey))
            ->nullable()
            ->placeholder(__('admin.common.filters.all'))
            ->trueLabel(__($labelKey . '_true'))
            ->falseLabel(__($labelKey . '_false'))
            ->queries(
                true: fn($query) => $query->where($field, $statusMapping['true'] ?? true),
                false: fn($query) => $query->where($field, $statusMapping['false'] ?? false),
            );
    }
}