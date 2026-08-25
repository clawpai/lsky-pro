<?php

namespace App\Filament\Resources\StorageResource\Pages;

use App\Filament\Resources\StorageResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Storage;
use App\StorageProvider;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Str;

class ListStorages extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = StorageResource::class;

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
            $this->getProviderColumn(),
            $this->getIntroColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 储存名称
     * @return Column
     */
    protected function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/storage.columns.name'))
            ->sortable()
            ->searchable();
    }

    /**
     * 驱动类型
     * @return Column
     */
    protected function getProviderColumn(): Column
    {
        return TextColumn::make('provider')
            ->label(__('admin/storage.columns.provider'))
            ->formatStateUsing(fn(StorageProvider $state) => __('admin.storage_providers.' . Str::snake($state->name)))
            ->badge();
    }

    /**
     * 简介
     * @return Column
     */
    protected function getIntroColumn(): Column
    {
        return TextColumn::make('intro')
            ->label(__('admin/storage.columns.intro'))
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
            ->label(__('admin/storage.columns.created_at'))
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
        return DeleteAction::make()
            ->modalDescription(__('admin/storage.actions.delete.modal_description'))
            ->after(fn(Storage $record) => app('files')->delete(public_path($record->prefix)));
    }

    /**
     * 获取批量操作
     * @return array
     */
    protected function getBulkActions(): array
    {
        return [];
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
