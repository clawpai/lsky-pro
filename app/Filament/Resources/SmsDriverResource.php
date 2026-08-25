<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\SmsDriverResource\Pages;
use App\Models\Driver;
use App\SmsProvider;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Overtrue\EasySms\Gateways\ChuanglanGateway;

class SmsDriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone-arrow-down-left';

    protected static ?int $navigationSort = 21;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.driver');
    }

    public static function getModelLabel(): string
    {
        return __('admin/sms_driver.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/sms_driver.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', DriverType::Sms);
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Section::make()->schema([
                Grid::make()->schema([
                    self::getNameFormComponent(),
                    self::getOptionProviderFormComponent(),
                ]),
                self::getIntroFormComponent(),
                ...self::getAliyunOptionFormComponents(),
                ...self::getAliyunRestOptionFormComponents(),
                ...self::getAliyunIntlOptionFormComponents(),
                ...self::getYunpianOptionFormComponents(),
                ...self::getSubmailOptionFormComponents(),
                ...self::getLuosimaoOptionFormComponents(),
                ...self::getYuntongxunOptionFormComponents(),
                ...self::getHuyiOptionFormComponents(),
                ...self::getJuheOptionFormComponents(),
                ...self::getSendCloudOptionFormComponents(),
                ...self::getBaiduOptionFormComponents(),
                ...self::getHuaxinOptionFormComponents(),
                ...self::getChuanglanOptionFormComponents(),
                ...self::getChuanglanv1OptionFormComponents(),
                ...self::getRongCloudOptionFormComponents(),
                ...self::getTianyiwuxianOptionFormComponents(),
                ...self::getTwilioOptionFormComponents(),
                ...self::getTiniyoOptionFormComponents(),
                ...self::getQCloudOptionFormComponents(),
                ...self::getHuaweiOptionFormComponents(),
                ...self::getYunxinOptionFormComponents(),
                ...self::getYunzhixunOptionFormComponents(),
                ...self::getKingttoOptionFormComponents(),
                ...self::getQiniuOptionFormComponents(),
                ...self::getUCloudOptionFormComponents(),
                ...self::getSmsbaoOptionFormComponents(),
                ...self::getModuyunOptionFormComponents(),
                ...self::getRongheyunOptionFormComponents(),
                ...self::getZzyunOptionFormComponents(),
                ...self::getMaapOptionFormComponents(),
                ...self::getTinreeOptionFormComponents(),
                ...self::getNowcnOptionFormComponents(),
                ...self::getVolcengineOptionFormComponents(),
                ...self::getYidongmasblackOptionFormComponents(),
            ]),
            self::getOptionTemplatesFormComponent(),
        ]);
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/sms_driver.form_fields.name.label'))
            ->placeholder(__('admin/sms_driver.form_fields.name.placeholder'))
            ->minLength(1)
            ->maxLength(200)
            ->required();
    }

    /**
     * 发信网关
     * @return Component
     */
    protected static function getOptionProviderFormComponent(): Component
    {
        return Select::make('options.provider')
            ->label(__('admin/sms_driver.form_fields.options.provider.label'))
            ->placeholder(__('admin/sms_driver.form_fields.options.provider.placeholder'))
            ->options(AppService::getAllSmsProviders())
            ->live()
            ->required()
            ->searchable()
            ->default(SmsProvider::Aliyun->value)
            ->native(false);
    }

    /**
     * 简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/sms_driver.form_fields.intro.label'))
            ->placeholder(__('admin/sms_driver.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 阿里云配置表单
     * @return array<Component>
     */
    protected static function getAliyunOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.access_key_id')
                        ->label(__('admin/sms_driver.form_fields.aliyun_options.access_key_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.aliyun_options.access_key_secret.label'))
                        ->required(),
                    TextInput::make('options.access_key_secret')
                        ->label(__('admin/sms_driver.form_fields.aliyun_options.access_key_secret.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.aliyun_options.access_key_secret.label'))
                        ->password()
                        ->revealable()
                        ->required()
                ]),
                TextInput::make('options.sign_name')
                    ->label(__('admin/sms_driver.form_fields.aliyun_options.sign_name.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.aliyun_options.sign_name.label'))
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Aliyun->value),
        ];
    }

    /**
     * 阿里云 Rest 配置表单
     * @return array<Component>
     */
    protected static function getAliyunRestOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.app_key')
                        ->label(__('admin/sms_driver.form_fields.aliyunrest_options.app_key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.aliyunrest_options.app_key.label'))
                        ->required(),
                    TextInput::make('options.app_secret_key')
                        ->label(__('admin/sms_driver.form_fields.aliyunrest_options.app_secret_key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.aliyunrest_options.app_secret_key.label'))
                        ->password()
                        ->revealable()
                        ->required()
                ]),
                TextInput::make('options.sign_name')
                    ->label(__('admin/sms_driver.form_fields.aliyunrest_options.sign_name.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.aliyunrest_options.sign_name.label'))
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::AliyunRest->value),
        ];
    }

    /**
     * 阿里云国际配置表单
     * @return array<Component>
     */
    protected static function getAliyunIntlOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.access_key_id')
                        ->label(__('admin/sms_driver.form_fields.aliyunintl_options.access_key_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.aliyunintl_options.access_key_secret.label'))
                        ->required(),
                    TextInput::make('options.access_key_secret')
                        ->label(__('admin/sms_driver.form_fields.aliyunintl_options.access_key_secret.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.aliyunintl_options.access_key_secret.label'))
                        ->password()
                        ->revealable()
                        ->required()
                ]),
                TextInput::make('options.sign_name')
                    ->label(__('admin/sms_driver.form_fields.aliyunintl_options.sign_name.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.aliyunintl_options.sign_name.label'))
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::AliyunIntl->value),
        ];
    }

    /**
     * 云片配置表单
     * @return array<Component>
     */
    protected static function getYunpianOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.api_key')
                    ->label(__('admin/sms_driver.form_fields.yunpian_options.api_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.yunpian_options.api_key.label'))
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('options.signature')
                    ->label(__('admin/sms_driver.form_fields.yunpian_options.signature.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.yunpian_options.signature.label'))
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Yunpian->value),
        ];
    }

    /**
     * Submail 配置表单
     * @return array<Component>
     */
    protected static function getSubmailOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.app_id')
                        ->label(__('admin/sms_driver.form_fields.submail_options.app_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.submail_options.app_id.label'))
                        ->required(),
                    TextInput::make('options.app_key')
                        ->label(__('admin/sms_driver.form_fields.submail_options.app_key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.submail_options.app_key.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.project')
                    ->label(__('admin/sms_driver.form_fields.submail_options.project.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.submail_options.project.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Submail->value),
        ];
    }

    /**
     * 螺丝帽配置表单
     * @return array<Component>
     */
    protected static function getLuosimaoOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                TextInput::make('options.api_key')
                    ->label(__('admin/sms_driver.form_fields.luosimao_options.api_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.luosimao_options.api_key.label'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Luosimao->value),
        ];
    }

    /**
     * 容联云通讯配置表单
     * @return array<Component>
     */
    protected static function getYuntongxunOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.app_id')
                        ->label(__('admin/sms_driver.form_fields.yuntongxun_options.app_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yuntongxun_options.app_id.label'))
                        ->required(),
                    TextInput::make('options.account_sid')
                        ->label(__('admin/sms_driver.form_fields.yuntongxun_options.account_sid.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yuntongxun_options.account_sid.label')),
                ]),
                TextInput::make('options.account_token')
                    ->label(__('admin/sms_driver.form_fields.yuntongxun_options.account_token.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.yuntongxun_options.account_token.label'))
                    ->password()
                    ->revealable(),
                Toggle::make('options.is_sub_account')
                    ->label(__('admin/sms_driver.form_fields.yuntongxun_options.is_sub_account.label'))
                    ->inline(false)
                    ->default(false),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Yuntongxun->value),
        ];
    }

    /**
     * 互亿无线配置表单
     * @return array<Component>
     */
    protected static function getHuyiOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.api_id')
                        ->label(__('admin/sms_driver.form_fields.huyi_options.api_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.huyi_options.api_id.label'))
                        ->required(),
                    TextInput::make('options.api_key')
                        ->label(__('admin/sms_driver.form_fields.huyi_options.api_key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.huyi_options.api_key.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.signature')
                    ->label(__('admin/sms_driver.form_fields.huyi_options.signature.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.huyi_options.signature.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Huyi->value),
        ];
    }

    /**
     * 聚合数据配置表单
     * @return array<Component>
     */
    protected static function getJuheOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                TextInput::make('options.app_key')
                    ->label(__('admin/sms_driver.form_fields.juhe_options.app_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.juhe_options.app_key.label'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Juhe->value),
        ];
    }

    /**
     * SendCloud 配置表单
     * @return array<Component>
     */
    protected static function getSendCloudOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.sms_user')
                        ->label(__('admin/sms_driver.form_fields.sendcloud_options.sms_user.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.sendcloud_options.sms_user.label'))
                        ->required(),
                    TextInput::make('options.sms_key')
                        ->label(__('admin/sms_driver.form_fields.sendcloud_options.sms_key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.sendcloud_options.sms_key.label'))
                        ->password()
                        ->revealable(),
                ]),
                Toggle::make('options.timestamp')
                    ->label(__('admin/sms_driver.form_fields.sendcloud_options.timestamp.label'))
                    ->inline(false)
                    ->default(false),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::SendCloud->value),
        ];
    }

    /**
     * Baidu 配置表单
     * @return array<Component>
     */
    protected static function getBaiduOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.ak')
                    ->label(__('admin/sms_driver.form_fields.baidu_options.ak.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.baidu_options.ak.label'))
                    ->required(),
                TextInput::make('options.sk')
                    ->label(__('admin/sms_driver.form_fields.baidu_options.sk.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.baidu_options.sk.label'))
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('options.invoke_id')
                    ->label(__('admin/sms_driver.form_fields.baidu_options.invoke_id.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.baidu_options.invoke_id.label')),
                TextInput::make('options.domain')
                    ->label(__('admin/sms_driver.form_fields.baidu_options.domain.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.baidu_options.domain.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Baidu->value),
        ];
    }

    /**
     * 华信短信平台配置表单
     * @return array<Component>
     */
    protected static function getHuaxinOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.user_id')
                        ->label(__('admin/sms_driver.form_fields.huaxin_options.user_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.huaxin_options.user_id.label'))
                        ->required(),
                    TextInput::make('options.password')
                        ->label(__('admin/sms_driver.form_fields.huaxin_options.password.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.huaxin_options.password.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                    TextInput::make('options.account')
                        ->label(__('admin/sms_driver.form_fields.huaxin_options.account.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.huaxin_options.account.label')),
                    TextInput::make('options.ip')
                        ->label(__('admin/sms_driver.form_fields.huaxin_options.ip.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.huaxin_options.ip.label')),
                ]),
                TextInput::make('options.ext_no')
                    ->label(__('admin/sms_driver.form_fields.huaxin_options.ext_no.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.huaxin_options.ext_no.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Huaxin->value),
        ];
    }

    /**
     * 253云通讯（创蓝）配置表单
     * @return array<Component>
     */
    protected static function getChuanglanOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Select::make('options.channel')
                    ->label(__('admin/sms_driver.form_fields.chuanglan_options.channel.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.chuanglan_options.channel.label'))
                    ->options([
                        ChuanglanGateway::CHANNEL_VALIDATE_CODE => __('admin/sms_driver.form_fields.chuanglan_options.channel.options.validate'),
                        ChuanglanGateway::CHANNEL_PROMOTION_CODE => __('admin/sms_driver.form_fields.chuanglan_options.channel.options.promotion'),
                    ])
                    ->native(false)
                    ->default(ChuanglanGateway::CHANNEL_VALIDATE_CODE)
                    ->required(),
                Grid::make()->schema([
                    TextInput::make('options.account')
                        ->label(__('admin/sms_driver.form_fields.chuanglan_options.account.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglan_options.account.label')),
                    TextInput::make('options.password')
                        ->label(__('admin/sms_driver.form_fields.chuanglan_options.password.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglan_options.password.label'))
                        ->password()
                        ->revealable(),
                    TextInput::make('options.intel_account')
                        ->label(__('admin/sms_driver.form_fields.chuanglan_options.intel_account.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglan_options.intel_account.label')),
                    TextInput::make('options.intel_password')
                        ->label(__('admin/sms_driver.form_fields.chuanglan_options.intel_password.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglan_options.intel_password.label'))
                        ->password()
                        ->revealable(),
                    TextInput::make('options.sign')
                        ->label(__('admin/sms_driver.form_fields.chuanglan_options.sign.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglan_options.sign.label')),
                    TextInput::make('options.unsubscribe')
                        ->label(__('admin/sms_driver.form_fields.chuanglan_options.unsubscribe.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglan_options.unsubscribe.label')),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Chuanglan->value),
        ];
    }

    /**
     * 创蓝云智配置表单
     * @return array<Component>
     */
    protected static function getChuanglanv1OptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.account')
                        ->label(__('admin/sms_driver.form_fields.chuanglanv1_options.account.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglanv1_options.account.label'))
                        ->required(),
                    TextInput::make('options.password')
                        ->label(__('admin/sms_driver.form_fields.chuanglanv1_options.password.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.chuanglanv1_options.password.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                Toggle::make('options.needstatus')
                    ->label(__('admin/sms_driver.form_fields.chuanglanv1_options.needstatus.label'))
                    ->inline(false)
                    ->default(false),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Chuanglanv1->value),
        ];
    }

    /**
     * 融云配置表单
     * @return array<Component>
     */
    protected static function getRongCloudOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.app_key')
                    ->label(__('admin/sms_driver.form_fields.rongcloud_options.app_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.rongcloud_options.app_key.label'))
                    ->required(),
                TextInput::make('options.app_secret')
                    ->label(__('admin/sms_driver.form_fields.rongcloud_options.app_secret.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.rongcloud_options.app_secret.label'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::RongCloud->value),
        ];
    }

    /**
     * 天毅无线配置表单
     * @return array<Component>
     */
    protected static function getTianyiwuxianOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.username')
                        ->label(__('admin/sms_driver.form_fields.tianyiwuxian_options.username.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.tianyiwuxian_options.username.label'))
                        ->required(),
                    TextInput::make('options.password')
                        ->label(__('admin/sms_driver.form_fields.tianyiwuxian_options.password.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.tianyiwuxian_options.password.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.gwid')
                    ->label(__('admin/sms_driver.form_fields.tianyiwuxian_options.gwid.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.tianyiwuxian_options.gwid.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Tianyiwuxian->value),
        ];
    }

    /**
     * Twilio 配置表单
     * @return array<Component>
     */
    protected static function getTwilioOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                TextInput::make('options.from')
                    ->label(__('admin/sms_driver.form_fields.twilio_options.from.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.twilio_options.from.label')),
                Grid::make()->schema([
                    TextInput::make('options.account_sid')
                        ->label(__('admin/sms_driver.form_fields.twilio_options.account_sid.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.twilio_options.account_sid.label'))
                        ->required(),
                    TextInput::make('options.token')
                        ->label(__('admin/sms_driver.form_fields.twilio_options.token.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.twilio_options.token.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Twilio->value),
        ];
    }

    /**
     * Tiniyo 配置表单
     * @return array<Component>
     */
    protected static function getTiniyoOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                TextInput::make('options.from')
                    ->label(__('admin/sms_driver.form_fields.tiniyo_options.from.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.tiniyo_options.from.label')),
                Grid::make()->schema([
                    TextInput::make('options.account_sid')
                        ->label(__('admin/sms_driver.form_fields.tiniyo_options.account_sid.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.tiniyo_options.account_sid.label'))
                        ->required(),
                    TextInput::make('options.token')
                        ->label(__('admin/sms_driver.form_fields.tiniyo_options.token.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.tiniyo_options.token.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Tiniyo->value),
        ];
    }

    /**
     * 腾讯云 SMS 配置表单
     * @return array<Component>
     */
    protected static function getQCloudOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.sdk_app_id')
                    ->label(__('admin/sms_driver.form_fields.qcloud_options.sdk_app_id.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.qcloud_options.sdk_app_id.label'))
                    ->required(),
                TextInput::make('options.secret_id')
                    ->label(__('admin/sms_driver.form_fields.qcloud_options.secret_id.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.qcloud_options.secret_id.label'))
                    ->required(),
                TextInput::make('options.secret_key')
                    ->label(__('admin/sms_driver.form_fields.qcloud_options.secret_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.qcloud_options.secret_key.label'))
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('options.sign_name')
                    ->label(__('admin/sms_driver.form_fields.qcloud_options.sign_name.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.qcloud_options.sign_name.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::QCloud->value),
        ];
    }

    /**
     * 华为云 SMS 配置表单
     * @return array<Component>
     */
    protected static function getHuaweiOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.endpoint')
                    ->label(__('admin/sms_driver.form_fields.huawei_options.endpoint.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.huawei_options.endpoint.label')),
                TextInput::make('options.app_key')
                    ->label(__('admin/sms_driver.form_fields.huawei_options.app_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.huawei_options.app_key.label'))
                    ->required(),
                TextInput::make('options.app_secret')
                    ->label(__('admin/sms_driver.form_fields.huawei_options.app_secret.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.huawei_options.app_secret.label'))
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('options.from.default')
                    ->label(__('admin/sms_driver.form_fields.huawei_options.from_default.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.huawei_options.from_default.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Huawei->value),
        ];
    }

    /**
     * 网易云信配置表单
     * @return array<Component>
     */
    protected static function getYunxinOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.app_key')
                        ->label(__('admin/sms_driver.form_fields.yunxin_options.app_key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yunxin_options.app_key.label'))
                        ->required(),
                    TextInput::make('options.app_secret')
                        ->label(__('admin/sms_driver.form_fields.yunxin_options.app_secret.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yunxin_options.app_secret.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                Toggle::make('options.need_up')
                    ->label(__('admin/sms_driver.form_fields.yunxin_options.need_up.label'))
                    ->inline(false)
                    ->default(false),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Yunxin->value),
        ];
    }

    /**
     * 云之讯配置表单
     * @return array<Component>
     */
    protected static function getYunzhixunOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                TextInput::make('options.sid')
                    ->label(__('admin/sms_driver.form_fields.yunzhixun_options.sid.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.yunzhixun_options.sid.label'))
                    ->required(),
                Grid::make()->schema([
                    TextInput::make('options.app_id')
                        ->label(__('admin/sms_driver.form_fields.yunzhixun_options.app_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yunzhixun_options.app_id.label'))
                        ->required(),
                    TextInput::make('options.token')
                        ->label(__('admin/sms_driver.form_fields.yunzhixun_options.token.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yunzhixun_options.token.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Yunzhixun->value),
        ];
    }

    /**
     * 凯信通配置表单
     * @return array<Component>
     */
    protected static function getKingttoOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                TextInput::make('options.userid')
                    ->label(__('admin/sms_driver.form_fields.kingtto_options.userid.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.kingtto_options.userid.label'))
                    ->required(),
                Grid::make()->schema([
                    TextInput::make('options.account')
                        ->label(__('admin/sms_driver.form_fields.kingtto_options.account.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.kingtto_options.account.label'))
                        ->required(),
                    TextInput::make('options.password')
                        ->label(__('admin/sms_driver.form_fields.kingtto_options.password.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.kingtto_options.password.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Kingtto->value),
        ];
    }

    /**
     * 七牛云配置表单
     * @return array<Component>
     */
    protected static function getQiniuOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.access_key')
                    ->label(__('admin/sms_driver.form_fields.qiniu_options.access_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.qiniu_options.access_key.label'))
                    ->required(),
                TextInput::make('options.secret_key')
                    ->label(__('admin/sms_driver.form_fields.qiniu_options.secret_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.qiniu_options.secret_key.label'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Qiniu->value),
        ];
    }

    /**
     * UCloud 配置表单
     * @return array<Component>
     */
    protected static function getUCloudOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.private_key')
                    ->label(__('admin/sms_driver.form_fields.ucloud_options.private_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.ucloud_options.private_key.label'))
                    ->required(),
                TextInput::make('options.public_key')
                    ->label(__('admin/sms_driver.form_fields.ucloud_options.public_key.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.ucloud_options.public_key.label'))
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('options.sig_content')
                    ->label(__('admin/sms_driver.form_fields.ucloud_options.sig_content.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.ucloud_options.sig_content.label')),
                TextInput::make('options.project_id')
                    ->label(__('admin/sms_driver.form_fields.ucloud_options.project_id.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.ucloud_options.project_id.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::UCloud->value),
        ];
    }

    /**
     * 短信宝配置表单
     * @return array<Component>
     */
    protected static function getSmsbaoOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.user')
                    ->label(__('admin/sms_driver.form_fields.smsbao_options.user.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.smsbao_options.user.label'))
                    ->required(),
                TextInput::make('options.password')
                    ->label(__('admin/sms_driver.form_fields.smsbao_options.password.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.smsbao_options.password.label'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Smsbao->value),
        ];
    }

    /**
     * 摩杜云配置表单
     * @return array<Component>
     */
    protected static function getModuyunOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('options.accesskey')
                    ->label(__('admin/sms_driver.form_fields.moduyun_options.accesskey.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.moduyun_options.accesskey.label'))
                    ->required(),
                TextInput::make('options.secretkey')
                    ->label(__('admin/sms_driver.form_fields.moduyun_options.secretkey.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.moduyun_options.secretkey.label'))
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('options.sign_id')
                    ->label(__('admin/sms_driver.form_fields.moduyun_options.sign_id.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.moduyun_options.sign_id.label')),
                Select::make('options.type')
                    ->label(__('admin/sms_driver.form_fields.moduyun_options.type.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.moduyun_options.type.label'))
                    ->options([
                        0 => __('admin/sms_driver.form_fields.moduyun_options.type.options.normal'),
                        1 => __('admin/sms_driver.form_fields.moduyun_options.type.options.marketing'),
                    ])
                    ->native(false)
                    ->default(0)
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Moduyun->value),
        ];
    }

    /**
     * 融合云（助通）配置表单
     * @return array<Component>
     */
    protected static function getRongheyunOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.username')
                        ->label(__('admin/sms_driver.form_fields.rongheyun_options.username.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.rongheyun_options.username.label'))
                        ->required(),
                    TextInput::make('options.password')
                        ->label(__('admin/sms_driver.form_fields.rongheyun_options.password.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.rongheyun_options.password.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.signature')
                    ->label(__('admin/sms_driver.form_fields.rongheyun_options.signature.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.rongheyun_options.signature.label'))
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Rongheyun->value),
        ];
    }

    /**
     * 蜘蛛云配置表单
     * @return array<Component>
     */
    protected static function getZzyunOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.user_id')
                        ->label(__('admin/sms_driver.form_fields.zzyun_options.user_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.zzyun_options.user_id.label'))
                        ->required(),
                    TextInput::make('options.secret')
                        ->label(__('admin/sms_driver.form_fields.zzyun_options.secret.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.zzyun_options.secret.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.sign_name')
                    ->label(__('admin/sms_driver.form_fields.zzyun_options.sign_name.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.zzyun_options.sign_name.label'))
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Zzyun->value),
        ];
    }

    /**
     * 融合云信配置表单
     * @return array<Component>
     */
    protected static function getMaapOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.cpcode')
                        ->label(__('admin/sms_driver.form_fields.maap_options.cpcode.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.maap_options.cpcode.label'))
                        ->required(),
                    TextInput::make('options.key')
                        ->label(__('admin/sms_driver.form_fields.maap_options.key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.maap_options.key.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.excode')
                    ->label(__('admin/sms_driver.form_fields.maap_options.excode.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.maap_options.excode.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Maap->value),
        ];
    }

    /**
     * 天瑞云配置表单
     * @return array<Component>
     */
    protected static function getTinreeOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.accesskey')
                        ->label(__('admin/sms_driver.form_fields.tinree_options.accesskey.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.tinree_options.accesskey.label'))
                        ->required(),
                    TextInput::make('options.secret')
                        ->label(__('admin/sms_driver.form_fields.tinree_options.secret.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.tinree_options.secret.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.sign')
                    ->label(__('admin/sms_driver.form_fields.tinree_options.sign.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.tinree_options.sign.label'))
                    ->required(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Tinree->value),
        ];
    }

    /**
     * 时代互联配置表单
     * @return array<Component>
     */
    protected static function getNowcnOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.key')
                        ->label(__('admin/sms_driver.form_fields.nowcn_options.key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.nowcn_options.key.label'))
                        ->required(),
                    TextInput::make('options.secret')
                        ->label(__('admin/sms_driver.form_fields.nowcn_options.secret.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.nowcn_options.secret.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
                TextInput::make('options.api_type')
                    ->label(__('admin/sms_driver.form_fields.nowcn_options.api_type.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.nowcn_options.api_type.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Nowcn->value),
        ];
    }

    /**
     * 火山引擎配置表单
     * @return array<Component>
     */
    protected static function getVolcengineOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.access_key_id')
                        ->label(__('admin/sms_driver.form_fields.volcengine_options.access_key_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.volcengine_options.access_key_id.label'))
                        ->required(),
                    TextInput::make('options.access_key_secret')
                        ->label(__('admin/sms_driver.form_fields.volcengine_options.access_key_secret.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.volcengine_options.access_key_secret.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                    TextInput::make('options.region_id')
                        ->label(__('admin/sms_driver.form_fields.volcengine_options.region_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.volcengine_options.region_id.label')),
                    TextInput::make('options.sign_name')
                        ->label(__('admin/sms_driver.form_fields.volcengine_options.sign_name.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.volcengine_options.sign_name.label')),
                ]),
                TextInput::make('options.sms_account')
                    ->label(__('admin/sms_driver.form_fields.volcengine_options.sms_account.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.volcengine_options.sms_account.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Volcengine->value),
        ];
    }

    /**
     * 移动云MAS（黑名单模式）配置表单
     * @return array<Component>
     */
    protected static function getYidongmasblackOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    TextInput::make('options.ap_id')
                        ->label(__('admin/sms_driver.form_fields.yidongmasblack_options.ap_id.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yidongmasblack_options.ap_id.label'))
                        ->required(),
                    TextInput::make('options.ec_name')
                        ->label(__('admin/sms_driver.form_fields.yidongmasblack_options.ec_name.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yidongmasblack_options.ec_name.label'))
                        ->required(),
                    TextInput::make('options.secret_key')
                        ->label(__('admin/sms_driver.form_fields.yidongmasblack_options.secret_key.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yidongmasblack_options.secret_key.label'))
                        ->password()
                        ->revealable()
                        ->required(),
                    TextInput::make('options.sign')
                        ->label(__('admin/sms_driver.form_fields.yidongmasblack_options.sign.label'))
                        ->placeholder(__('admin/sms_driver.form_fields.yidongmasblack_options.sign.label')),
                ]),
                TextInput::make('options.add_serial')
                    ->label(__('admin/sms_driver.form_fields.yidongmasblack_options.add_serial.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.yidongmasblack_options.add_serial.label')),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SmsProvider::Yidongmasblack->value),
        ];
    }

    /**
     * 短信模板配置
     * @return Component
     */
    protected static function getOptionTemplatesFormComponent(): Component
    {
        return Repeater::make('options.templates')
            ->label(__('admin/sms_driver.form_fields.options.templates.label'))
            ->columns(2)
            ->schema([
                Hidden::make('event'),
                TextInput::make('template')
                    ->label(fn(Get $get) => __("admin/sms_driver.events.{$get('event')}") . __('admin/sms_driver.form_fields.options.templates.template.label'))
                    ->placeholder(__('admin/sms_driver.form_fields.options.templates.template.placeholder')),
                Textarea::make('content')
                    ->label(__('admin/sms_driver.form_fields.options.templates.content.label'))
                    ->rows(1)
                    ->placeholder(__('admin/sms_driver.form_fields.options.templates.content.placeholder')),
            ])
            ->reorderableWithDragAndDrop(false)
            ->addable(false)
            ->deletable(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsDrivers::route('/'),
            'create' => Pages\CreateSmsDriver::route('/create'),
            'edit' => Pages\EditSmsDriver::route('/{record}/edit'),
        ];
    }
}
