<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Infolists\Components\Component as InfolistComponent;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getRecord(): Model
    {
        return $this->record->load('group.group')->loadSum([
            'capacities' => fn($builder) => $builder->where('expired_at', '>', now())->orWhereNull('expired_at')
        ], 'capacity');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(1)->schema([
            Section::make()->schema([
                Grid::make(3)->schema([
                    $this->getAvatarEntryComponent(),
                    $this->getIsAdminEntryComponent(),
                    $this->getGroupNameEntryComponent(),
                    $this->getNameEntryComponent(),
                    $this->getEmailEntryComponent(),
                    $this->getPhoneEntryComponent(),
                    $this->getLoginIpEntryComponent(),
                    $this->getRegisterIpEntryComponent(),
                    $this->getEmailVerifiedAtEntryComponent(),
                    $this->getPhoneVerifiedAtEntryComponent(),
                    $this->getCreatedAtEntryComponent(),
                    $this->getOrdersCountEntryComponent(),
                    $this->getPhotosCountEntryComponent(),
                    $this->getSharesCountEntryComponent(),
                    $this->getGroupsCountEntryComponent(),
                    $this->getCapacitiesSumCapacityEntryComponent(),
                ])
            ]),
        ]);
    }

    /**
     * 头像
     * @return InfolistComponent
     */
    protected function getAvatarEntryComponent(): InfolistComponent
    {
        return ImageEntry::make('avatar')
            ->hiddenLabel()
            ->width(100)
            ->height(100)
            ->defaultImageUrl(fn(User $user) => App::make(UiAvatarsProvider::class)->get($user))
            ->circular()
            ->alignCenter()
            ->columnSpanFull();
    }

    /**
     * 是否为管理员
     * @return InfolistComponent
     */
    protected function getIsAdminEntryComponent(): InfolistComponent
    {
        return IconEntry::make('is_admin')
            ->label(__('admin/user.columns.is_admin'))
            ->boolean();
    }

    /**
     * 组名称
     * @return InfolistComponent
     */
    protected function getGroupNameEntryComponent(): InfolistComponent
    {
        return TextEntry::make('group.group.name')->label(__('admin/user.columns.group_name'));
    }

    /**
     * 名称
     * @return InfolistComponent
     */
    protected function getNameEntryComponent(): InfolistComponent
    {
        return TextEntry::make('name')->label(__('admin/user.columns.name'));
    }

    /**
     * 邮箱
     * @return InfolistComponent
     */
    protected function getEmailEntryComponent(): InfolistComponent
    {
        return TextEntry::make('email')->label(__('admin/user.columns.email'));
    }

    /**
     * 手机号
     * @return InfolistComponent
     */
    protected function getPhoneEntryComponent(): InfolistComponent
    {
        return TextEntry::make('phone')->label(__('admin/user.columns.phone'));
    }

    /**
     * 登录ip
     * @return InfolistComponent
     */
    protected function getLoginIpEntryComponent(): InfolistComponent
    {
        return TextEntry::make('login_ip')->label(__('admin/user.columns.login_ip'));
    }

    /**
     * 注册ip
     * @return InfolistComponent
     */
    protected function getRegisterIpEntryComponent(): InfolistComponent
    {
        return TextEntry::make('register_ip')->label(__('admin/user.columns.register_ip'));
    }

    /**
     * 邮箱验证时间
     * @return InfolistComponent
     */
    protected function getEmailVerifiedAtEntryComponent(): InfolistComponent
    {
        return TextEntry::make('email_verified_at')
            ->label(__('admin/user.columns.email_verified_at'))
            ->dateTime();
    }

    /**
     * 手机号验证时间
     * @return InfolistComponent
     */
    protected function getPhoneVerifiedAtEntryComponent(): InfolistComponent
    {
        return TextEntry::make('phone_verified_at')
            ->label(__('admin/user.columns.phone_verified_at'))
            ->dateTime();
    }

    /**
     * 创建时间
     * @return InfolistComponent
     */
    protected function getCreatedAtEntryComponent(): InfolistComponent
    {
        return TextEntry::make('created_at')
            ->label(__('admin/user.columns.created_at'))
            ->dateTime();
    }

    /**
     * 订单数量
     * @return InfolistComponent
     */
    protected function getOrdersCountEntryComponent(): InfolistComponent
    {
        return TextEntry::make('orders_count')->label(__('admin/user.columns.orders_count'));
    }

    /**
     * 图片数量
     * @return InfolistComponent
     */
    protected function getPhotosCountEntryComponent(): InfolistComponent
    {
        return TextEntry::make('photos_count')->label(__('admin/user.columns.photos_count'));
    }

    /**
     * 分享数量
     * @return InfolistComponent
     */
    protected function getSharesCountEntryComponent(): InfolistComponent
    {
        return TextEntry::make('shares_count')->label(__('admin/user.columns.shares_count'));
    }

    /**
     * 组数量
     * @return InfolistComponent
     */
    protected function getGroupsCountEntryComponent(): InfolistComponent
    {
        return TextEntry::make('groups_count')->label(__('admin/user.columns.groups_count'));
    }

    /**
     * 可用容量
     * @return InfolistComponent
     */
    protected function getCapacitiesSumCapacityEntryComponent(): InfolistComponent
    {
        return TextEntry::make('capacities_sum_capacity')
            ->label(__('admin/user.columns.capacities_sum_capacity'))
            ->default(0)
            ->formatStateUsing(fn($state): string => $state ? Number::fileSize($state * 1024) : '0.00B');
    }
}
