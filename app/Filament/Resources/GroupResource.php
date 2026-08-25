<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\GroupResource\Pages;
use App\Models\Group;
use App\Models\Storage;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('admin/group.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/group.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['storages']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()->columns(1)->schema([
                self::getTabsComponent(),
            ])
        ]);
    }

    protected static function getTabsComponent(): Tabs
    {
        return Tabs::make()->tabs([
            self::getBasicSettingComponent(),
            self::getUploadSettingComponent(),
            self::getStorageSettingComponent(),
        ])
            ->persistTab()
            ->id('group-tabs');
    }

    /**
     * 基础设置 Tab
     * @return Tabs\Tab
     */
    protected static function getBasicSettingComponent(): Tabs\Tab
    {
        return Tabs\Tab::make(__('admin/group.form_tabs.basic'))->id('basic')->schema([
            self::getNameFormComponent(),
            self::getIntroFormComponent(),
            self::getSmsDriverFormComponent(),
            self::getMailDriverFormComponent(),
            self::getPaymentDriverFormComponent(),
            self::getIsDefaultFormComponent(),
            self::getIsGuestFormComponent(),
        ]);
    }

    /**
     * 角色组名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/group.form_fields.name.label'))
            ->placeholder(__('admin/group.form_fields.name.placeholder'))
            ->minLength(1)
            ->maxLength(200)
            ->required();
    }

    /**
     * 角色组简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/group.form_fields.intro.label'))
            ->placeholder(__('admin/group.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 短信驱动
     * @return Component
     */
    protected static function getSmsDriverFormComponent(): Component
    {
        return Select::make('sms_drivers')
            ->label(__('admin/group.form_fields.sms.label'))
            ->placeholder(__('admin/group.form_fields.sms.placeholder'))
            ->helperText(__('admin/group.form_fields.sms.helper_text'))
            ->relationship(
                name: 'smsDrivers',
                titleAttribute: 'name',
                modifyQueryUsing: fn(Builder $query) => $query->where('drivers.type', DriverType::Sms)
            )
            ->multiple()
            ->preload();
    }

    /**
     * 邮件驱动
     * @return Component
     */
    protected static function getMailDriverFormComponent(): Component
    {
        return Select::make('mail_drivers')
            ->label(__('admin/group.form_fields.mail.label'))
            ->placeholder(__('admin/group.form_fields.mail.placeholder'))
            ->helperText(__('admin/group.form_fields.mail.helper_text'))
            ->relationship(
                name: 'mailDrivers',
                titleAttribute: 'name',
                modifyQueryUsing: fn(Builder $query) => $query->where('drivers.type', DriverType::Mail)
            )
            ->multiple()
            ->preload();
    }

    /**
     * 支付驱动
     * @return Component
     */
    protected static function getPaymentDriverFormComponent(): Component
    {
        return Select::make('payment_drivers')
            ->label(__('admin/group.form_fields.payment.label'))
            ->placeholder(__('admin/group.form_fields.payment.placeholder'))
            ->helperText(__('admin/group.form_fields.payment.helper_text'))
            ->relationship(
                name: 'paymentDrivers',
                titleAttribute: 'name',
                modifyQueryUsing: fn(Builder $query) => $query->where('drivers.type', DriverType::Payment)
            )
            ->multiple()
            ->preload();
    }

    /**
     * 是否为系统默认组
     * @return Component
     */
    protected static function getIsDefaultFormComponent(): Component
    {
        return Toggle::make('is_default')
            ->label(__('admin/group.form_fields.is_default.label'))
            ->helperText(__('admin/group.form_fields.is_default.helper_text'))
            ->disabled(function (string $operation, ?Group $record): bool {
                if ($operation === 'edit') {
                    return $record->is_default;
                }

                return false;
            });
    }

    /**
     * 是否为游客组
     * @return Component
     */
    protected static function getIsGuestFormComponent(): Component
    {
        return Toggle::make('is_guest')
            ->label(__('admin/group.form_fields.is_guest.label'))
            ->helperText(__('admin/group.form_fields.is_guest.helper_text'))
            ->disabled(function (string $operation, ?Group $record): bool {
                if ($operation === 'edit') {
                    return $record->is_guest;
                }

                return false;
            });
    }

    /**
     * 上传设置 Tab
     * @return Tabs\Tab
     */
    protected static function getUploadSettingComponent(): Tabs\Tab
    {
        return Tabs\Tab::make(__('admin/group.form_tabs.upload'))->id('upload')->schema([
            Grid::make()->schema([
                self::getOptionMaxUploadSizeFormComponent(),
                self::getOptionFileExpireSecondFormComponent(),
                self::getOptionLimitConcurrentUploadFormComponent(),
                self::getOptionLimitPerMinuteFormComponent(),
                self::getOptionLimitPerHourFormComponent(),
                self::getOptionLimitPerDayFormComponent(),
                self::getOptionLimitPerWeekFormComponent(),
                self::getOptionLimitPerMonthFormComponent(),
            ]),
            self::getOptionAllowFileTypesFormComponent(),
        ]);
    }

    /**
     * 最大上传尺寸
     * @return Component
     */
    protected static function getOptionMaxUploadSizeFormComponent(): Component
    {
        return TextInput::make('options.max_upload_size')
            ->label(__('admin/group.form_fields.options.max_upload_size.label'))
            ->placeholder(__('admin/group.form_fields.options.max_upload_size.placeholder'))
            ->numeric()
            ->step(0.01)
            ->minValue(0)
            ->suffix('KB')
            ->inputMode('decimal');
    }

    /**
     * 图片到期时间
     * @return Component
     */
    protected static function getOptionFileExpireSecondFormComponent(): Component
    {
        return TextInput::make('options.file_expire_seconds')
            ->label(__('admin/group.form_fields.options.file_expire_seconds.label'))
            ->suffix(__('admin/group.form_fields.options.file_expire_seconds.suffix'))
            ->placeholder(__('admin/group.form_fields.options.file_expire_seconds.placeholder'))
            ->numeric()
            ->minValue(0);
    }

    /**
     * 并发上传限制
     * @return Component
     */
    protected static function getOptionLimitConcurrentUploadFormComponent(): Component
    {
        return TextInput::make('options.limit_concurrent_upload')
            ->label(__('admin/group.form_fields.options.limit_concurrent_upload.label'))
            ->placeholder(__('admin/group.form_fields.options.limit_concurrent_upload.placeholder'))
            ->numeric()
            ->minValue(1)
            ->maxValue(20);
    }

    /**
     * 每分钟上传限制
     * @return Component
     */
    protected static function getOptionLimitPerMinuteFormComponent(): Component
    {
        return TextInput::make('options.limit_per_minute')
            ->label(__('admin/group.form_fields.options.limit_per_minute.label'))
            ->placeholder(__('admin/group.form_fields.options.limit_per_minute.placeholder'))
            ->numeric()
            ->minValue(0);
    }

    /**
     * 每小时上传限制
     * @return Component
     */
    protected static function getOptionLimitPerHourFormComponent(): Component
    {
        return TextInput::make('options.limit_per_hour')
            ->label(__('admin/group.form_fields.options.limit_per_hour.label'))
            ->placeholder(__('admin/group.form_fields.options.limit_per_hour.placeholder'))
            ->numeric()
            ->minValue(0);
    }

    /**
     * 每天上传限制
     * @return Component
     */
    protected static function getOptionLimitPerDayFormComponent(): Component
    {
        return TextInput::make('options.limit_per_day')
            ->label(__('admin/group.form_fields.options.limit_per_day.label'))
            ->placeholder(__('admin/group.form_fields.options.limit_per_day.placeholder'))
            ->numeric()
            ->minValue(0);
    }

    /**
     * 每周上传限制
     * @return Component
     */
    protected static function getOptionLimitPerWeekFormComponent(): Component
    {
        return TextInput::make('options.limit_per_week')
            ->label(__('admin/group.form_fields.options.limit_per_week.label'))
            ->placeholder(__('admin/group.form_fields.options.limit_per_week.placeholder'))
            ->numeric()
            ->minValue(0);
    }

    /**
     * 每月上传限制
     * @return Component
     */
    protected static function getOptionLimitPerMonthFormComponent(): Component
    {
        return TextInput::make('options.limit_per_month')
            ->label(__('admin/group.form_fields.options.limit_per_month.label'))
            ->placeholder(__('admin/group.form_fields.options.limit_per_month.placeholder'))
            ->numeric()
            ->minValue(0);
    }

    /**
     * 允许上传的文件类型
     * @return Component
     */
    protected static function getOptionAllowFileTypesFormComponent(): Component
    {
        $options = collect(AppService::getAllImageTypes())->map(function ($value) {
            return [$value => strtoupper($value)];
        })->collapse()->toArray();

        return CheckboxList::make('options.allow_file_types')
            ->label(__('admin/group.form_fields.options.allow_file_types.label'))
            ->options($options)
            ->columns(8)
            ->bulkToggleable()
            ->helperText(__('admin/group.form_fields.options.allow_file_types.helper_text'))
            ->disableOptionWhen(fn(string $value): bool => !in_array($value, AppService::getAllSupportedImageTypes()))
            ->gridDirection('row');
    }

    /**
     * 储存设置 Tab
     * @return Tabs\Tab
     */
    protected static function getStorageSettingComponent(): Tabs\Tab
    {
        return Tabs\Tab::make(__('admin/group.form_tabs.storage'))->id('storage')->schema([
            self::getStorageFormComponent(),
        ]);
    }

    /**
     * 储存
     * @return Component
     */
    protected static function getStorageFormComponent(): Component
    {
        return CheckboxList::make('storages')
            ->label(__('admin/group.form_fields.storages.label'))
            ->helperText(__('admin/group.form_fields.storages.helper_text'))
            ->searchable()
            ->bulkToggleable()
            ->descriptions(fn() => Storage::get()->pluck('intro', 'id'))
            ->relationship(
                name: 'storages',
                titleAttribute: 'name',
            );
    }
}
