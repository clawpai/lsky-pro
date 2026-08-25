<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Facades\PhotoService;
use App\Filament\Resources\PhotoResource;
use App\Models\Photo;
use App\PhotoStatus;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;

class ViewPhoto extends ViewRecord
{
    protected static string $resource = PhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getRestoreViolationAction(),
            DeleteAction::make('delete'),
        ];
    }

    /**
     * 恢复违规图片操作
     * @return Action
     */
    protected function getRestoreViolationAction(): Action
    {
        return Action::make('restore_violation')
            ->label(__('admin/photo.actions.restore_violation.label'))
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(__('admin/photo.actions.restore_violation.modal_description'))
            ->action(function () {
                try {
                    PhotoService::restoreViolationPhoto($this->record);
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
            ->visible(fn(): bool => $this->record->status === PhotoStatus::Violation);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(1)->schema([
            Split::make([
                Section::make()->schema([
                    $this->getThumbnailUrlEntryComponent(),
                    Fieldset::make(__('admin.user_info'))->schema([
                        $this->getUserAvatarEntryComponent(),
                        $this->getUserNameEntryComponent(),
                        $this->getUserEmailEntryComponent(),
                        $this->getUserPhoneEntryComponent(),
                    ]),
                ])->grow(false),
                Section::make()->schema([
                    Grid::make()->schema([
                        $this->getGroupNameEntryComponent(),
                        $this->getStorageNameEntryComponent(),
                        $this->getNameEntryComponent(),
                        $this->getMimetypeEntryComponent(),
                        $this->getSizeEntryComponent(),
                        $this->getMd5EntryComponent(),
                        $this->getSha1EntryComponent(),
                        $this->getIpAddressEntryComponent(),
                        $this->getCreatedAtEntryComponent(),
                        $this->getUrlEntryComponent(),
                        $this->getHtmlEntryComponent(),
                        $this->getBBCodeEntryComponent(),
                        $this->getMarkdownEntryComponent(),
                        $this->getMarkdownWithLinkEntryComponent(),
                    ]),
                ]),
            ])->from('md'),
        ]);
    }

    /**
     * 图片
     * @return Component
     */
    protected function getThumbnailUrlEntryComponent(): Component
    {
        return ImageEntry::make('thumbnail_url')
            ->label(__('admin/photo.columns.thumbnail_url'))
            ->alignCenter()
            ->extraImgAttributes([
                'loading' => 'lazy',
                'style' => 'object-fit: contain; max-width: 20rem; min-height: 20rem;',
            ])
            ->checkFileExistence(false);
    }

    /**
     * 头像
     * @return Component
     */
    protected function getUserAvatarEntryComponent(): Component
    {
        return ImageEntry::make('user.avatar')
            ->label(__('admin/photo.columns.user.avatar'))
            ->defaultImageUrl(fn (Photo $obj): ?string => $obj->user ? App::make(UiAvatarsProvider::class)->get($obj->user) : null)
            ->width(40)
            ->height(40)
            ->circular();
    }

    /**
     * 用户名
     * @return Component
     */
    protected function getUserNameEntryComponent(): Component
    {
        return TextEntry::make('user.name')
            ->label(__('admin/photo.columns.user.name'));
    }

    /**
     * 邮箱
     * @return Component
     */
    protected function getUserEmailEntryComponent(): Component
    {
        return TextEntry::make('user.email')
            ->label(__('admin/photo.columns.user.email'));
    }

    /**
     * 手机号
     * @return Component
     */
    protected function getUserPhoneEntryComponent(): Component
    {
        return TextEntry::make('user.phone')
            ->label(__('admin/photo.columns.user.phone'));
    }

    /**
     * 所在组名称
     * @return Component
     */
    protected function getGroupNameEntryComponent(): Component
    {
        return TextEntry::make('group.name')
            ->label(__('admin/photo.columns.group.name'));
    }

    /**
     * 所在储存名称
     * @return Component
     */
    protected function getStorageNameEntryComponent(): Component
    {
        return TextEntry::make('storage.name')
            ->label(__('admin/photo.columns.storage.name'));
    }

    /**
     * 文件名
     * @return Component
     */
    protected function getNameEntryComponent(): Component
    {
        return TextEntry::make('name')
            ->label(__('admin/photo.columns.name'))
            ->copyable();
    }

    /**
     * 文件类型
     * @return Component
     */
    protected function getMimetypeEntryComponent(): Component
    {
        return TextEntry::make('mimetype')
            ->label(__('admin/photo.columns.mimetype'));
    }

    /**
     * 文件大小
     * @return Component
     */
    protected function getSizeEntryComponent(): Component
    {
        return TextEntry::make('size')
            ->label(__('admin/photo.columns.size'))
            ->formatStateUsing(fn($state): string => $state ? Number::fileSize($state * 1024) : '0.00B');
    }

    /**
     * 文件 MD5
     * @return Component
     */
    protected function getMd5EntryComponent(): Component
    {
        return TextEntry::make('md5')
            ->label(__('admin/photo.columns.md5'))
            ->copyable();
    }

    /**
     * 文件 SHA-1
     * @return Component
     */
    protected function getSha1EntryComponent(): Component
    {
        return TextEntry::make('sha1')
            ->label(__('admin/photo.columns.sha1'))
            ->copyable();
    }

    /**
     * 上传 ip
     * @return Component
     */
    protected function getIpAddressEntryComponent(): Component
    {
        return TextEntry::make('ip_address')
            ->label(__('admin/photo.columns.ip_address'))
            ->copyable();
    }

    /**
     * 创建时间
     * @return Component
     */
    protected function getCreatedAtEntryComponent(): Component
    {
        return TextEntry::make('created_at')
            ->label(__('admin/photo.columns.created_at'))
            ->dateTime();
    }

    /**
     * Url
     *
     * @return Component
     */
    protected function getUrlEntryComponent(): Component
    {
        return TextEntry::make('resource_urls.url')
            ->label('Url')
            ->copyable();
    }

    /**
     * Html
     *
     * @return Component
     */
    protected function getHtmlEntryComponent(): Component
    {
        return TextEntry::make('resource_urls.html')
            ->label('Html')
            ->copyable();
    }

    /**
     * BBCode
     *
     * @return Component
     */
    protected function getBBCodeEntryComponent(): Component
    {
        return TextEntry::make('resource_urls.bbcode')
            ->label('BBCode')
            ->copyable();
    }

    /**
     * Markdown
     *
     * @return Component
     */
    protected function getMarkdownEntryComponent(): Component
    {
        return TextEntry::make('resource_urls.markdown')
            ->label('Markdown')
            ->copyable();
    }

    /**
     * Markdown with link
     *
     * @return Component
     */
    protected function getMarkdownWithLinkEntryComponent(): Component
    {
        return TextEntry::make('resource_urls.markdown_with_link')
            ->label('Markdown with link')
            ->copyable();
    }
}
