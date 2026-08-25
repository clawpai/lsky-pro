<?php

namespace App\Filament\Resources\AlbumResource\Pages;

use App\Filament\Resources\AlbumResource;
use App\Models\Album;
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

class ViewAlbum extends ViewRecord
{
    protected static string $resource = AlbumResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(1)->schema([
            Section::make()->schema([
                Fieldset::make(__('admin.user_info'))->schema([
                    $this->getUserAvatarEntryComponent(),
                    $this->getUserNameEntryComponent(),
                    $this->getUserEmailEntryComponent(),
                    $this->getUserPhoneEntryComponent(),
                ]),
            ]),
            Section::make()->schema([
                Grid::make()->schema([
                    $this->getNameEntryComponent(),
                    $this->getIntroEntryComponent(),
                    $this->getCreatedAtEntryComponent(),
                ]),
            ])
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make('delete')];
    }

    /**
     * 头像
     * @return Component
     */
    protected function getUserAvatarEntryComponent(): Component
    {
        return ImageEntry::make('user.avatar')
            ->label(__('admin/album.columns.user.avatar'))
            ->defaultImageUrl(fn(Album $obj): ?string => $obj->user ? App::make(UiAvatarsProvider::class)->get($obj->user) : null)
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
            ->label(__('admin/album.columns.user.name'));
    }

    /**
     * 邮箱
     * @return Component
     */
    protected function getUserEmailEntryComponent(): Component
    {
        return TextEntry::make('user.email')
            ->label(__('admin/album.columns.user.email'));
    }

    /**
     * 手机号
     * @return Component
     */
    protected function getUserPhoneEntryComponent(): Component
    {
        return TextEntry::make('user.phone')
            ->label(__('admin/album.columns.user.phone'));
    }

    /**
     * 名称
     * @return Component
     */
    protected function getNameEntryComponent(): Component
    {
        return TextEntry::make('name')->label(__('admin/album.columns.name'));
    }

    /**
     * 简介
     * @return Component
     */
    protected function getIntroEntryComponent(): Component
    {
        return TextEntry::make('intro')->label(__('admin/album.columns.intro'));
    }

    /**
     * 创建时间
     * @return Component
     */
    protected function getCreatedAtEntryComponent(): Component
    {
        return TextEntry::make('created_at')
            ->label(__('admin/album.columns.created_at'))
            ->dateTime();
    }
}
