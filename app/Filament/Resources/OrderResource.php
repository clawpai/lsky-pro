<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\OrderStatus;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.operate');
    }

    public static function getModelLabel(): string
    {
        return __('admin/order.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/order.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'user', 'coupon' => fn($query) => $query->withTrashed(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)->schema([
            Section::make(__('admin/order.view.plan.label'))->schema([
                self::getPlanNameEntryComponent(),
                self::getPlanIntroEntryComponent(),
                self::getPlanBadgeEntryComponent(),
                self::getPlanGroupEntryComponent(),
                self::getPlanCapacityEntryComponent(),
            ]),
            Section::make(__('admin/order.view.product.label'))->schema([
                self::getProductNameEntryComponent(),
                self::getProductDurationEntryComponent(),
                self::getProductPriceEntryComponent(),
            ]),
            Section::make(__('admin/order.view.order.label'))->schema([
                self::getTradeNoEntryComponent(),
                self::getPayMethodEntryComponent(),
                self::getAmountEntryComponent(),
                self::getCanceledAtEntryComponent(),
                self::getPaidAtEntryComponent(),
                self::getStatusEntryComponent(),
                self::getCreatedAtEntryComponent(),
            ]),
            Section::make(__('admin/order.view.user.label'))->schema([
                self::getUserNameEntryComponent(),
                self::getUserEmailEntryComponent(),
                self::getUserPhoneEntryComponent(),
            ]),
        ]);
    }

    /**
     * 套餐名称
     * @return Component
     */
    protected static function getPlanNameEntryComponent(): Component
    {
        return TextEntry::make('plan.name')->label(__('admin/order.view.plan.name'));
    }

    /**
     * 套餐简介
     * @return Component
     */
    protected static function getPlanIntroEntryComponent(): Component
    {
        return TextEntry::make('plan.intro')->label(__('admin/order.view.plan.intro'));
    }

    /**
     * 套餐角标
     * @return Component
     */
    protected static function getPlanBadgeEntryComponent(): Component
    {
        return TextEntry::make('plan.badge')
            ->label(__('admin/order.view.plan.badge'))
            ->visible(fn($state): bool => (bool)$state);
    }

    /**
     * 套餐关联角色组
     * @return Component
     */
    protected static function getPlanGroupEntryComponent(): Component
    {
        return TextEntry::make('snapshot.group.group.name')
            ->label(__('admin/order.view.plan.group'))
            ->visible(fn(Order $order): bool => isset($order->snapshot['group']['group']['name']));
    }

    /**
     * 套餐容量
     * @return Component
     */
    protected static function getPlanCapacityEntryComponent(): Component
    {
        return TextEntry::make('snapshot.capacity.capacity')
            ->label(__('admin/order.view.plan.capacity'))
            ->visible(fn(Order $order): bool => (bool)($order->snapshot['capacity'] ?? false));
    }

    /**
     * 产品名称
     * @return Component
     */
    protected static function getProductNameEntryComponent(): Component
    {
        return TextEntry::make('product.name')->label(__('admin/order.view.product.name'));
    }

    /**
     * 产品时长
     * @return Component
     */
    protected static function getProductDurationEntryComponent(): Component
    {
        return TextEntry::make('product.duration')
            ->label(__('admin/order.view.product.duration'))
            ->default('-');
    }

    /**
     * 产品价格
     * @return Component
     */
    protected static function getProductPriceEntryComponent(): Component
    {
        return TextEntry::make('product.price')
            ->label(__('admin/order.view.product.price'))
            ->default('0.00')
            ->color(Color::Red);
    }

    /**
     * 订单号
     * @return Component
     */
    protected static function getTradeNoEntryComponent(): Component
    {
        return TextEntry::make('trade_no')->label(__('admin/order.columns.trade_no'));
    }

    /**
     * 支付方式
     * @return Component
     */
    protected static function getPayMethodEntryComponent(): Component
    {
        return TextEntry::make('pay_method')
            ->label(__('admin/order.columns.pay_method'))
            ->badge()
            ->visible(fn(Order $order): bool => $order->status === OrderStatus::Paid);
    }

    /**
     * 订单金额
     * @return Component
     */
    protected static function getAmountEntryComponent(): Component
    {
        return TextEntry::make('amount')
            ->label(__('admin/order.columns.amount'))
            ->default('0.00')
            ->color(Color::Red);
    }

    /**
     * 订单取消时间
     * @return Component
     */
    protected static function getCanceledAtEntryComponent(): Component
    {
        return TextEntry::make('canceled_at')
            ->label(__('admin/order.columns.canceled_at'))
            ->dateTime()
            ->visible(fn(Order $order): bool => $order->status === OrderStatus::Cancelled);
    }

    /**
     * 订单支付时间
     * @return Component
     */
    protected static function getPaidAtEntryComponent(): Component
    {
        return TextEntry::make('paid_at')
            ->label(__('admin/order.columns.paid_at'))
            ->dateTime()
            ->visible(fn(Order $order): bool => $order->status === OrderStatus::Paid);
    }

    /**
     * 订单状态
     * @return Component
     */
    protected static function getStatusEntryComponent(): Component
    {
        return TextEntry::make('status')
            ->label(__('admin/order.columns.status'))
            ->badge()
            ->iconColor(fn(Order $order) => match ($order->status) {
                OrderStatus::Paid => Color::Green,
                OrderStatus::Cancelled => Color::Red,
                default => Color::Yellow,
            })
            ->icon(fn(Order $order) => match ($order->status) {
                OrderStatus::Paid => 'heroicon-o-check-circle',
                OrderStatus::Cancelled => 'heroicon-o-x-mark',
                default => 'heroicon-o-information-circle',
            })
            ->color(fn(Order $order) => match ($order->status) {
                OrderStatus::Paid => Color::Green,
                OrderStatus::Cancelled => Color::Red,
                default => Color::Yellow,
            })
            ->formatStateUsing(fn(Order $order): string => __("admin.order_statuses.{$order->status->value}"));
    }

    /**
     * 订单创建时间
     * @return Component
     */
    protected static function getCreatedAtEntryComponent(): Component
    {
        return TextEntry::make('created_at')
            ->label(__('admin/order.columns.created_at'))
            ->dateTime();
    }

    /**
     * 用户名
     * @return Component
     */
    protected static function getUserNameEntryComponent(): Component
    {
        return TextEntry::make('user.name')->label(__('admin/order.columns.user.name'));
    }

    /**
     * 用户邮箱
     * @return Component
     */
    protected static function getUserEmailEntryComponent(): Component
    {
        return TextEntry::make('user.email')->label(__('admin/order.columns.user.email'));
    }

    /**
     * 用户手机号
     * @return Component
     */
    protected static function getUserPhoneEntryComponent(): Component
    {
        return TextEntry::make('user.phone')->label(__('admin/order.columns.user.phone'));
    }
}
