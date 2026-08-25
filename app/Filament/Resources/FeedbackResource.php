<?php

namespace App\Filament\Resources;

use App\FeedbackType;
use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?int $navigationSort = 15;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.operate');
    }

    public static function getModelLabel(): string
    {
        return __('admin/feedback.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/feedback.plural_model_label');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(1)->schema([
            self::getTypeEntryComponent(),
            self::getNameEntryComponent(),
            self::getEmailEntryComponent(),
            self::getCreatedAtEntryComponent(),
            self::getTitleEntryComponent(),
            self::getContentEntryComponent(),
        ]);
    }

    /**
     * 类型
     * @return Component
     */
    protected static function getTypeEntryComponent(): Component
    {
        return TextEntry::make('type')
            ->label(__('admin/feedback.columns.type'))
            ->formatStateUsing(fn(FeedbackType $state) => __("admin.feedback_types.{$state->value}"))
            ->badge();
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameEntryComponent(): Component
    {
        return TextEntry::make('name')->label(__('admin/feedback.columns.name'));
    }

    /**
     * 邮箱
     * @return Component
     */
    protected static function getEmailEntryComponent(): Component
    {
        return TextEntry::make('email')->label(__('admin/feedback.columns.email'));
    }

    /**
     * 创建时间
     * @return Component
     */
    protected static function getCreatedAtEntryComponent(): Component
    {
        return TextEntry::make('created_at')
            ->label(__('admin/feedback.columns.created_at'))
            ->dateTime();
    }

    /**
     * 标题
     * @return Component
     */
    protected static function getTitleEntryComponent(): Component
    {
        return TextEntry::make('title')->label(__('admin/feedback.columns.title'));
    }

    /**
     * 反馈内容
     * @return Component
     */
    protected static function getContentEntryComponent(): Component
    {
        return TextEntry::make('content')->label(__('admin/feedback.columns.content'));
    }
}
