<?php

namespace App\Filament\Resources\ViolationResource\Pages;

use App\Facades\ViolationService;
use App\Filament\Resources\ViolationResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Violation;
use App\ViolationStatus;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Number;

class ListViolations extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = ViolationResource::class;

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
            ColumnGroup::make(__('admin.user_info'), [
                $this->getUserNameColumn(),
                $this->getUserEmailColumn(),
                $this->getUserPhoneColumn(),
            ]),
            $this->getUserViolationsCountColumn(),
            $this->getPhotoThumbnailUrlColumn(),
            $this->getPhotoNameColumn(),
            $this->getPhotoMimetypeColumn(),
            $this->getPhotoSizeColumn(),
            $this->getReasonColumn(),
            $this->getPhotoCreatedAtColumn(),
            $this->getCreatedAtColumn(),
            $this->getStatusColumn(),
            $this->getHandledAtColumn(),
        ];
    }

    /**
     * 用户名
     * @return Column
     */
    protected function getUserNameColumn(): Column
    {
        return TextColumn::make('user.name')
            ->label(__('admin/violation.columns.user.name'))
            ->searchable()
            ->copyable();
    }

    /**
     * 邮箱
     * @return Column
     */
    protected function getUserEmailColumn(): Column
    {
        return TextColumn::make('user.email')
            ->label(__('admin/violation.columns.user.email'))
            ->searchable(isIndividual: true)
            ->copyable();
    }

    /**
     * 用户违规次数
     * @return Column
     */
    protected function getUserViolationsCountColumn(): Column
    {
        return TextColumn::make('user.violations_count')
            ->label(__('admin/violation.columns.user.violations_count'))
            ->sortable()
            ->alignCenter();
    }

    /**
     * 缩略图
     * @return Column
     */
    protected function getPhotoThumbnailUrlColumn(): Column
    {
        return ImageColumn::make('photo.thumbnail_url')
            ->label(__('admin/violation.columns.photo.thumbnail_url'))
            ->alignCenter()
            ->checkFileExistence(false);
    }

    /**
     * 自定义名称
     * @return Column
     */
    protected function getPhotoNameColumn(): Column
    {
        return TextColumn::make('photo.name')
            ->label(__('admin/violation.columns.photo.name'))
            ->searchable(isIndividual: true);
    }

    /**
     * 文件类型
     * @return Column
     */
    protected function getPhotoMimetypeColumn(): Column
    {
        return TextColumn::make('photo.mimetype')
            ->label(__('admin/violation.columns.photo.mimetype'))
            ->searchable();
    }

    /**
     * 文件大小
     * @return Column
     */
    protected function getPhotoSizeColumn(): Column
    {
        return TextColumn::make('photo.size')
            ->label(__('admin/violation.columns.photo.size'))
            ->alignCenter()
            ->sortable()
            ->formatStateUsing(fn($state): string => $state ? Number::fileSize($state * 1024) : '0.00B');
    }

    /**
     * 违规原因
     * @return Column
     */
    protected function getReasonColumn(): Column
    {
        return TextColumn::make('reason')
            ->label(__('admin/violation.columns.reason'))
            ->searchable()
            ->badge()
            ->color(Color::Red);
    }

    /**
     * 上传时间
     * @return Column
     */
    protected function getPhotoCreatedAtColumn(): Column
    {
        return TextColumn::make('photo.created_at')
            ->label(__('admin/violation.columns.photo.created_at'))
            ->dateTime()
            ->alignCenter()
            ->sortable();
    }

    /**
     * 记录时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/violation.columns.created_at'))
            ->dateTime()
            ->alignCenter()
            ->sortable();
    }

    /**
     * 状态
     * @return Column
     */
    protected function getStatusColumn(): Column
    {
        return TextColumn::make('status')
            ->label(__('admin/violation.columns.status'))
            ->badge()
            ->color(fn(Violation $violation) => match ($violation->status) {
                ViolationStatus::Handled => Color::Green,
                default => Color::Red,
            })
            ->formatStateUsing(fn(Violation $violation): string => __("admin.violation_statuses.{$violation->status->value}"));
    }

    /**
     * 处理时间
     * @return Column
     */
    protected function getHandledAtColumn(): Column
    {
        return TextColumn::make('handled_at')
            ->label(__('admin/violation.columns.handled_at'))
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
            $this->getHandleRowAction(),
            $this->getDeletePhotoRowAction(),
            $this->getDeleteRowAction(),
        ];
    }

    /**
     * 详情操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return ViewAction::make()
            ->modalWidth(MaxWidth::Medium)
            ->slideOver()
            ->visible(fn(Violation $violation): bool => !$violation->photo?->deleted_at);
    }

    /**
     * 处理操作
     * @return Action
     */
    protected function getHandleRowAction(): Action
    {
        return Action::make(__('admin/violation.actions.handle.label'))
            ->icon('heroicon-o-check')
            ->requiresConfirmation()
            ->action(function (Violation $violation) {
                ViolationService::handle($violation);
                Notification::make()->success()->title(__('admin/violation.actions.handle.success'))->send();
            })->visible(fn(Violation $violation): bool => $violation->status === ViolationStatus::Unhandled);
    }

    /**
     * 删除图片操作
     * @return Action
     */
    protected function getDeletePhotoRowAction(): Action
    {
        return Action::make(__('admin/violation.actions.delete_photo.label'))
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalDescription(__('admin/violation.actions.delete_photo.modal_description'))
            ->color(Color::Red)
            ->action(function (Violation $violation) {
                ViolationService::deletePhoto($violation);
                Notification::make()->success()->title(__('admin/violation.actions.delete_photo.success'))->send();
            })->visible(fn(Violation $violation): bool => $violation->status === ViolationStatus::Unhandled);
    }

    /**
     * 删除违规记录操作
     * @return Action
     */
    protected function getDeleteRowAction(): Action
    {
        return Action::make(__('admin/violation.actions.delete.label'))
            ->icon('heroicon-o-trash')
            ->color(Color::Red)
            ->requiresConfirmation()
            ->modalDescription(__('admin/violation.actions.delete_violation.modal_description'))
            ->action(function (Violation $violation) {
                $deletedCount = ViolationService::deleteViolationsAndRestorePhotos($violation);
                
                if ($deletedCount > 0) {
                    Notification::make()
                        ->success()
                        ->title(__('admin/violation.actions.delete_violation.success'))
                        ->send();
                } else {
                    Notification::make()
                        ->danger()
                        ->title(__('admin/violation.actions.delete_violation.error'))
                        ->send();
                }
            });
    }

    /**
     * 获取批量操作
     * @return array
     */
    protected function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                $this->getHandleBulkAction(),
                $this->getDeletePhotoBulkAction(),
                $this->getDeleteBulkAction(),
            ]),
        ];
    }

    /**
     * 批量处理
     * @return BulkAction
     */
    protected function getHandleBulkAction(): BulkAction
    {
        return BulkAction::make(__('admin/violation.actions.handle.label'))
            ->requiresConfirmation()
            ->icon('heroicon-o-check')
            ->action(function (Collection $records) {
                $records->each(fn(Violation $violation) => ViolationService::handle($violation));
                Notification::make()->success()->title(__('admin/violation.actions.handle.success'))->send();
            });
    }

    /**
     * 批量删除图片操作
     * @return BulkAction
     */
    protected function getDeletePhotoBulkAction(): BulkAction
    {
        return BulkAction::make(__('admin/violation.actions.delete_photo.label'))
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalDescription(__('admin/violation.actions.delete_photo.modal_description'))
            ->color(Color::Red)
            ->action(function (Collection $records) {
                $records->each(fn(Violation $violation) => ViolationService::deletePhoto($violation));
                Notification::make()->success()->title(__('admin/violation.actions.delete_photo.success'))->send();
            });
    }

    /**
     * 批量删除违规记录操作
     * @return BulkAction
     */
    protected function getDeleteBulkAction(): BulkAction
    {
        return BulkAction::make(__('admin/violation.actions.delete.label'))
            ->icon('heroicon-o-trash')
            ->color(Color::Red)
            ->requiresConfirmation()
            ->modalDescription(__('admin/violation.actions.delete_violation.bulk_modal_description'))
            ->action(function (Collection $records) {
                try {
                    $deletedCount = ViolationService::deleteViolationsAndRestorePhotos($records);
                    
                    if ($deletedCount > 0) {
                        Notification::make()
                            ->success()
                            ->title(__('admin/violation.actions.delete_violation.bulk_success', ['count' => $deletedCount]))
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title(__('admin/violation.actions.delete_violation.no_records_deleted'))
                            ->send();
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(__('admin/violation.actions.delete_violation.bulk_error_exception'))
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }

    /**
     * 获取过滤器
     * @return array
     */
    protected function getFilters(): array
    {
        return [
            \Filament\Tables\Filters\SelectFilter::make('status')
                ->label(__('admin/violation.filters.status'))
                ->options([
                    ViolationStatus::Unhandled->value => __('admin.violation_statuses.unhandled'),
                    ViolationStatus::Handled->value => __('admin.violation_statuses.handled'),
                ])
                ->placeholder(__('admin.common.filters.all')),
            \Filament\Tables\Filters\SelectFilter::make('user_id')
                ->label(__('admin/violation.filters.user'))
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
     * 手机号
     * @return Column
     */
    protected function getUserPhoneColumn(): Column
    {
        return TextColumn::make('user.phone')
            ->label(__('admin/violation.columns.user.phone'))
            ->searchable()
            ->copyable();
    }
}
