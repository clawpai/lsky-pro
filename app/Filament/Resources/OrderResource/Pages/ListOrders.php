<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App;
use App\Facades\OrderService;
use App\Filament\Resources\OrderResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\Order;
use App\OrderStatus;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
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
use Filament\Tables\Table;

class ListOrders extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = OrderResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getColumns())
            ->filters($this->getFilters())
            ->actions($this->getRowActions())
            ->bulkActions($this->getBulkActions())
            ->checkIfRecordIsSelectableUsing(fn(Order $order): bool => $this->isCanDelete($order));
    }

    /**
     * 获取 table 列
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            $this->getTradeNoColumn(),
            $this->getPlanNameColumn(),
            ColumnGroup::make(__('admin.user_info'), [
                $this->getUserNameColumn(),
                $this->getUserEmailColumn(),
                $this->getUserPhoneColumn(),
            ]),
            $this->getAmountColumn(),
            $this->getPayMethodColumn(),
            $this->getStatusColumn(),
            $this->getPaidAtColumn(),
            $this->getCanceledAtColumn(),
            $this->getCreatedAtColumn(),
        ];
    }

    /**
     * 订单号
     * @return Column
     */
    protected function getTradeNoColumn(): Column
    {
        return TextColumn::make('trade_no')
            ->label(__('admin/order.columns.trade_no'))
            ->searchable(isIndividual: true)
            ->copyable()
            ->alignCenter();
    }

    /**
     * 套餐名称
     * @return Column
     */
    protected function getPlanNameColumn(): Column
    {
        return TextColumn::make('plan.name')
            ->label(__('admin/order.columns.plan.name'))
            ->searchable(isIndividual: true)
            ->formatStateUsing(fn(Order $order): string => "{$order->snapshot['name']}({$order->product['name']})");
    }

    /**
     * 用户名
     * @return Column
     */
    protected function getUserNameColumn(): Column
    {
        return TextColumn::make('user.name')
            ->label(__('admin/order.columns.user.name'))
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
            ->label(__('admin/order.columns.user.email'))
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
            ->label(__('admin/order.columns.user.phone'))
            ->searchable(isIndividual: true)
            ->copyable();
    }

    /**
     * 订单金额
     * @return Column
     */
    protected function getAmountColumn(): Column
    {
        return TextColumn::make('amount')
            ->label(__('admin/order.columns.amount'))
            ->color(Color::Red)
            ->alignCenter()
            ->sortable();
    }

    /**
     * 支付方式
     * @return Column
     */
    protected function getPayMethodColumn(): Column
    {
        return TextColumn::make('pay_method')
            ->label(__('admin/order.columns.pay_method'))
            ->badge()
            ->alignCenter();
    }

    /**
     * 状态
     * @return Column
     */
    protected function getStatusColumn(): Column
    {
        return TextColumn::make('status')
            ->label(__('admin/order.columns.status'))
            ->badge()
            ->color(fn(Order $order) => match ($order->status) {
                OrderStatus::Paid => Color::Green,
                OrderStatus::Cancelled => Color::Red,
                default => Color::Yellow,
            })
            ->alignCenter()
            ->sortable()
            ->formatStateUsing(fn(Order $order): string => __("admin.order_statuses.{$order->status->value}"));
    }

    /**
     * 支付时间
     * @return Column
     */
    protected function getPaidAtColumn(): Column
    {
        return TextColumn::make('paid_at')
            ->label(__('admin/order.columns.paid_at'))
            ->dateTime()
            ->alignCenter()
            ->sortable();
    }

    /**
     * 取消时间
     * @return Column
     */
    protected function getCanceledAtColumn(): Column
    {
        return TextColumn::make('canceled_at')
            ->label(__('admin/order.columns.canceled_at'))
            ->dateTime()
            ->alignCenter()
            ->sortable();
    }

    /**
     * 下单时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/order.columns.created_at'))
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
            $this->getCancelRowAction(),
            $this->getSetAmountRowAction(),
            $this->getDeleteRowAction(),
        ];
    }

    /**
     * 查看详情操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return ViewAction::make()
            ->slideOver()
            ->modalWidth(MaxWidth::Medium)
            ->modalFooterActions([
                $this->getSetAmountRowAction(),
                $this->getCancelRowAction(),
                $this->getDeleteRowAction(),
            ]);
    }

    /**
     * 设置金额操作
     * @return Action
     */
    protected function getSetAmountRowAction(): Action
    {
        return Action::make(__('admin/order.actions.set_amount.label'))
            ->modal()
            ->modalWidth(MaxWidth::Medium)
            ->fillForm(fn(Order $order): array => ['amount' => $order->amount])
            ->form([
                TextInput::make('amount')
                    ->label(__('admin/order.columns.amount'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(999999)
                    ->required(),
            ])
            ->action(function (array $data, Order $order) {
                OrderService::setAmount($order, $data['amount']);
                Notification::make()->success()->title(__('admin/order.actions.set_amount.success'))->send();
            })
            ->visible(fn(Order $order): bool => OrderStatus::Unpaid === $order->status);
    }

    /**
     * 取消订单操作
     * @return Action
     */
    protected function getCancelRowAction(): Action
    {
        return Action::make(__('admin/order.actions.cancel.label'))
            ->requiresConfirmation()
            ->color(Color::Red)
            ->action(function (Order $order) {
                OrderService::cancel($order);
                Notification::make()->success()->title(__('admin/order.actions.cancel.success'))->send();
            })
            ->visible(fn(Order $order): bool => OrderStatus::Unpaid === $order->status);
    }

    /**
     * 删除操作
     * @return Action
     */
    protected function getDeleteRowAction(): Action
    {
        return DeleteAction::make()->visible(fn(Order $order): bool => $this->isCanDelete($order));
    }

    /**
     * 是否可以被删除
     * @param Order $order
     * @return bool
     */
    protected function isCanDelete(Order $order): bool
    {
        return OrderStatus::Paid !== $order->status;
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
            \Filament\Tables\Filters\SelectFilter::make('status')
                ->label(__('admin/order.filters.status'))
                ->options([
                    OrderStatus::Unpaid->value => __('admin.order_statuses.unpaid'),
                    OrderStatus::Paid->value => __('admin.order_statuses.paid'),
                    OrderStatus::Cancelled->value => __('admin.order_statuses.cancelled'),
                ])
                ->placeholder(__('admin.common.filters.all')),
            \Filament\Tables\Filters\SelectFilter::make('pay_method')
                ->label(__('admin/order.filters.pay_method'))
                ->relationship('plan', 'name')
                ->searchable()
                ->preload()
                ->placeholder(__('admin.common.filters.all')),
            \Filament\Tables\Filters\SelectFilter::make('user_id')
                ->label(__('admin/order.filters.user'))
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
            ->label(__('admin/order.columns.user.avatar'))
            ->defaultImageUrl(fn(Order $order): ?string => $order->user ? App::make(UiAvatarsProvider::class)->get($order->user) : null)
            ->circular();
    }
}
