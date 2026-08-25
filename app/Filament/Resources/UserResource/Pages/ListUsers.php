<?php

namespace App\Filament\Resources\UserResource\Pages;

use App;
use App\Facades\PhotoService;
use App\Filament\Resources\UserResource;
use App\Filament\Traits\HasTableFilters;
use App\Models\User;
use Filament\Actions;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;

class ListUsers extends ListRecords
{
    use HasTableFilters;
    
    protected static string $resource = UserResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getColumns())
            ->filters($this->getFilters())
            ->actions($this->getRowActions())
            ->bulkActions($this->getBulkActions())
            ->checkIfRecordIsSelectableUsing(fn(User $user): bool => $this->isCanDelete($user));
    }

    /**
     * 获取过滤器
     * @return array
     */
    protected function getFilters(): array
    {
        return array_merge([
            TernaryFilter::make('email_verified')
                ->label(__('admin/user.filters.email_verified'))
                ->nullable()
                ->trueLabel(__('admin/user.filters.email_verified_true'))
                ->falseLabel(__('admin/user.filters.email_verified_false'))
                ->queries(
                    true: fn($query) => $query->whereNotNull('email_verified_at'),
                    false: fn($query) => $query->whereNull('email_verified_at'),
                )
                ->placeholder(__('admin.common.filters.all')),
            
            TernaryFilter::make('phone_verified')
                ->label(__('admin/user.filters.phone_verified'))
                ->nullable()
                ->trueLabel(__('admin/user.filters.phone_verified_true'))
                ->falseLabel(__('admin/user.filters.phone_verified_false'))
                ->queries(
                    true: fn($query) => $query->whereNotNull('phone_verified_at'),
                    false: fn($query) => $query->whereNull('phone_verified_at'),
                )
                ->placeholder(__('admin.common.filters.all')),
            
            SelectFilter::make('group')
                ->label(__('admin/user.filters.group'))
                ->placeholder(__('admin.common.filters.all'))
                ->relationship('group.group', 'name')
                ->searchable()
                ->preload(),
        ], $this->getCommonFilters());
    }

    /**
     * 获取 table 列
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            self::getAvatarColumn(),
            self::getGroupNameColumn(),
            self::getNameColumn(),
            self::getEmailColumn(),
            self::getPhoneColumn(),
            self::getPhotosCountColumn(),
            self::getSharesCountColumn(),
            self::getOrdersCountColumn(),
            self::getCapacitiesSumCapacityCountColumn(),
            self::getEmailVerifiedAtColumn(),
            self::getPhoneVerifiedAtColumn(),
            self::getCreatedAtColumn(),
        ];
    }

    /**
     * 头像
     * @return Column
     */
    protected static function getAvatarColumn(): Column
    {
        return ImageColumn::make('avatar')
            ->label(__('admin/user.columns.avatar'))
            ->alignCenter()
            ->width(28)
            ->height(28)
            ->defaultImageUrl(fn(User $user) => App::make(UiAvatarsProvider::class)->get($user))
            ->circular();
    }

    /**
     * 角色组
     * @return Column
     */
    protected static function getGroupNameColumn(): Column
    {
        return TextColumn::make('group.group.name')
            ->label(__('admin/user.columns.group_name'))
            ->default('-')
            ->searchable();
    }

    /**
     * 名称
     * @return Column
     */
    protected static function getNameColumn(): Column
    {
        return TextColumn::make('name')
            ->label(__('admin/user.columns.name'))
            ->searchable();
    }

    /**
     * 邮箱
     * @return Column
     */
    protected static function getEmailColumn(): Column
    {
        return TextColumn::make('email')
            ->label(__('admin/user.columns.email'))
            ->searchable();
    }

    /**
     * 手机号
     * @return Column
     */
    protected static function getPhoneColumn(): Column
    {
        return TextColumn::make('phone')
            ->label(__('admin/user.columns.phone'))
            ->searchable();
    }

    /**
     * 资源数量
     * @return Column
     */
    protected static function getPhotosCountColumn(): Column
    {
        return TextColumn::make('photos_count')
            ->label(__('admin/user.columns.photos_count'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 分享数量
     * @return Column
     */
    protected static function getSharesCountColumn(): Column
    {
        return TextColumn::make('shares_count')
            ->label(__('admin/user.columns.shares_count'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 订单数量
     * @return Column
     */
    protected static function getOrdersCountColumn(): Column
    {
        return TextColumn::make('orders_count')
            ->label(__('admin/user.columns.orders_count'))
            ->alignCenter()
            ->sortable();
    }

    /**
     * 可用容量
     * @return Column
     */
    protected static function getCapacitiesSumCapacityCountColumn(): Column
    {
        return TextColumn::make('capacities_sum_capacity')
            ->label(__('admin/user.columns.capacities_sum_capacity'))
            ->default('0.00B')
            ->alignCenter()
            ->sortable()
            ->formatStateUsing(fn($state): string => $state ? Number::fileSize(floatval($state) * 1024) : '0.00B');
    }

    /**
     * 邮箱验证时间
     * @return Column
     */
    protected static function getEmailVerifiedAtColumn(): Column
    {
        return TextColumn::make('email_verified_at')
            ->label(__('admin/user.columns.email_verified_at'))
            ->alignCenter()
            ->dateTime()
            ->sortable();
    }

    /**
     * 手机号验证时间
     * @return Column
     */
    protected static function getPhoneVerifiedAtColumn(): Column
    {
        return TextColumn::make('phone_verified_at')
            ->label(__('admin/user.columns.phone_verified_at'))
            ->dateTime()
            ->alignCenter()
            ->sortable();
    }

    /**
     * 创建时间
     * @return Column
     */
    protected function getCreatedAtColumn(): Column
    {
        return TextColumn::make('created_at')
            ->label(__('admin/user.columns.created_at'))
            ->sortable()
            ->alignCenter()
            ->dateTime();
    }

    /**
     * 获取行操作列
     * @return array
     */
    protected function getRowActions(): array
    {
        return [
            $this->getViewRowAction(),
            $this->getEditRowAction(),
            $this->getDeleteRowAction(),
            $this->getChangePasswordRowAction(),
            $this->getDeleteAllPhotosRowAction(),
        ];
    }

    /**
     * 详情操作
     * @return Action
     */
    protected function getViewRowAction(): Action
    {
        return ViewAction::make()->slideOver()->modalWidth(MaxWidth::Medium);
    }

    /**
     * 编辑操作
     * @return Action
     */
    protected function getEditRowAction(): Action
    {
        return EditAction::make();
    }

    /**
     * 删除操作
     * @return Action
     */
    protected function getDeleteRowAction(): Action
    {
        return DeleteAction::make()->visible(fn(User $user): bool => $this->isCanDelete($user));
    }

    /**
     * 是否可以删除
     * @param User $user
     * @return bool
     */
    protected function isCanDelete(User $user): bool
    {
        return $user->id !== Auth::id();
    }

    /**
     * 修改密码操作
     * @return Action
     */
    protected function getChangePasswordRowAction(): Action
    {
        return Action::make(__('admin/user.actions.change_password.label'))->form([
            TextInput::make('new_password')
                ->label(__('admin/user.form_fields.new_password.label'))
                ->placeholder(__('admin/user.form_fields.new_password.label'))
                ->password()
                ->revealable()
                ->required()
                ->confirmed(),
            TextInput::make('new_password_confirmation')
                ->label(__('admin/user.form_fields.new_password_confirmation.label'))
                ->placeholder(__('admin/user.form_fields.new_password_confirmation.label'))
                ->password()
                ->revealable()
                ->required(),
        ])
            ->modalWidth(MaxWidth::Medium)
            ->action(function (User $user, $data) {
                $user->password = Hash::make($data['new_password']);
                $user->save();
                Notification::make()->success()->title(__('passwords.reset'))->send();
            })
            ->visible(fn(User $user): bool => $this->isCanChangePassword($user));
    }

    /**
     * 是否可以修改密码
     * @param User $user
     * @return bool
     */
    protected function isCanChangePassword(User $user): bool
    {
        return $user->id !== Auth::id();
    }

    /**
     * 删除用户所有图片操作
     * @return Action
     */
    protected function getDeleteAllPhotosRowAction(): Action
    {
        return Action::make('delete_all_photos')
            ->label(__('admin/user.actions.delete_all_photos.label'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->disabled(fn(User $user): bool => $user->photos_count === 0)
            ->tooltip(fn(User $user): ?string => $user->photos_count === 0 ? __('admin/user.actions.delete_all_photos.no_photos_tooltip') : null)
            ->requiresConfirmation()
            ->modalHeading(__('admin/user.actions.delete_all_photos.modal_heading'))
            ->modalDescription(__('admin/user.actions.delete_all_photos.modal_description'))
            ->modalSubmitActionLabel(__('admin/user.actions.delete_all_photos.modal_submit'))
            ->action(function (User $user) {
                try {
                    $deletedCount = PhotoService::destroyUserAllPhotos($user->id);
                    
                    if ($deletedCount > 0) {
                        Notification::make()
                            ->success()
                            ->title(__('admin/user.actions.delete_all_photos.success_title'))
                            ->body(__('admin/user.actions.delete_all_photos.success_message', ['count' => $deletedCount]))
                            ->send();
                    } else {
                        Notification::make()
                            ->info()
                            ->title(__('admin/user.actions.delete_all_photos.info_title'))
                            ->body(__('admin/user.actions.delete_all_photos.info_message'))
                            ->send();
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(__('admin/user.actions.delete_all_photos.error_title'))
                        ->body(__('admin/user.actions.delete_all_photos.error_message', ['error' => $e->getMessage()]))
                        ->send();
                }
            });
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

    protected function getHeaderActions(): array
    {
        return [
            $this->getRefreshAction(),
            Actions\CreateAction::make(),
        ];
    }
}
