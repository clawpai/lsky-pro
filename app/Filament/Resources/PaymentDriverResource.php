<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\PaymentDriverResource\Pages;
use App\Models\Driver;
use App\PaymentChannel;
use App\PaymentProvider;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Yansongda\Pay\Pay;

class PaymentDriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 22;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.driver');
    }

    public static function getModelLabel(): string
    {
        return __('admin/payment_driver.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/payment_driver.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', DriverType::Payment);
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
                ...self::getAlipayOptionFormComponents(),
                ...self::getWechatOptionFormComponents(),
                ...self::getUniPayOptionFormComponents(),
                ...self::getPayPalOptionFormComponents(),
                ...self::getEPayOptionFormComponents(),
                self::getOptionNotifyUrlFormComponent(),
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
            ->label(__('admin/payment_driver.form_fields.name.label'))
            ->placeholder(__('admin/payment_driver.form_fields.name.placeholder'))
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
            ->label(__('admin/payment_driver.form_fields.options.provider.label'))
            ->placeholder(__('admin/payment_driver.form_fields.options.provider.placeholder'))
            ->options(AppService::getAllPaymentProviders())
            ->live()
            ->required()
            ->default(PaymentProvider::Alipay->value)
            ->native(false);
    }

    /**
     * 简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/payment_driver.form_fields.intro.label'))
            ->placeholder(__('admin/payment_driver.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 支付宝支付配置表单
     * @return array
     */
    protected static function getAlipayOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getAlipayOptionModeFormComponent(),
                    self::getAlipayOptionAppIdFormComponent(),
                    self::getAlipayOptionAppAuthTokenFormComponent(),
                    self::getAlipayOptionServiceProviderIdFormComponent(),
                ]),
                self::getAlipayOptionAppSecretCertFormComponent(),
                Grid::make()->schema([
                    self::getAlipayOptionAlipayPublicCertPathFormComponent(),
                    self::getAlipayOptionAlipayRootCertPathFormComponent(),
                ]),
                self::getAlipayOptionAppPublicCertPathFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === PaymentProvider::Alipay->value),
        ];
    }

    /**
     * 支付宝配置 模式
     * @return Component
     */
    protected static function getAlipayOptionModeFormComponent(): Component
    {
        return Select::make('options.mode')
            ->label(__('admin/payment_driver.form_fields.alipay_options.mode.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.mode.placeholder'))
            ->options([
                Pay::MODE_NORMAL => __('admin/payment_driver.form_fields.alipay_options.mode.options.normal'),
                Pay::MODE_SANDBOX => __('admin/payment_driver.form_fields.alipay_options.mode.options.sandbox'),
                Pay::MODE_SERVICE => __('admin/payment_driver.form_fields.alipay_options.mode.options.service'),
            ])
            ->live()
            ->default(Pay::MODE_NORMAL)
            ->required();
    }

    /**
     * 支付宝配置 AppId
     * @return Component
     */
    protected static function getAlipayOptionAppIdFormComponent(): Component
    {
        return TextInput::make('options.app_id')
            ->label(__('admin/payment_driver.form_fields.alipay_options.app_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.app_id.placeholder'))
            ->required();
    }

    /**
     * 支付宝配置 第三方应用授权 token
     * @return Component
     */
    protected static function getAlipayOptionAppAuthTokenFormComponent(): Component
    {
        return TextInput::make('options.app_auth_token')
            ->label(__('admin/payment_driver.form_fields.alipay_options.app_auth_token.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.app_auth_token.placeholder'));
    }

    /**
     * 支付宝配置 服务提供者 ID
     * @return Component
     */
    protected static function getAlipayOptionServiceProviderIdFormComponent(): Component
    {
        return TextInput::make('options.service_provider_id')
            ->label(__('admin/payment_driver.form_fields.alipay_options.service_provider_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.service_provider_id.placeholder'))
            ->required(fn(Get $get): bool => $get('options.mode') == Pay::MODE_SERVICE);
    }

    /**
     * 支付宝配置 应用私钥
     * @return Component
     */
    protected static function getAlipayOptionAppSecretCertFormComponent(): Component
    {
        return Textarea::make('options.app_secret_cert')
            ->label(__('admin/payment_driver.form_fields.alipay_options.app_secret_cert.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.app_secret_cert.placeholder'))
            ->required();
    }

    /**
     * 支付宝配置 支付宝公钥证书
     * @return Component
     */
    protected static function getAlipayOptionAlipayPublicCertPathFormComponent(): Component
    {
        return FileUpload::make('options.alipay_public_cert_path')
            ->label(__('admin/payment_driver.form_fields.alipay_options.alipay_public_cert_path.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.alipay_public_cert_path.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.alipay_options.alipay_public_cert_path.helper_text'))
            ->previewable(false)
            ->required();
    }

    /**
     * 支付宝配置 支付宝根证书
     * @return Component
     */
    protected static function getAlipayOptionAlipayRootCertPathFormComponent(): Component
    {
        return FileUpload::make('options.alipay_root_cert_path')
            ->label(__('admin/payment_driver.form_fields.alipay_options.alipay_root_cert_path.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.alipay_root_cert_path.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.alipay_options.alipay_root_cert_path.helper_text'))
            ->previewable(false)
            ->required();
    }

    /**
     * 支付宝配置 应用公钥证书
     * @return Component
     */
    protected static function getAlipayOptionAppPublicCertPathFormComponent(): Component
    {
        return FileUpload::make('options.app_public_cert_path')
            ->label(__('admin/payment_driver.form_fields.alipay_options.app_public_cert_path.label'))
            ->placeholder(__('admin/payment_driver.form_fields.alipay_options.app_public_cert_path.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.alipay_options.app_public_cert_path.helper_text'))
            ->previewable(false)
            ->required();
    }

    /**
     * 微信支付配置表单
     * @return array
     */
    protected static function getWechatOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getWechatOptionModeFormComponent(),
                    self::getWechatOptionMchIdFormComponent(),
                ]),
                Grid::make()->schema([
                    self::getWechatOptionMchSecretCertFormComponent(),
                    self::getWechatOptionMchPublicCertPathFormComponent(),
                ]),
                self::getWechatOptionWechatPublicCertIdFormComponent(),
                self::getWechatOptionWechatPublicCertPathFormComponent(),
                Grid::make()->schema([
                    self::getWechatOptionMchSecretKeyV2FormComponent(),
                    self::getWechatOptionMchSecretKeyFormComponent(),
                    self::getWechatOptionMpAppIdFormComponent(),
                    self::getWechatOptionMiniAppIdFormComponent(),
                ]),
                self::getWechatOptionAppAppIdFormComponent(),
                Grid::make()->schema([
                    self::getWechatOptionSubMpAppIdFormComponent(),
                    self::getWechatOptionSubAppIdFormComponent(),
                    self::getWechatOptionSubMiniAppIdFormComponent(),
                    self::getWechatOptionSubMchIdFormComponent(),
                ])->visible(fn(Get $get): bool => $get('options.mode') == Pay::MODE_SERVICE),
            ])->visible(fn(Get $get): bool => $get('options.provider') === PaymentProvider::Wechat->value),
        ];
    }

    /**
     * 微信配置 模式
     * @return Component
     */
    protected static function getWechatOptionModeFormComponent(): Component
    {
        return Select::make('options.mode')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mode.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mode.placeholder'))
            ->options([
                Pay::MODE_NORMAL => __('admin/payment_driver.form_fields.wechat_options.mode.options.normal'),
                Pay::MODE_SERVICE => __('admin/payment_driver.form_fields.wechat_options.mode.options.service'),
            ])
            ->live()
            ->default(Pay::MODE_NORMAL)
            ->required();
    }

    /**
     * 微信配置 商户号
     * @return Component
     */
    protected static function getWechatOptionMchIdFormComponent(): Component
    {
        return TextInput::make('options.mch_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mch_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mch_id.placeholder'))
            ->required();
    }

    /**
     * 微信配置 商户私钥证书
     * @return Component
     */
    protected static function getWechatOptionMchSecretCertFormComponent(): Component
    {
        return FileUpload::make('options.mch_secret_cert')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mch_secret_cert.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mch_secret_cert.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.wechat_options.mch_secret_cert.helper_text'))
            ->required();
    }

    /**
     * 微信配置 商户公钥证书
     * @return Component
     */
    protected static function getWechatOptionMchPublicCertPathFormComponent(): Component
    {
        return FileUpload::make('options.mch_public_cert_path')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mch_public_cert_path.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mch_public_cert_path.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.wechat_options.mch_public_cert_path.helper_text'))
            ->previewable(false)
            ->required();
    }

    /**
     * 微信配置 微信平台公钥证书ID
     * @return Component
     */
    protected static function getWechatOptionWechatPublicCertIdFormComponent(): Component
    {
        return TextInput::make('options.wechat_public_cert_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.wechat_public_cert_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.wechat_public_cert_id.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.wechat_options.wechat_public_cert_id.helper_text'))
            ->required();
    }

    /**
     * 微信配置 微信平台公钥证书
     * @return Component
     */
    protected static function getWechatOptionWechatPublicCertPathFormComponent(): Component
    {
        return FileUpload::make('options.wechat_public_cert_path')
            ->label(__('admin/payment_driver.form_fields.wechat_options.wechat_public_cert_path.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.wechat_public_cert_path.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.wechat_options.wechat_public_cert_path.helper_text'))
            ->previewable(false)
            ->required();
    }

    /**
     * 微信配置 商户密钥（V2）
     * @return Component
     */
    protected static function getWechatOptionMchSecretKeyV2FormComponent(): Component
    {
        return TextInput::make('options.mch_secret_key_v2')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mch_secret_key_v2.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mch_secret_key_v2.placeholder'))
            ->password()
            ->revealable();
    }

    /**
     * 微信配置 商户密钥（V3）
     * @return Component
     */
    protected static function getWechatOptionMchSecretKeyFormComponent(): Component
    {
        return TextInput::make('options.mch_secret_key')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mch_secret_key.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mch_secret_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 微信配置 公众号 AppID
     * @return Component
     */
    protected static function getWechatOptionMpAppIdFormComponent(): Component
    {
        return TextInput::make('options.mp_app_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mp_app_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mp_app_id.placeholder'));
    }

    /**
     * 微信配置 小程序 App ID
     * @return Component
     */
    protected static function getWechatOptionMiniAppIdFormComponent(): Component
    {
        return TextInput::make('options.mini_app_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.mini_app_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.mini_app_id.placeholder'));
    }

    /**
     * 微信配置 APP AppID
     * @return Component
     */
    protected static function getWechatOptionAppAppIdFormComponent(): Component
    {
        return TextInput::make('options.app_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.app_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.app_id.placeholder'));
    }

    /**
     * 微信配置 子公众号 AppID
     * @return Component
     */
    protected static function getWechatOptionSubMpAppIdFormComponent(): Component
    {
        return TextInput::make('options.sub_mp_app_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.sub_mp_app_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.sub_mp_app_id.placeholder'));
    }

    /**
     * 微信配置 子 APP 的 App ID
     * @return Component
     */
    protected static function getWechatOptionSubAppIdFormComponent(): Component
    {
        return TextInput::make('options.sub_app_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.sub_app_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.sub_app_id.placeholder'));
    }

    /**
     * 微信配置 子小程序 App ID
     * @return Component
     */
    protected static function getWechatOptionSubMiniAppIdFormComponent(): Component
    {
        return TextInput::make('options.sub_mini_app_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.sub_mini_app_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.sub_mini_app_id.placeholder'));
    }

    /**
     * 微信配置 子商户号
     * @return Component
     */
    protected static function getWechatOptionSubMchIdFormComponent(): Component
    {
        return TextInput::make('options.sub_mch_id')
            ->label(__('admin/payment_driver.form_fields.wechat_options.sub_mch_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.wechat_options.sub_mch_id.placeholder'));
    }

    /**
     * 银联支付配置表单
     * @return array
     */
    protected static function getUniPayOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getUnipayOptionMchIdFormComponent(),
                    self::getUnipayOptionMchSecretKeyFormComponent(),
                    self::getUnipayOptionUnipayPublicCertPathFormComponent(),
                    self::getUnipayOptionMchCertPathFormComponent(),
                ]),
                self::getUnipayOptionMchCertPasswordFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === PaymentProvider::UniPay->value),
        ];
    }

    /**
     * 银联配置 商户号
     * @return Component
     */
    protected static function getUnipayOptionMchIdFormComponent(): Component
    {
        return TextInput::make('options.mch_id')
            ->label(__('admin/payment_driver.form_fields.unipay_options.mch_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.unipay_options.mch_id.placeholder'))
            ->required();
    }

    /**
     * 银联配置 商户密钥
     * @return Component
     */
    protected static function getUnipayOptionMchSecretKeyFormComponent(): Component
    {
        return TextInput::make('options.mch_secret_key')
            ->label(__('admin/payment_driver.form_fields.unipay_options.mch_secret_key.label'))
            ->placeholder(__('admin/payment_driver.form_fields.unipay_options.mch_secret_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 银联配置 银联公钥证书
     * @return Component
     */
    protected static function getUnipayOptionUnipayPublicCertPathFormComponent(): Component
    {
        return FileUpload::make('options.unipay_public_cert_path')
            ->label(__('admin/payment_driver.form_fields.unipay_options.unipay_public_cert_path.label'))
            ->placeholder(__('admin/payment_driver.form_fields.unipay_options.unipay_public_cert_path.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.unipay_options.unipay_public_cert_path.helper_text'))
            ->previewable(false);
    }

    /**
     * 微信配置 商户公私钥
     * @return Component
     */
    protected static function getUnipayOptionMchCertPathFormComponent(): Component
    {
        return FileUpload::make('options.mch_cert_path')
            ->label(__('admin/payment_driver.form_fields.unipay_options.mch_cert_path.label'))
            ->placeholder(__('admin/payment_driver.form_fields.unipay_options.mch_cert_path.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.unipay_options.mch_cert_path.helper_text'))
            ->previewable(false);
    }

    /**
     * 银联配置 商户公私钥密码
     * @return Component
     */
    protected static function getUnipayOptionMchCertPasswordFormComponent(): Component
    {
        return TextInput::make('options.mch_cert_password')
            ->label(__('admin/payment_driver.form_fields.unipay_options.mch_cert_password.label'))
            ->placeholder(__('admin/payment_driver.form_fields.unipay_options.mch_cert_password.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * PayPal 配置表单
     * @return array
     */
    protected static function getPayPalOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                self::getPayPalOptionModeFormComponent(),
                Grid::make()->schema([
                    self::getPayPalOptionClientIdFormComponent(),
                    self::getPayPalOptionClientSecretFormComponent(),
                    self::getPayPalOptionCurrencyFormComponent(),
                    self::getPayPalOptionWebhookIdFormComponent(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === PaymentProvider::Paypal->value)
        ];
    }

    /**
     * PayPal 模式
     * @return Component
     */
    protected static function getPayPalOptionModeFormComponent(): Component
    {
        return Select::make('options.mode')
            ->label(__('admin/payment_driver.form_fields.paypal_options.mode.label'))
            ->placeholder(__('admin/payment_driver.form_fields.paypal_options.mode.placeholder'))
            ->options([
                'sandbox' => __('admin/payment_driver.form_fields.paypal_options.mode.options.sandbox'),
                'live' => __('admin/payment_driver.form_fields.paypal_options.mode.options.live'),
            ])
            ->live()
            ->default('live')
            ->required();
    }

    /**
     * PayPal ClientID
     * @return Component
     */
    protected static function getPayPalOptionClientIdFormComponent(): Component
    {
        return TextInput::make('options.client_id')
            ->label(__('admin/payment_driver.form_fields.paypal_options.client_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.paypal_options.client_id.placeholder'))
            ->required();
    }

    /**
     * PayPal Secret
     * @return Component
     */
    protected static function getPayPalOptionClientSecretFormComponent(): Component
    {
        return TextInput::make('options.client_secret')
            ->label(__('admin/payment_driver.form_fields.paypal_options.client_secret.label'))
            ->placeholder(__('admin/payment_driver.form_fields.paypal_options.client_secret.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * PayPal 货币代码
     * @return Component
     */
    protected static function getPayPalOptionCurrencyFormComponent(): Component
    {
        return TextInput::make('options.currency')
            ->label(__('admin/payment_driver.form_fields.paypal_options.currency.label'))
            ->placeholder(__('admin/payment_driver.form_fields.paypal_options.currency.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.paypal_options.currency.helper_text'))
            ->default('USD')
            ->required();
    }

    /**
     * PayPal Webhook ID
     * @return Component
     */
    protected static function getPayPalOptionWebhookIdFormComponent(): Component
    {
        return TextInput::make('options.webhook_id')
            ->label(__('admin/payment_driver.form_fields.paypal_options.webhook_id.label'))
            ->placeholder(__('admin/payment_driver.form_fields.paypal_options.webhook_id.placeholder'))
            ->helperText(__('admin/payment_driver.form_fields.paypal_options.webhook_id.helper_text'))
            ->required();
    }

    /**
     * EPay 配置表单
     * @return array
     */
    protected static function getEPayOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getEPayOptionApiUrlFormComponent(),
                    self::getEPayOptionPidFormComponent(),
                    self::getEPayOptionMerchantPrivateKeyFormComponent(),
                    self::getEPayOptionPlatformPublicKeyFormComponent(),
                ]),
                self::getEPayOptionChannelsFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === PaymentProvider::EPay->value)
        ];
    }

    /**
     * EPay 支付接口地址
     * @return Component
     */
    protected static function getEPayOptionApiUrlFormComponent(): Component
    {
        return TextInput::make('options.api_url')
            ->label(__('admin/payment_driver.form_fields.epay_options.api_url.label'))
            ->placeholder(__('admin/payment_driver.form_fields.epay_options.api_url.placeholder'))
            ->url()
            ->required();
    }

    /**
     * EPay 商户ID
     * @return Component
     */
    protected static function getEPayOptionPidFormComponent(): Component
    {
        return TextInput::make('options.pid')
            ->label(__('admin/payment_driver.form_fields.epay_options.pid.label'))
            ->placeholder(__('admin/payment_driver.form_fields.epay_options.pid.placeholder'))
            ->required();
    }

    /**
     * EPay 平台公钥
     * @return Component
     */
    protected static function getEPayOptionPlatformPublicKeyFormComponent(): Component
    {
        return Textarea::make('options.platform_public_key')
            ->label(__('admin/payment_driver.form_fields.epay_options.platform_public_key.label'))
            ->placeholder(__('admin/payment_driver.form_fields.epay_options.platform_public_key.placeholder'))
            ->required();
    }

    /**
     * EPay 商户私钥
     * @return Component
     */
    protected static function getEPayOptionMerchantPrivateKeyFormComponent(): Component
    {
        return Textarea::make('options.merchant_private_key')
            ->label(__('admin/payment_driver.form_fields.epay_options.merchant_private_key.label'))
            ->placeholder(__('admin/payment_driver.form_fields.epay_options.merchant_private_key.placeholder'))
            ->required();
    }

    /**
     * EPay 支持的支付通道
     * @return Component
     */
    protected static function getEPayOptionChannelsFormComponent(): Component
    {
        foreach ([
            PaymentChannel::Unified,
            PaymentChannel::Alipay,
            PaymentChannel::WXPay,
            PaymentChannel::QQPay,
            PaymentChannel::Bank,
            PaymentChannel::JDPay,
            PaymentChannel::Paypal,
            PaymentChannel::USDT,
        ] as $item) {
            $options[$item->value] = __("admin.payment_channels.{$item->value}");
        }

        return CheckboxList::make('options.channels')
            ->label(__('admin/payment_driver.form_fields.epay_options.channels.label'))
            ->options($options)
            ->bulkToggleable()
            ->helperText(__('admin/payment_driver.form_fields.epay_options.channels.helper_text'))
            ->gridDirection('row');
    }

    /**
     * 异步回调地址
     * @return Component
     */
    protected static function getOptionNotifyUrlFormComponent(): Component
    {
        return Placeholder::make('options.notify_url')
            ->label(__('admin/payment_driver.form_fields.options.notify_url.label'))
            ->content(fn(Get $get): string => AppService::getPaymentNotifyUrl($get('options.provider')))
            ->visible(fn(Get $get): bool => in_array($get('options.provider'), [
                PaymentProvider::Alipay->value,
                PaymentProvider::Wechat->value,
                PaymentProvider::UniPay->value,
                PaymentProvider::EPay->value,
            ]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentDrivers::route('/'),
            'create' => Pages\CreatePaymentDriver::route('/create'),
            'edit' => Pages\EditPaymentDriver::route('/{record}/edit'),
        ];
    }
}
