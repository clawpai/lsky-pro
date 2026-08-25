<?php

namespace App\Filament\Resources\FeedbackResource\Pages;

use App\FeedbackType;
use App\Filament\Resources\FeedbackResource;
use App\Filament\Traits\HasTableFilters;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListFeedback extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = FeedbackResource::class;

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
            $this->getTypeColumn(),
            $this->getTitleColumn(),
            $this->getContentColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 类型
     * @return Column
     */
    protected function getTypeColumn(): Column
    {
        return TextColumn::make('type')
            ->label(__('admin/feedback.columns.type'))
            ->formatStateUsing(fn(FeedbackType $state) => __("admin.feedback_types.{$state->value}"))
            ->badge();
    }

    /**
     * 标题
     * @return Column
     */
    protected function getTitleColumn(): Column
    {
        return TextColumn::make('title')
            ->label(__('admin/feedback.columns.title'));
    }

    /**
     * 名称
     * @return Column
     */
    protected function getContentColumn(): Column
    {
        return TextColumn::make('content')
            ->label(__('admin/feedback.columns.content'))
            ->wrap()
            ->limit(40);
    }

    /**
     * 反馈时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/feedback.columns.created_at'))
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
            $this->getViewRowAction(),
            $this->getDeleteRowAction(),
        ];
    }

    /**
     * 显示详情操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return ViewAction::make()->slideOver()->modalWidth(MaxWidth::Medium);
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
     * 删除批量操作
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
                ->label(__('admin/feedback.filters.type'))
                ->options([
                    FeedbackType::General->value => __('admin.feedback_types.general'),
                    FeedbackType::Dmca->value => __('admin.feedback_types.dmca'),
                ])
                ->placeholder(__('admin.common.filters.all')),
            ...$this->getCommonFilters(),
        ];
    }

    /**
     * 获取表头操作
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getRefreshAction(),
        ];
    }
}
