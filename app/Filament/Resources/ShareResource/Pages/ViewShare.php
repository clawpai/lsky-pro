<?php

namespace App\Filament\Resources\ShareResource\Pages;

use App\Filament\Resources\ShareResource;
use App\Models\Share;
use Filament\Actions\DeleteAction;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\App;

class ViewShare extends ViewRecord
{
    protected static string $resource = ShareResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make('delete')];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(1)->schema([
            Section::make()->columns(1)->schema([
                Fieldset::make(__('admin.user_info'))->schema([
                    $this->getUserAvatarEntryComponent(),
                    $this->getUserNameEntryComponent(),
                    $this->getUserEmailEntryComponent(),
                    $this->getUserPhoneEntryComponent(),
                ]),
            ]),
            Section::make()->schema([
                Grid::make()->schema([
                    $this->getUrlEntryComponent(),
                    $this->getPasswordEntryComponent(),
                    $this->getContentEntryComponent(),
                    $this->getExpiredAtEntryComponent(),
                    $this->getCreatedAtEntryComponent(),
                ])
            ]),
        ]);
    }

    /**
     * 头像
     * @return Component
     */
    protected function getUserAvatarEntryComponent(): Component
    {
        return ImageEntry::make('user.avatar')
            ->label(__('admin/share.columns.user.avatar'))
            ->defaultImageUrl(fn(Share $obj): ?string => $obj->user ? App::make(UiAvatarsProvider::class)->get($obj->user) : null)
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
            ->label(__('admin/share.columns.user.name'));
    }

    /**
     * 邮箱
     * @return Component
     */
    protected function getUserEmailEntryComponent(): Component
    {
        return TextEntry::make('user.email')
            ->label(__('admin/share.columns.user.email'));
    }

    /**
     * 手机号
     * @return Component
     */
    protected function getUserPhoneEntryComponent(): Component
    {
        return TextEntry::make('user.phone')
            ->label(__('admin/share.columns.user.phone'));
    }

    /**
     * 分享地址
     * @return Component
     */
    protected function getUrlEntryComponent(): Component
    {
        return TextEntry::make('url')
            ->label(__('admin/share.columns.url'))
            ->copyable();
    }

    /**
     * 提取码
     * @return Component
     */
    protected function getPasswordEntryComponent(): Component
    {
        return TextEntry::make('password')
            ->label(__('admin/share.columns.password'))
            ->copyable();
    }

    /**
     * 分享内容
     * @return Component
     */
    protected function getContentEntryComponent(): Component
    {
        return TextEntry::make('content')
            ->label(__('admin/share.columns.content'));
    }

    /**
     * 到期时间
     * @return Component
     */
    protected function getExpiredAtEntryComponent(): Component
    {
        return TextEntry::make('expired_at')
            ->label(__('admin/share.columns.expired_at'))
            ->dateTime();
    }

    /**
     * 分享时间
     * @return Component
     */
    protected function getCreatedAtEntryComponent(): Component
    {
        return TextEntry::make('created_at')
            ->label(__('admin/share.columns.created_at'))
            ->dateTime();
    }
}
