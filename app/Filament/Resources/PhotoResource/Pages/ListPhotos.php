<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App;
use App\Facades\PhotoService;
use App\Filament\Resources\PhotoResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Photo;
use App\PhotoStatus;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Number;

class ListPhotos extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = PhotoResource::class;

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
            $this->getThumbnailUrlColumn(),
            $this->getNameColumn(),
            $this->getMimetypeColumn(),
            $this->getSizeColumn(),
            $this->getIsPublicColumn(),
            $this->getStatusColumn(),
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
            ->label(__('admin/photo.columns.user.name'))
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
            ->label(__('admin/photo.columns.user.email'))
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
            ->label(__('admin/photo.columns.user.phone'))
            ->searchable(isIndividual: true)
            ->copyable();
    }

    /**
     * 缩略图
     * @return Column
     */
    protected function getThumbnailUrlColumn(): Column
    {
        return ImageColumn::make('thumbnail_url')
            ->label(__('admin/photo.columns.thumbnail_url'))
            ->alignCenter()
            ->checkFileExistence(false);
    }

    /**
     * 文件名称
     * @return Column
     */
    protected function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/photo.columns.name'))
            ->wrap()
            ->searchable(isIndividual: true);
    }

    /**
     * 文件类型
     * @return Column
     */
    protected function getMimetypeColumn(): Column
    {
        return TextColumn::make('mimetype')
            ->label(__('admin/photo.columns.mimetype'))
            ->searchable(isIndividual: true);
    }

    /**
     * 文件大小
     * @return Column
     */
    protected function getSizeColumn(): Column
    {
        return TextColumn::make('size')
            ->label(__('admin/photo.columns.size'))
            ->alignCenter()
            ->sortable()
            ->formatStateUsing(fn($state): string => $state ? Number::fileSize($state * 1024) : '0.00B');
    }

    /**
     * 是否公开
     * @return Column
     */
    protected function getIsPublicColumn(): Column
    {
        return ToggleColumn::make('is_public')
            ->label(__('admin/photo.columns.is_public'))
            ->alignCenter();
    }

    /**
     * 状态
     * @return Column
     */
    protected function getStatusColumn(): Column
    {
        return SelectColumn::make('status')
            ->label(__('admin/photo.columns.status'))
            ->options([
                PhotoStatus::Pending->value => __('admin/photo.status.pending'),
                PhotoStatus::Normal->value => __('admin/photo.status.normal'),
                PhotoStatus::Violation->value => __('admin/photo.status.violation'),
            ])
            ->selectablePlaceholder(false)
            ->afterStateUpdated(function (string $state, Photo $photo) {
                try {
                    $newStatus = PhotoStatus::from($state);
                    PhotoService::updatePhotoStatus($photo, $newStatus);
                    
                    Notification::make()
                        ->success()
                        ->title(__('admin/photo.actions.update_status.success'))
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(__('admin/photo.actions.update_status.error'))
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }

    /**
     * 上传时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/photo.columns.created_at'))
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
            ActionGroup::make([
                $this->getRenameRowAction(),
                $this->getRestoreViolationRowAction(),
                $this->getUrlRowAction(),
            ])
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
     * 重命名操作
     * @return Action
     */
    protected function getRenameRowAction(): Action
    {
        return Action::make(__('admin/photo.actions.rename.label'))
            ->icon('heroicon-o-pencil')
            ->modal()
            ->modalWidth(MaxWidth::Medium)
            ->form([
                TextInput::make('name')
                    ->label(__('admin/photo.actions.rename.form_fields.name'))
                    ->maxLength(200)
                    ->required(),
            ])
            ->fillForm(fn(Photo $photo): array => [
                'name' => $photo->name,
            ])
            ->action(function (array $data, Photo $photo) {
                $photo->name = data_get($data, 'name', $photo->name);
                $photo->save();
                Notification::make()->success()->title(__('admin/photo.actions.rename.success'))->send();
            });
    }

    /**
     * 恢复违规图片操作
     * @return Action
     */
    protected function getRestoreViolationRowAction(): Action
    {
        return Action::make(__('admin/photo.actions.restore_violation.label'))
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(__('admin/photo.actions.restore_violation.modal_description'))
            ->action(function (Photo $photo) {
                try {
                    PhotoService::restoreViolationPhoto($photo);
                    Notification::make()
                        ->success()
                        ->title(__('admin/photo.actions.restore_violation.success'))
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(__('admin/photo.actions.restore_violation.error'))
                        ->body($e->getMessage())
                        ->send();
                }
            })
            ->visible(fn(Photo $photo): bool => $photo->status === PhotoStatus::Violation);
    }

    /**
     * 打开图片操作
     * @return Action
     */
    protected function getUrlRowAction(): Action
    {
        return Action::make(__('admin/photo.actions.url.label'))
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->url(fn(Photo $photo): string => $photo->public_url, true);
    }

    /**
     * 获取批量操作
     * @return array
     */
    protected function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                $this->getRestoreViolationBulkAction(),
                $this->getDeleteBulkAction(),
            ]),
        ];
    }

    /**
     * 批量恢复违规图片操作
     * @return BulkAction
     */
    protected function getRestoreViolationBulkAction(): BulkAction
    {
        return BulkAction::make(__('admin/photo.actions.restore_violation.label'))
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(__('admin/photo.actions.restore_violation.bulk_modal_description'))
            ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                $violationPhotos = $records->filter(fn(Photo $photo) => $photo->status === PhotoStatus::Violation);
                
                if ($violationPhotos->isEmpty()) {
                    Notification::make()
                        ->warning()
                        ->title(__('admin/photo.actions.restore_violation.no_violation_selected'))
                        ->send();
                    return;
                }

                $successCount = 0;
                $errorCount = 0;

                foreach ($violationPhotos as $photo) {
                    try {
                        PhotoService::restoreViolationPhoto($photo);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                    }
                }

                if ($successCount > 0) {
                    Notification::make()
                        ->success()
                        ->title(__('admin/photo.actions.restore_violation.bulk_success', ['count' => $successCount]))
                        ->send();
                }

                if ($errorCount > 0) {
                    Notification::make()
                        ->danger()
                        ->title(__('admin/photo.actions.restore_violation.bulk_error', ['count' => $errorCount]))
                        ->send();
                }
            });
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
     * 使用游标分页
     * @param Builder $query
     * @return CursorPaginator
     */
    /*protected function paginateTableQuery(Builder $query): CursorPaginator
    {
        return $query->cursorPaginate(($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage());
    }*/

    /**
     * 获取过滤器
     * @return array
     */
    protected function getFilters(): array
    {
        return [
            \Filament\Tables\Filters\SelectFilter::make('status')
                ->label(__('admin/photo.filters.status'))
                ->options([
                    PhotoStatus::Pending->value => __('admin/photo.status.pending'),
                    PhotoStatus::Normal->value => __('admin/photo.status.normal'),
                    PhotoStatus::Violation->value => __('admin/photo.status.violation'),
                ])
                ->placeholder(__('admin.common.filters.all')),
            $this->getStatusFilter('is_public', 'admin/photo.filters.is_public'),
            \Filament\Tables\Filters\SelectFilter::make('user_id')
                ->label(__('admin/photo.filters.user'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->placeholder(__('admin.common.filters.all')),
            \Filament\Tables\Filters\SelectFilter::make('storage_id')
                ->label(__('admin/photo.filters.storage'))
                ->relationship('storage', 'name')
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
            ->label(__('admin/photo.columns.user.avatar'))
            ->defaultImageUrl(fn(Photo $photo): ?string => $photo->user ? App::make(UiAvatarsProvider::class)->get($photo->user) : null)
            ->circular();
    }
}
