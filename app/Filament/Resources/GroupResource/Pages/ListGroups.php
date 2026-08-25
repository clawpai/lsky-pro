<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Group;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListGroups extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = GroupResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getColumns())
            ->filters($this->getFilters())
            ->actions($this->getRowActions())
            ->bulkActions($this->getBulkActions())
            ->checkIfRecordIsSelectableUsing(fn(Group $group): bool => $this->checkRowIsCanDelete($group));
    }

    /**
     * 获取所有表格列
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            $this->getNameColumn(),
            $this->getIntroColumn(),
            $this->getStorageCountColumn(),
            $this->getIsDefaultColumn(),
            $this->getIsGuestColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 角色组名称
     * @return Column
     */
    protected function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/group.columns.name'))
            ->sortable()
            ->searchable();
    }

    /**
     * 简介
     * @return Column
     */
    protected function getIntroColumn(): Column
    {
        return TextColumn::make('intro')
            ->label(__('admin/group.columns.intro'))
            ->limit(60)
            ->searchable();
    }

    /**
     * 储存驱动数量
     * @return Column
     */
    protected function getStorageCountColumn(): Column
    {
        return TextColumn::make('storages_count')
            ->label(__('admin/group.columns.storages_count'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 是否默认角色组
     * @return Column
     */
    protected function getIsDefaultColumn(): Column
    {
        return IconColumn::make('is_default')
            ->label(__('admin/group.columns.is_default'))
            ->alignCenter();
    }

    /**
     * 是否游客角色组
     * @return Column
     */
    protected function getIsGuestColumn(): Column
    {
        return IconColumn::make('is_guest')
            ->label(__('admin/group.columns.is_guest'))
            ->alignCenter();
    }

    /**
     * 创建时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/group.columns.created_at'))
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
            ->modalDescription(__('admin/group.actions.delete.modal_description'))
            ->disabled(fn(Group $record) => !$this->checkRowIsCanDelete($record));
    }

    /**
     * 判断行是否可以被删除
     * @param Group $group
     * @return bool
     */
    protected function checkRowIsCanDelete(Group $group): bool
    {
        return !($group->is_default || $group->is_guest);
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
        return DeleteBulkAction::make()
            ->modalDescription(__('admin/group.actions.delete.modal_description'));
    }

    /**
     * 获取过滤器
     * @return array
     */
    protected function getFilters(): array
    {
        return [
            $this->getStatusFilter('is_default', 'admin/group.filters.is_default'),
            $this->getStatusFilter('is_guest', 'admin/group.filters.is_guest'),
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
