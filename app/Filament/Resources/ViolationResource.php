<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ViolationResource\Pages;
use App\Models\Violation;
use App\ViolationStatus;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Number;

class ViolationResource extends Resource
{
    protected static ?string $model = Violation::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye-slash';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.user');
    }

    public static function getModelLabel(): string
    {
        return __('admin/violation.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/violation.plural_model_label');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', ViolationStatus::Unhandled)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', ViolationStatus::Unhandled)->count() > 10 ? 'warning' : 'primary';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'user' => fn($query) => $query->withCount(['violations' => fn($query) => $query->withTrashed()]),
            'photo' => fn($query) => $query->withTrashed()->with('group', 'storage'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListViolations::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)->columns(1)->schema([
            self::getPhotoThumbnailUrlEntryComponent(),
            self::getUserNameEntryComponent(),
            self::getUserEmailEntryComponent(),
            self::getUserPhoneEntryComponent(),
            self::getUserViolationsCountEntryComponent(),
            self::getPhotoGroupNameEntryComponent(),
            self::getPhotoStorageNameEntryComponent(),
            self::getPhotoNameEntryComponent(),
            self::getPhotoMimetypeEntryComponent(),
            self::getPhotoSizeEntryComponent(),
            self::getPhotoMd5EntryComponent(),
            self::getPhotoSha1EntryComponent(),
            self::getPhotoIpAddressEntryComponent(),
            self::getPhotoCreatedAtEntryComponent(),
            self::getCreatedAtEntryComponent(),
            self::getReasonEntryComponent(),
            self::getStatusEntryComponent(),
            self::getHandledAtEntryComponent(),
            self::getPhotoUrlEntryComponent(),
        ]);
    }

    /**
     * 图片
     * @return Component
     */
    protected static function getPhotoThumbnailUrlEntryComponent(): Component
    {
        return ImageEntry::make('photo.thumbnail_url')
            ->label(__('admin/violation.columns.photo.thumbnail_url'))
            ->alignCenter()
            ->checkFileExistence(false);
    }

    /**
     * 用户名
     * @return Component
     */
    protected static function getUserNameEntryComponent(): Component
    {
        return TextEntry::make('user.name')
            ->label(__('admin/violation.columns.user.name'));
    }

    /**
     * 邮箱
     * @return Component
     */
    protected static function getUserEmailEntryComponent(): Component
    {
        return TextEntry::make('user.email')
            ->label(__('admin/violation.columns.user.email'));
    }

    /**
     * 手机号
     * @return Component
     */
    protected static function getUserPhoneEntryComponent(): Component
    {
        return TextEntry::make('user.phone')
            ->label(__('admin/violation.columns.user.phone'));
    }

    /**
     * 用户违规次数
     * @return Component
     */
    protected static function getUserViolationsCountEntryComponent(): Component
    {
        return TextEntry::make('user.violations_count')
            ->label(__('admin/violation.columns.user.violations_count'));
    }

    /**
     * 所在组名称
     * @return Component
     */
    protected static function getPhotoGroupNameEntryComponent(): Component
    {
        return TextEntry::make('photo.group.name')
            ->label(__('admin/violation.columns.photo.group.name'));
    }

    /**
     * 所在储存名称
     * @return Component
     */
    protected static function getPhotoStorageNameEntryComponent(): Component
    {
        return TextEntry::make('photo.storage.name')
            ->label(__('admin/violation.columns.photo.storage.name'));
    }

    /**
     * 文件名
     * @return Component
     */
    protected static function getPhotoNameEntryComponent(): Component
    {
        return TextEntry::make('photo.name')
            ->label(__('admin/violation.columns.photo.name'))
            ->copyable();
    }

    /**
     * 文件类型
     * @return Component
     */
    protected static function getPhotoMimetypeEntryComponent(): Component
    {
        return TextEntry::make('photo.mimetype')
            ->label(__('admin/violation.columns.photo.mimetype'));
    }

    /**
     * 文件大小
     * @return Component
     */
    protected static function getPhotoSizeEntryComponent(): Component
    {
        return TextEntry::make('photo.size')
            ->label(__('admin/violation.columns.photo.size'))
            ->formatStateUsing(fn($state): string => $state ? Number::fileSize($state * 1024) : '0.00B');
    }

    /**
     * 文件 MD5
     * @return Component
     */
    protected static function getPhotoMd5EntryComponent(): Component
    {
        return TextEntry::make('photo.md5')
            ->label(__('admin/violation.columns.photo.md5'))
            ->copyable();
    }

    /**
     * 文件 SHA-1
     * @return Component
     */
    protected static function getPhotoSha1EntryComponent(): Component
    {
        return TextEntry::make('photo.sha1')
            ->label(__('admin/violation.columns.photo.sha1'))
            ->copyable();
    }

    /**
     * 上传 ip
     * @return Component
     */
    protected static function getPhotoIpAddressEntryComponent(): Component
    {
        return TextEntry::make('photo.ip_address')
            ->label(__('admin/violation.columns.photo.ip_address'))
            ->copyable();
    }

    /**
     * 上传时间
     * @return Component
     */
    protected static function getPhotoCreatedAtEntryComponent(): Component
    {
        return TextEntry::make('photo.created_at')
            ->label(__('admin/violation.columns.photo.created_at'))
            ->dateTime();
    }

    /**
     * 记录时间
     * @return Component
     */
    protected static function getCreatedAtEntryComponent(): Component
    {
        return TextEntry::make('created_at')
            ->label(__('admin/violation.columns.created_at'))
            ->dateTime();
    }

    /**
     * 违规原因
     * @return Component
     */
    protected static function getReasonEntryComponent(): Component
    {
        return TextEntry::make('reason')
            ->label(__('admin/violation.columns.reason'))
            ->badge()
            ->color(Color::Red);
    }

    /**
     * 状态
     * @return Component
     */
    protected static function getStatusEntryComponent(): Component
    {
        return TextEntry::make('status')
            ->label(__('admin/violation.columns.status'))
            ->badge()
            ->color(fn(Violation $violation) => match ($violation->status) {
                ViolationStatus::Handled => Color::Green,
                default => Color::Red,
            })
            ->formatStateUsing(fn(Violation $violation) => __("admin.violation_statuses.{$violation->status->value}"));
    }

    /**
     * 处理时间
     * @return Component
     */
    protected static function getHandledAtEntryComponent(): Component
    {
        return TextEntry::make('handled_at')
            ->label(__('admin/violation.columns.handled_at'))
            ->dateTime();
    }

    /**
     * Url
     * @return Component
     */
    protected static function getPhotoUrlEntryComponent(): Component
    {
        return TextEntry::make('photo.public_url')
            ->label('Url')
            ->copyable();
    }
}
