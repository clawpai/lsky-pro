<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Plan;
use App\PlanType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListPlans extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = PlanResource::class;

    public function table(Table $table): Table
    {
        $query = $table->getQuery()->withCount(['orders' => fn(Builder $builder) => $builder->whereNotNull('paid_at')]);
        return $table
            ->query($query)
            ->columns($this->getColumns())
            ->filters($this->getFilters())
            ->actions($this->getRowActions())
            ->bulkActions($this->getBulkActions())
            ->checkIfRecordIsSelectableUsing(fn(Plan $plan): bool => !$plan->is_up);
    }

    /**
     * 获取 table 列
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            $this->getTypeColumn(),
            $this->getNameColumn(),
            $this->getIntroColumn(),
            $this->getOrdersCountColumn(),
            $this->getIsUpColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 套餐类型
     * @return Column
     */
    protected function getTypeColumn(): Column
    {
        $types = collect(PlanType::cases())->map(fn (PlanType $type) => [
            $type->value => __("admin.plan_types.{$type->value}")
        ])->collapse()->toArray();

        return TextColumn::make('type')
            ->label(__('admin/plan.columns.type'))
            ->formatStateUsing(fn (PlanType $state): string => $types[$state->value]);
    }

    /**
     * 套餐名称
     * @return Column
     */
    protected function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/plan.columns.name'))
            ->searchable();
    }

    /**
     * 简介
     * @return Column
     */
    protected function getIntroColumn(): Column
    {
        return TextColumn::make('intro')
            ->label(__('admin/plan.columns.intro'))
            ->limit(60)
            ->searchable();
    }

    /**
     * 订单数量
     * @return Column
     */
    protected function getOrdersCountColumn(): Column
    {
        return TextColumn::make('orders_count')
            ->label(__('admin/plan.columns.orders_count'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 是否上架
     * @return Column
     */
    protected function getIsUpColumn(): Column
    {
        return ToggleColumn::make('is_up')
            ->label(__('admin/plan.columns.is_up'))
            ->alignCenter();
    }

    /**
     * 创建时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/plan.columns.created_at'))
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
        // TODO 打开页面
        return [
            $this->getEditRowAction(),
            $this->getDeleteRowAction(),
        ];
    }

    /**
     * 编辑操作
     * @return Action
     */
    protected function getEditRowAction(): Action
    {
        return EditAction::make();
    }

    /**
     * 删除操作
     * @return Action
     */
    protected function getDeleteRowAction(): Action
    {
        return DeleteAction::make()->visible(fn(Plan $plan): bool => !$plan->is_up);
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
            \Filament\Tables\Filters\SelectFilter::make('type')
                ->label(__('admin/plan.filters.type'))
                ->options([
                    PlanType::Vip->value => __('admin.plan_types.vip'),
                    PlanType::Storage->value => __('admin.plan_types.storage'),
                ])
                ->placeholder(__('admin.common.filters.all')),
            $this->getStatusFilter('is_up', 'admin/plan.filters.is_up'),
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
