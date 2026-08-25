<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Traits\HasTableFilters;
use App\PageType;
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
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ListPages extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = PageResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getColumns())
            ->filters($this->getFilters())
            ->actions($this->getRowActions())
            ->bulkActions($this->getBulkActions())
;
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
            $this->getViewNumColumn(),
            $this->getSortColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 名称
     * @return Column
     */
    public function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/page.columns.name'));
    }

    /**
     * 类型
     * @return Column
     */
    public function getTypeColumn(): Column
    {
        return TextColumn::make('type')
            ->label(__('admin/page.columns.type'))
            ->formatStateUsing(fn(PageType $state): string => __("admin.page_types.{$state->value}"))
            ->badge();
    }

    /**
     * 阅读量
     * @return Column
     */
    public function getViewNumColumn(): Column
    {
        return TextColumn::make('view_count')
            ->label(__('admin/page.columns.view_count'))
            ->formatStateUsing(fn($state): string => Number::abbreviate($state))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 排序值
     * @return Column
     */
    public function getSortColumn(): Column
    {
        return TextColumn::make('sort')
            ->label(__('admin/page.columns.sort'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 创建时间
     * @return Column
     */
    public function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/page.columns.created_at'))
            ->alignCenter()
            ->sortable()
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
            \Filament\Tables\Filters\SelectFilter::make('type')
                ->label(__('admin/page.filters.type'))
                ->options([
                    PageType::Internal->value => __('admin.page_types.internal'),
                    PageType::External->value => __('admin.page_types.external'),
                ])
                ->placeholder(__('admin.common.filters.all')),
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
