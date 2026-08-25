<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\CouponType;
use App\Filament\Resources\CouponResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Coupon;
use App\OrderStatus;
use App\Settings\AppSettings;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class ListCoupons extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = CouponResource::class;

    public function table(Table $table): Table
    {
        $query = $table->getQuery()->withCount(['orders' => fn(Builder $builder) => $builder->where('status', '<>', OrderStatus::Cancelled)]);

        return $table
            ->query($query)
            ->columns($this->getColumns())
            ->filters($this->getFilters())
            ->actions($this->getRowActions())
            ->bulkActions($this->getBulkActions());
    }

    /**
     * 获取 table 列
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            $this->getNameColumn(),
            $this->getTypeColumn(),
            $this->getCodeColumn(),
            $this->getValueColumn(),
            $this->getUsageLimitColumn(),
            $this->getUsageCountColumn(),
            $this->getExpiredAtColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 名称
     * @return Column
     */
    protected function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/coupon.columns.name'))
            ->searchable();
    }

    /**
     * 类型
     * @return Column
     */
    protected function getTypeColumn(): Column
    {
        return TextColumn::make('type')
            ->label(__('admin/coupon.columns.type'))
            ->formatStateUsing(fn(CouponType $state): string => __("admin.coupon_types.{$state->value}"))
            ->badge();
    }

    /**
     * 券码
     * @return Column
     */
    protected function getCodeColumn(): Column
    {
        return TextColumn::make('code')
            ->label(__('admin/coupon.columns.code'))
            ->searchable();
    }

    /**
     * 值
     * @return Column
     */
    protected function getValueColumn(): Column
    {
        return TextColumn::make('value')
            ->label(__('admin/coupon.columns.value'))
            ->formatStateUsing(fn(Coupon $record): string => $record->type === CouponType::Percent ? Number::percentage($record->value * 100) : Number::currency($record->value, in: app(AppSettings::class)->currency))
            ->sortable();
    }

    /**
     * 可使用次数
     * @return Column
     */
    protected function getUsageLimitColumn(): Column
    {
        return TextColumn::make('usage_limit')
            ->label(__('admin/coupon.columns.usage_limit'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 已使用次数
     * @return Column
     */
    protected function getUsageCountColumn(): Column
    {
        return TextColumn::make('orders_count')
            ->label(__('admin/coupon.columns.usage_count'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 到期时间
     * @return Column
     */
    protected function getExpiredAtColumn(): Column
    {
        return TextColumn::make('expired_at')
            ->label(__('admin/coupon.columns.expired_at'))
            ->sortable()
            ->alignCenter()
            ->dateTime()
            ->color(fn(Carbon $state): string => $state->isPast() ? 'danger' : '');
    }

    /**
     * 创建时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/coupon.columns.created_at'))
            ->sortable()
            ->alignCenter()
            ->dateTime();
    }

    /**
     * 获取行操作列
     * @return array
     */
    protected function getRowActions(): array
    {
        return [
            $this->getDeleteRowAction(),
        ];
    }

    /**
     * 获取删除操作
     * @return Action
     */
    protected function getDeleteRowAction(): Action
    {
        return DeleteAction::make();
    }

    /**
     * 获取批量操作
     * @return array
     */
    protected function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                $this->getDeleteBulkAction(),
            ]),
        ];
    }

    /**
     * 批量删除操作
     * @return BulkAction
     */
    protected function getDeleteBulkAction(): BulkAction
    {
        return DeleteBulkAction::make();
    }

    /**
     * 获取过滤器
     * @return array
     */
    protected function getFilters(): array
    {
        return [
            SelectFilter::make('type')
                ->label(__('admin/coupon.filters.type'))
                ->options([
                    CouponType::Direct->value => __('admin.coupon_types.direct'),
                    CouponType::Percent->value => __('admin.coupon_types.percent'),
                ])
                ->placeholder(__('admin.common.filters.all')),
            Filter::make('expired_status')
                ->label(__('admin/coupon.filters.expired_status'))
                ->form([
                    \Filament\Forms\Components\Select::make('expired')
                        ->label(__('admin/coupon.filters.expired_status'))
                        ->options([
                            'unexpired' => __('admin/coupon.filters.unexpired'),
                            'expired' => __('admin/coupon.filters.expired'),
                        ])
                        ->placeholder(__('admin/coupon.filters.all_status')),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['expired'] === 'expired', fn(Builder $query) => $query->where('expired_at', '<', now()))
                        ->when($data['expired'] === 'unexpired', fn(Builder $query) => $query->where('expired_at', '>=', now()));
                }),
            ...$this->getCommonFilters(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getRefreshAction(),
            Actions\CreateAction::make(),
        ];
    }
}
