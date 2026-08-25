<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\ScanDriverResource\Pages;
use App\Models\Driver;
use App\ScanProvider;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ScanDriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 25;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.driver');
    }

    public static function getModelLabel(): string
    {
        return __('admin/scan_driver.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/scan_driver.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', DriverType::Scan);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make()->schema([
                    self::getNameFormComponent(),
                    self::getOptionProviderFormComponent(),
                ]),
                self::getIntroFormComponent(),
                self::getOptionsIsSyncFormComponent(),
                self::getOptionsViolationStoreDirFormComponent(),
                ...self::getAliyunV1OptionFormComponents(),
                ...self::getAliyunV2OptionFormComponents(),
                ...self::getTencentOptionFormComponents(),
                ...self::getNsfwJSOptionFormComponents(),
                ...self::getModerateContentOptionFormComponents(),
            ]),
        ]);
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/scan_driver.form_fields.name.label'))
            ->placeholder(__('admin/scan_driver.form_fields.name.placeholder'))
            ->minLength(1)
            ->maxLength(200)
            ->required();
    }

    /**
     * 驱动器
     * @return Component
     */
    protected static function getOptionProviderFormComponent(): Component
    {
        return Select::make('options.provider')
            ->label(__('admin/scan_driver.form_fields.options.provider.label'))
            ->placeholder(__('admin/scan_driver.form_fields.options.provider.placeholder'))
            ->options(AppService::getAllScanProviders())
            ->live()
            ->required()
            ->default(ScanProvider::AliyunV1->value)
            ->native(false);
    }

    /**
     * 简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/scan_driver.form_fields.intro.label'))
            ->placeholder(__('admin/scan_driver.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 是否同步审核
     * @return Component
     */
    protected static function getOptionsIsSyncFormComponent(): Component
    {
        return Toggle::make('options.is_sync')
            ->label(__('admin/scan_driver.form_fields.options_is_sync.label'))
            ->helperText(__('admin/scan_driver.form_fields.options_is_sync.helper_text'))
            ->default(false);
    }

    /**
     * 违规图片转移储存目录
     * @return Component
     */
    protected static function getOptionsViolationStoreDirFormComponent(): Component
    {
        return TextInput::make('options.violation_store_dir')
            ->label(__('admin/scan_driver.form_fields.options_violation_store_dir.label'))
            ->placeholder(__('admin/scan_driver.form_fields.options_violation_store_dir.placeholder'))
            ->helperText(__('admin/scan_driver.form_fields.options_violation_store_dir.helper_text'))
            ->minLength(1)
            ->maxLength(600)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 阿里云图片安全V1配置表单
     * @return array<Component>
     */
    protected static function getAliyunV1OptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getAliyunV1OptionAccessKeyIdFormComponent(),
                    self::getAliyunV1OptionAccessKeySecretFormComponent(),
                    self::getAliyunV1OptionRegionIdFormComponent(),
                    self::getAliyunV1OptionBizTypeFormComponent(),
                ]),
                self::getAliyunV1OptionScenesFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === ScanProvider::AliyunV1->value),
        ];
    }

    /**
     * 阿里云图片安全V1 AccessKeyID
     * @return Component
     */
    protected static function getAliyunV1OptionAccessKeyIdFormComponent(): Component
    {
        return TextInput::make('options.access_key_id')
            ->label(__('admin/scan_driver.form_fields.aliyun_v1_options.access_key_id.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v1_options.access_key_id.placeholder'))
            ->required();
    }

    /**
     * 阿里云图片安全V1 AccessKeySecret
     * @return Component
     */
    protected static function getAliyunV1OptionAccessKeySecretFormComponent(): Component
    {
        return TextInput::make('options.access_key_secret')
            ->label(__('admin/scan_driver.form_fields.aliyun_v1_options.access_key_secret.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v1_options.access_key_secret.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 阿里云图片安全V1 Region
     * @return Component
     */
    protected static function getAliyunV1OptionRegionIdFormComponent(): Component
    {
        return TextInput::make('options.region_id')
            ->label(__('admin/scan_driver.form_fields.aliyun_v1_options.region_id.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v1_options.region_id.placeholder'))
            ->default('ap-shanghai')
            ->required();
    }

    /**
     * 阿里云图片安全V1 BizType
     * @return Component
     */
    protected static function getAliyunV1OptionBizTypeFormComponent(): Component
    {
        return TextInput::make('options.biz_type')
            ->label(__('admin/scan_driver.form_fields.aliyun_v1_options.biz_type.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v1_options.biz_type.placeholder'));
    }

    /**
     * 阿里云图片安全V1 检测场景
     * @return Component
     */
    protected static function getAliyunV1OptionScenesFormComponent(): Component
    {
        return CheckboxList::make('options.scenes')
            ->label(__('admin/scan_driver.form_fields.aliyun_v1_options.scenes.label'))
            ->options([
                'porn' => __('admin/scan_driver.form_fields.aliyun_v1_options.scenes.options.porn'),
                'terrorism' => __('admin/scan_driver.form_fields.aliyun_v1_options.scenes.options.terrorism'),
                'ad' => __('admin/scan_driver.form_fields.aliyun_v1_options.scenes.options.ad'),
                'qrcode' => __('admin/scan_driver.form_fields.aliyun_v1_options.scenes.options.qrcode'),
                'live' => __('admin/scan_driver.form_fields.aliyun_v1_options.scenes.options.live'),
                'logo' => __('admin/scan_driver.form_fields.aliyun_v1_options.scenes.options.logo'),
            ])
            ->bulkToggleable()
            ->required();
    }

    /**
     * 阿里云图片安全增强版配置表单
     * @return array<Component>
     */
    protected static function getAliyunV2OptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getAliyunV2OptionEndpointFormComponent(),
                    self::getAliyunV2OptionServiceFormComponent(),
                    self::getAliyunV2OptionAccessKeyIdFormComponent(),
                    self::getAliyunV2OptionAccessKeySecretFormComponent(),
                    self::getAliyunV2OptionHttpProxyFormComponent(),
                    self::getAliyunV2OptionHttpsProxyFormComponent(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === ScanProvider::AliyunV2->value),
        ];
    }

    /**
     * 阿里云图片安全增强版 Endpoint
     * @return Component
     */
    protected static function getAliyunV2OptionEndpointFormComponent(): Component
    {
        return TextInput::make('options.endpoint')
            ->label(__('admin/scan_driver.form_fields.aliyun_v2_options.endpoint.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v2_options.endpoint.placeholder'))
            ->required();
    }

    /**
     * 阿里云图片安全增强版 Service
     * @return Component
     */
    protected static function getAliyunV2OptionServiceFormComponent(): Component
    {
        return TextInput::make('options.service')
            ->label(__('admin/scan_driver.form_fields.aliyun_v2_options.service.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v2_options.service.placeholder'))
            ->default('baselineCheck')
            ->required();
    }

    /**
     * 阿里云图片安全增强版 AccessKeyID
     * @return Component
     */
    protected static function getAliyunV2OptionAccessKeyIdFormComponent(): Component
    {
        return TextInput::make('options.access_key_id')
            ->label(__('admin/scan_driver.form_fields.aliyun_v2_options.access_key_id.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v2_options.access_key_id.placeholder'))
            ->required();
    }

    /**
     * 阿里云图片安全增强版 AccessKeySecret
     * @return Component
     */
    protected static function getAliyunV2OptionAccessKeySecretFormComponent(): Component
    {
        return TextInput::make('options.access_key_secret')
            ->label(__('admin/scan_driver.form_fields.aliyun_v2_options.access_key_secret.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v2_options.access_key_secret.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 阿里云图片安全增强版 httpProxy
     * @return Component
     */
    protected static function getAliyunV2OptionHttpProxyFormComponent(): Component
    {
        return TextInput::make('options.http_proxy')
            ->label(__('admin/scan_driver.form_fields.aliyun_v2_options.http_proxy.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v2_options.http_proxy.placeholder'))
            ->url();
    }

    /**
     * 阿里云图片安全增强版 httpsProxy
     * @return Component
     */
    protected static function getAliyunV2OptionHttpsProxyFormComponent(): Component
    {
        return TextInput::make('options.https_proxy')
            ->label(__('admin/scan_driver.form_fields.aliyun_v2_options.https_proxy.label'))
            ->placeholder(__('admin/scan_driver.form_fields.aliyun_v2_options.https_proxy.placeholder'))
            ->url();
    }

    /**
     * 腾讯云图片安全配置表单
     * @return array<Component>
     */
    protected static function getTencentOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                self::getTencentOptionEndpointFormComponent(),
                Grid::make()->schema([
                    self::getTencentOptionSecretIdFormComponent(),
                    self::getTencentOptionSecretKeyFormComponent(),
                    self::getTencentOptionRegionIdFormComponent(),
                    self::getTencentOptionBizTypeFormComponent(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === ScanProvider::Tencent->value),
        ];
    }

    /**
     * 腾讯云图片安全 Endpoint
     * @return Component
     */
    protected static function getTencentOptionEndpointFormComponent(): Component
    {
        return TextInput::make('options.endpoint')
            ->label(__('admin/scan_driver.form_fields.tencent_options.endpoint.label'))
            ->placeholder(__('admin/scan_driver.form_fields.tencent_options.endpoint.placeholder'))
            ->required();
    }

    /**
     * 腾讯云图片安全 SecretId
     * @return Component
     */
    protected static function getTencentOptionSecretIdFormComponent(): Component
    {
        return TextInput::make('options.secret_id')
            ->label(__('admin/scan_driver.form_fields.tencent_options.secret_id.label'))
            ->placeholder(__('admin/scan_driver.form_fields.tencent_options.secret_id.placeholder'))
            ->required();
    }

    /**
     * 腾讯云图片安全 SecretKey
     * @return Component
     */
    protected static function getTencentOptionSecretKeyFormComponent(): Component
    {
        return TextInput::make('options.secret_key')
            ->label(__('admin/scan_driver.form_fields.tencent_options.secret_key.label'))
            ->placeholder(__('admin/scan_driver.form_fields.tencent_options.secret_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 腾讯云图片安全 Region
     * @return Component
     */
    protected static function getTencentOptionRegionIdFormComponent(): Component
    {
        return TextInput::make('options.region_id')
            ->label(__('admin/scan_driver.form_fields.tencent_options.region_id.label'))
            ->placeholder(__('admin/scan_driver.form_fields.tencent_options.region_id.placeholder'))
            ->default('ap-beijing')
            ->required();
    }

    /**
     * 腾讯云图片安全 BizType
     * @return Component
     */
    protected static function getTencentOptionBizTypeFormComponent(): Component
    {
        return TextInput::make('options.biz_type')
            ->label(__('admin/scan_driver.form_fields.tencent_options.biz_type.label'))
            ->placeholder(__('admin/scan_driver.form_fields.tencent_options.biz_type.placeholder'));
    }

    /**
     * NsfwJS 配置表单
     * @return array<Component>
     */
    protected static function getNsfwJSOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getNsfwJsOptionEndpointFormComponent(),
                    self::getNsfwJsOptionAttrNameFormComponent(),
                ]),
                self::getNsfwJsOptionThresholdFormComponent(),
                self::getNsfwJsOptionScenesFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === ScanProvider::NsfwJS->value),
        ];
    }

    /**
     * NsfwJS Endpoint
     * @return Component
     */
    protected static function getNsfwJsOptionEndpointFormComponent(): Component
    {
        return TextInput::make('options.endpoint')
            ->label(__('admin/scan_driver.form_fields.nsfw_js_options.endpoint.label'))
            ->placeholder(__('admin/scan_driver.form_fields.nsfw_js_options.endpoint.placeholder'))
            ->required();
    }

    /**
     * NsfwJS 属性名称
     * @return Component
     */
    protected static function getNsfwJsOptionAttrNameFormComponent(): Component
    {
        return TextInput::make('options.attr_name')
            ->label(__('admin/scan_driver.form_fields.nsfw_js_options.attr_name.label'))
            ->placeholder(__('admin/scan_driver.form_fields.nsfw_js_options.attr_name.placeholder'))
            ->default('image')
            ->required();
    }

    /**
     * NsfwJS 阈值
     * @return Component
     */
    protected static function getNsfwJsOptionThresholdFormComponent(): Component
    {
        return TextInput::make('options.threshold')
            ->label(__('admin/scan_driver.form_fields.nsfw_js_options.threshold.label'))
            ->placeholder(__('admin/scan_driver.form_fields.nsfw_js_options.threshold.placeholder'))
            ->numeric()
            ->default(60)
            ->required();
    }

    /**
     * Nsfw 检测场景
     * @return Component
     */
    protected static function getNsfwJsOptionScenesFormComponent(): Component
    {
        return CheckboxList::make('options.scenes')
            ->label(__('admin/scan_driver.form_fields.nsfw_js_options.scenes.label'))
            ->options([
                'drawing' => __('admin/scan_driver.form_fields.nsfw_js_options.scenes.options.drawing'),
                'hentai' => __('admin/scan_driver.form_fields.nsfw_js_options.scenes.options.hentai'),
                'neutral' => __('admin/scan_driver.form_fields.nsfw_js_options.scenes.options.neutral'),
                'porn' => __('admin/scan_driver.form_fields.nsfw_js_options.scenes.options.porn'),
                'sexy' => __('admin/scan_driver.form_fields.nsfw_js_options.scenes.options.sexy'),
            ])
            ->bulkToggleable()
            ->required();
    }

    /**
     * ModerateContent 配置表单
     * @return array<Component>
     */
    protected static function getModerateContentOptionFormComponents(): array
    {
        return [
            self::getModerateContentOptionApiKeyFormComponent()
                ->visible(fn(Get $get): bool => $get('options.provider') === ScanProvider::ModerateContent->value),
        ];
    }

    /**
     * ModerateContent ApiKey
     * @return Component
     */
    protected static function getModerateContentOptionApiKeyFormComponent(): Component
    {
        return TextInput::make('options.api_key')
            ->label(__('admin/scan_driver.form_fields.moderate_content_options.api_key.label'))
            ->placeholder(__('admin/scan_driver.form_fields.moderate_content_options.api_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScanDrivers::route('/'),
            'create' => Pages\CreateScanDriver::route('/create'),
            'edit' => Pages\EditScanDriver::route('/{record}/edit'),
        ];
    }
}
