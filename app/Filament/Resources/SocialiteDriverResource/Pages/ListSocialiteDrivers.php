<?php

namespace App\Filament\Resources\SocialiteDriverResource\Pages;

use App\Filament\Resources\SocialiteDriverResource;
use App\Filament\Traits\HasTableFilters;
use App\SocialiteProvider;
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

class ListSocialiteDrivers extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = SocialiteDriverResource::class;

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
            $this->getOptionProviderColumn(),
            $this->getIntroColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 驱动名称
     * @return Column
     */
    protected function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/socialite_driver.columns.name'))
            ->sortable()
            ->searchable();
    }

    /**
     * 登录驱动
     * @return Column
     */
    protected function getOptionProviderColumn(): Column
    {
        return TextColumn::make('options.provider')
            ->label(__('admin/socialite_driver.columns.options_provider'))
            ->formatStateUsing(fn(string $state) => __('admin.socialite_providers.' . SocialiteProvider::tryFrom($state)->value))
            ->badge();
    }

    /**
     * 简介
     * @return Column
     */
    protected function getIntroColumn(): Column
    {
        return TextColumn::make('intro')
            ->label(__('admin/socialite_driver.columns.intro'))
            ->limit(60)
            ->searchable();
    }

    /**
     * 创建时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/socialite_driver.columns.created_at'))
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
        return $this->getCommonFilters();
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getRefreshAction(),
            Actions\CreateAction::make(),
        ];
    }
}
