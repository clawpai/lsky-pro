<?php

namespace App\Filament\Resources\ShareResource\Pages;

use App\Filament\Resources\ShareResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Share;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class ListShares extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = ShareResource::class;

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
            ColumnGroup::make(__('admin.user_info'), [
                $this->getUserNameColumn(),
                $this->getUserEmailColumn(),
                $this->getUserPhoneColumn(),
            ]),
            $this->getViewNumColumn(),
            $this->getPasswordColumn(),
            $this->getExpiredAtColumn(),
            $this->getContentColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 用户名
     * @return Column
     */
    protected function getUserNameColumn(): Column
    {
        return TextColumn::make('user.name')
            ->label(__('admin/share.columns.user.name'))
            ->searchable(isIndividual: true)
            ->copyable();
    }

    /**
     * 邮箱
     * @return Column
     */
    protected function getUserEmailColumn(): Column
    {
        return TextColumn::make('user.email')
            ->label(__('admin/share.columns.user.email'))
            ->searchable(isIndividual: true)
            ->copyable();
    }

    /**
     * 手机号
     * @return Column
     */
    protected function getUserPhoneColumn(): Column
    {
        return TextColumn::make('user.phone')
            ->label(__('admin/share.columns.user.phone'))
            ->searchable(isIndividual: true)
            ->copyable();
    }

    /**
     * 浏览量
     * @return Column
     */
    protected function getViewNumColumn(): Column
    {
        return TextColumn::make('view_count')
            ->label(__('admin/share.columns.view_count'))
            ->sortable();
    }

    /**
     * 提取码
     * @return Column
     */
    protected function getPasswordColumn(): Column
    {
        return TextColumn::make('password')
            ->label(__('admin/share.columns.password'))
            ->copyable();
    }

    /**
     * 到期时间
     * @return Column
     */
    protected function getExpiredAtColumn(): Column
    {
        return TextColumn::make('expired_at')
            ->label(__('admin/share.columns.expired_at'))
            ->sortable()
            ->alignCenter()
            ->dateTime()
            ->color(fn(Carbon $state): string => $state->isPast() ? 'danger' : '');
    }

    /**
     * 分享内容
     * @return Column
     */
    protected function getContentColumn(): Column
    {
        return TextColumn::make('content')
            ->label(__('admin/share.columns.content'))
            ->wrap()
            ->searchable();
    }

    /**
     * 分享时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/share.columns.created_at'))
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
            $this->getViewRowAction(),
            $this->getUrlRowAction(),
            $this->getDeleteRowAction(),
        ];
    }

    /**
     * 详情操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return ViewAction::make()->modalWidth(MaxWidth::Large)->slideOver();
    }

    /**
     * 打开 url 操作
     * @return Action
     */
    protected function getUrlRowAction(): Action
    {
        return Action::make(__('admin/share.actions.url.label'))
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->url(fn(Share $share): string => $share->url, true);
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
            \Filament\Tables\Filters\SelectFilter::make('user_id')
                ->label(__('admin/share.filters.user'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
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
