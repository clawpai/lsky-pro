<?php

namespace App\Filament\Resources\AlbumResource\Pages;

use App;
use App\Filament\Resources\AlbumResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Album;
use Filament\AvatarProviders\UiAvatarsProvider;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListAlbums extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = AlbumResource::class;

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
            $this->getNameColumn(),
            $this->getIntroColumn(),
            $this->getIsPublicColumn(),
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
            ->label(__('admin/album.columns.user.name'))
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
            ->label(__('admin/album.columns.user.email'))
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
            ->label(__('admin/album.columns.user.phone'))
            ->searchable(isIndividual: true)
            ->copyable();
    }

    /**
     * 相册名称
     * @return Column
     */
    protected function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/album.columns.name'))
            ->wrap()
            ->searchable(isIndividual: true);
    }

    /**
     * 相册简介
     * @return Column
     */
    protected function getIntroColumn(): Column
    {
        return TextColumn::make('intro')
            ->label(__('admin/album.columns.intro'))
            ->wrap()
            ->searchable(isIndividual: true);
    }

    /**
     * 是否公开
     * @return Column
     */
    protected function getIsPublicColumn(): Column
    {
        return ToggleColumn::make('is_public')
            ->label(__('admin/album.columns.is_public'))
            ->alignCenter();
    }

    /**
     * 创建时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/album.columns.created_at'))
            ->dateTime()
            ->alignCenter()
            ->sortable();
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
     * 详情操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return ViewAction::make()->modalWidth(MaxWidth::Medium)->slideOver();
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
            $this->getStatusFilter('is_public', 'admin/album.filters.is_public'),
            SelectFilter::make('user_id')
                ->label(__('admin/album.filters.user'))
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

    /**
     * 头像
     * @return Column
     */
    protected function getUserAvatarColumn(): Column
    {
        return ImageColumn::make('user.avatar')
            ->label(__('admin/album.columns.user.avatar'))
            ->defaultImageUrl(fn(Album $album): ?string => $album->user ? App::make(UiAvatarsProvider::class)->get($album->user) : null)
            ->circular();
    }
}
