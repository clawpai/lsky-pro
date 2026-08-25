<?php

namespace App\Filament\Resources\NoticeResource\Pages;

use App\Filament\Resources\NoticeResource;
use App\Filament\Traits\HasTableFilters;
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

class ListNotices extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = NoticeResource::class;

    public function table(Table $table): Table
    {
        return $table
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
            $this->getTitleColumn(),
            $this->getSortColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 标题
     * @return Column
     */
    public function getTitleColumn(): Column
    {
        return TextColumn::make('title')
            ->label(__('admin/notice.columns.title'))
            ->limit();
    }

    /**
     * 排序值
     * @return Column
     */
    public function getSortColumn(): Column
    {
        return TextColumn::make('sort')
            ->label(__('admin/notice.columns.sort'))
            ->sortable();
    }

    /**
     * 发布时间
     * @return Column
     */
    public function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/notice.columns.created_at'))
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
