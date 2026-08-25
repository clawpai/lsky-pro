<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Facades\ReportService;
use App\Filament\Resources\ReportResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Report;
use App\Models\Share;
use App\Models\User;
use App\ReportStatus;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ListReports extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = ReportResource::class;

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
            $this->getTypeColumn(),
            ColumnGroup::make(__('admin.user_info'), [
                $this->getUserNameColumn(),
                $this->getUserEmailColumn(),
                $this->getUserPhoneColumn(),
            ]),
            $this->getUserBeReportsCountColumn(),
            $this->getContentColumn(),
            $this->getStatusColumn(),
            $this->getHandledAtColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 举报类型
     * @return Column
     */
    protected function getTypeColumn(): Column
    {
        return TextColumn::make('type')
            ->label(__('admin/report.columns.type'))
            ->badge()
            ->color(fn(Report $report) => match ($report->reportable_type) {
                Photo::class => Color::Sky,
                Album::class => Color::Pink,
                Share::class => Color::Green,
                default => Color::Indigo,
            })
            ->state(fn(Report $report): string => match ($report->reportable_type) {
                Photo::class => __('admin/report.columns.types.photo'),
                Album::class => __('admin/report.columns.types.album'),
                User::class => __('admin/report.columns.types.user'),
                default => __('admin/report.columns.types.share'),
            });
    }

    /**
     * 被举报用户名称
     * @return Column
     */
    protected function getUserNameColumn(): Column
    {
        return TextColumn::make('reportUser.name')
            ->label(__('admin/report.columns.report_user.name'))
            ->searchable()
            ->copyable();
    }

    /**
     * 被举报邮箱
     * @return Column
     */
    protected function getUserEmailColumn(): Column
    {
        return TextColumn::make('reportUser.email')
            ->label(__('admin/report.columns.report_user.email'))
            ->searchable()
            ->copyable();
    }

    /**
     * 用户被举报次数
     * @return Column
     */
    protected function getUserBeReportsCountColumn(): Column
    {
        return TextColumn::make('reportUser.be_reports_count')
            ->label(__('admin/report.columns.report_user.be_reports_count'))
            ->sortable()
            ->alignCenter();
    }

    /**
     * 举报内容
     * @return Column
     */
    protected function getContentColumn(): Column
    {
        return TextColumn::make('content')
            ->label(__('admin/report.columns.content'))
            ->wrap()
            ->searchable()
            ->color(Color::Red);
    }

    /**
     * 状态
     * @return Column
     */
    protected function getStatusColumn(): Column
    {
        return TextColumn::make('status')
            ->label(__('admin/report.columns.status'))
            ->badge()
            ->color(fn(Report $report) => match ($report->status) {
                ReportStatus::Handled => Color::Green,
                default => Color::Red,
            })
            ->formatStateUsing(fn(Report $report): string => __("admin.report_statuses.{$report->status->value}"));
    }

    /**
     * 处理时间
     * @return Column
     */
    protected function getHandledAtColumn(): Column
    {
        return TextColumn::make('handled_at')
            ->label(__('admin/report.columns.handled_at'))
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
            ->label(__('admin/report.columns.created_at'))
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
            $this->getHandleRowAction(),
            $this->getViewRowAction(),
            $this->getDeleteRowAction(),
        ];
    }

    /**
     * 处理操作
     * @return Action
     */
    protected function getHandleRowAction(): Action
    {
        return Action::make(__('admin/report.actions.handle.label'))
            ->icon('heroicon-o-check')
            ->requiresConfirmation()
            ->action(function (Report $report) {
                ReportService::handle($report);
                Notification::make()->success()->title(__('admin/report.actions.handle.success'))->send();
            })->visible(fn(Report $report): bool => $report->status === ReportStatus::Unhandled);
    }

    /**
     * 显示操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return Action::make(__('admin/report.actions.view.label'))
            ->url(function (Report $report): string {
                return route(match ($report->reportable_type) {
                    Album::class => 'filament.admin.resources.albums.view',
                    Photo::class => 'filament.admin.resources.photos.view',
                    Share::class => 'filament.admin.resources.shares.view',
                    User::class => 'filament.admin.resources.users.view',
                }, $report->reportable_id);
            })
            ->openUrlInNewTab();
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
                $this->getHandleBulkAction(),
                $this->getDeleteBulkAction(),
            ]),
        ];
    }

    /**
     * 批量处理操作
     * @return BulkAction
     */
    protected function getHandleBulkAction(): BulkAction
    {
        return BulkAction::make(__('admin/report.actions.handle.label'))
            ->requiresConfirmation()
            ->icon('heroicon-o-check')
            ->action(function (Collection $records) {
                $records->each(fn(Report $report) => ReportService::handle($report));
                Notification::make()->success()->title(__('admin/report.actions.handle.success'))->send();
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
     * 获取过滤器
     * @return array
     */
    protected function getFilters(): array
    {
        return [
            \Filament\Tables\Filters\SelectFilter::make('status')
                ->label(__('admin/report.filters.status'))
                ->options([
                    ReportStatus::Unhandled->value => __('admin.report_statuses.unhandled'),
                    ReportStatus::Handled->value => __('admin.report_statuses.handled'),
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

    /**
     * 被举报手机号
     * @return Column
     */
    protected function getUserPhoneColumn(): Column
    {
        return TextColumn::make('reportUser.phone')
            ->label(__('admin/report.columns.report_user.phone'))
            ->searchable()
            ->copyable();
    }
}
