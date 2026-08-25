<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Facades\TicketService;
use App\Filament\Resources\TicketResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Ticket;
use App\TicketLevel;
use App\TicketStatus;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ListTickets extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = TicketResource::class;

    public function table(Table $table): Table
    {
        $query = $table->getQuery()->with('reply');
        return $table
            ->query($query)
            ->columns($this->getColumns())
            ->filters($this->getFilters())
            ->actions($this->getRowActions())
            ->bulkActions($this->getBulkActions())
            ->checkIfRecordIsSelectableUsing(fn(Ticket $ticket) => $ticket->status === TicketStatus::InProgress);
    }

    /**
     * 获取 table 列
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            $this->getIssueNoColumn(),
            $this->getTitleColumn(),
            $this->getLevelColumn(),
            $this->getLastReplyContentColumn(),
            $this->getStatusColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 工单号
     * @return Column
     */
    protected function getIssueNoColumn(): Column
    {
        return TextColumn::make('issue_no')
            ->label(__('admin/ticket.columns.issue_no'))
            ->searchable();
    }

    /**
     * 标题
     * @return Column
     */
    protected function getTitleColumn(): Column
    {
        return TextColumn::make('title')
            ->label(__('admin/ticket.columns.title'))
            ->searchable();
    }

    /**
     * 工单级别
     * @return Column
     */
    protected function getLevelColumn(): Column
    {
        return TextColumn::make('level')
            ->label(__('admin/ticket.columns.level'))
            ->badge()
            ->color(fn(TicketLevel $state) => match ($state) {
                TicketLevel::Medium => 'warning',
                TicketLevel::High => 'danger',
                default => 'info',
            })
            ->alignCenter()
            ->formatStateUsing(fn(TicketLevel $state) => __("admin.ticket_levels.{$state->value}"));
    }

    /**
     * 最后回复内容
     * @return Column
     */
    protected function getLastReplyContentColumn(): Column
    {
        return TextColumn::make('reply.content')
            ->label(__('admin/ticket.columns.reply.content'))
            ->limit(60)
            ->icon(function (Ticket $ticket) {
                if ($ticket->reply) {
                    if ($ticket->reply->user_id !== Auth::id() && !$ticket->reply->read_at) {
                        return 'heroicon-o-bell-alert';
                    }
                }
                return null;
            })
            ->iconColor(Color::Red);
    }

    /**
     * 工单状态
     * @return Column
     */
    protected function getStatusColumn(): Column
    {
        return TextColumn::make('status')
            ->label(__('admin/ticket.columns.status'))
            ->alignCenter()
            ->badge()
            ->color(fn(TicketStatus $state) => match ($state) {
                TicketStatus::InProgress => 'warning',
                default => 'success',
            })
            ->formatStateUsing(fn(TicketStatus $state) => __("admin.ticket_statuses.{$state->value}"));
    }

    /**
     * 创建时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/ticket.columns.created_at'))
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
            $this->getCloseRowAction(),
            $this->getViewRowAction(),
        ];
    }

    /**
     * 关闭操作
     * @return Action
     */
    protected function getCloseRowAction(): Action
    {
        return Action::make(__('admin/ticket.actions.close.label'))
            ->requiresConfirmation()
            ->color(Color::Red)
            ->modalDescription(__('admin/ticket.actions.close.description'))
            ->action(function (Ticket $ticket) {
                TicketService::close($ticket);
                Notification::make()->success()->title(__('admin/ticket.actions.close.success'))->send();
            })->visible(fn(Ticket $ticket) => $ticket->status === TicketStatus::InProgress);
    }

    /**
     * 详情操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return ViewAction::make()->label(__('admin/ticket.actions.view.label'));
    }

    /**
     * 获取批量操作
     * @return array
     */
    protected function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                $this->getCloseBulkAction(),
            ]),
        ];
    }

    /**
     * 批量关闭操作
     * @return BulkAction
     */
    protected function getCloseBulkAction(): BulkAction
    {
        return BulkAction::make(__('admin/ticket.actions.close.label'))
            ->requiresConfirmation()
            ->color(Color::Red)
            ->modalDescription(__('admin/ticket.actions.close.description'))
            ->action(function (Collection $tickets) {
                $tickets->each(function (Ticket $ticket) {
                    TicketService::close($ticket);
                });

                Notification::make()->success()->title(__('admin/ticket.actions.close.success'))->send();
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
                ->label(__('admin/ticket.filters.status'))
                ->options([
                    TicketStatus::InProgress->value => __('admin.ticket_statuses.in_progress'),
                    TicketStatus::Completed->value => __('admin.ticket_statuses.completed'),
                ])
                ->placeholder(__('admin.common.filters.all')),
            \Filament\Tables\Filters\SelectFilter::make('level')
                ->label(__('admin/ticket.filters.level'))
                ->options([
                    TicketLevel::Low->value => __('admin.ticket_levels.low'),
                    TicketLevel::Medium->value => __('admin.ticket_levels.medium'),
                    TicketLevel::High->value => __('admin.ticket_levels.high'),
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
