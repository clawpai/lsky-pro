<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\MailDriverResource\Pages;
use App\MailProvider;
use App\Models\Driver;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class MailDriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.driver');
    }

    public static function getModelLabel(): string
    {
        return __('admin/mail_driver.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/mail_driver.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', DriverType::Mail);
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Section::make()->columns(1)->schema(fn(Get $get): array => [
                Grid::make()->schema([
                    self::getNameFormComponent(),
                    self::getOptionProviderFormComponent(),
                ]),
                self::getIntroFormComponent(),
                Grid::make()->schema([
                    self::getFromAddressComponent(),
                    self::getFromNameComponent(),
                ]),
                ...self::getSmtpOptionFormComponents(),
                ...self::getSesOptionFormComponents(),
                ...self::getMailgunOptionFormComponents(),
                ...self::getPostmarkOptionFormComponents(),
            ]),
        ]);
    }

    /**
     * 策略名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/mail_driver.form_fields.name.label'))
            ->placeholder(__('admin/mail_driver.form_fields.name.placeholder'))
            ->minLength(1)
            ->maxLength(200)
            ->required();
    }

    /**
     * 邮件驱动器
     * @return Component
     */
    protected static function getOptionProviderFormComponent(): Component
    {
        return Select::make('options.provider')
            ->label(__('admin/mail_driver.form_fields.options.provider.label'))
            ->placeholder(__('admin/mail_driver.form_fields.options.provider.placeholder'))
            ->options(AppService::getAllMailProviders())
            ->live()
            ->required()
            ->default(MailProvider::Smtp->value)
            ->native(false);
    }

    /**
     * 策略简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/mail_driver.form_fields.intro.label'))
            ->placeholder(__('admin/mail_driver.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 发件人邮箱地址
     * @return Component
     */
    protected static function getFromAddressComponent(): Component
    {
        return TextInput::make('options.from_address')
            ->label(__('admin/mail_driver.form_fields.options.from_address.label'))
            ->placeholder(__('admin/mail_driver.form_fields.options.from_address.placeholder'))
            ->email()
            ->required();
    }

    /**
     * 发件人名称
     * @return Component
     */
    protected static function getFromNameComponent(): Component
    {
        return TextInput::make('options.from_name')
            ->label(__('admin/mail_driver.form_fields.options.from_name.label'))
            ->placeholder(__('admin/mail_driver.form_fields.options.from_name.placeholder'))
            ->maxLength(100);
    }

    /**
     * Smtp 配置表单
     * @return array<Component>
     */
    protected static function getSmtpOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                self::getSmtpOptionHostFormComponent(),
                Grid::make()->schema([
                    self::getSmtpOptionPortFormComponent(),
                    self::getSmtpOptionEncryptionFormComponent(),
                    self::getSmtpOptionUsernameFormComponent(),
                    self::getSmtpOptionPasswordFormComponent(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === MailProvider::Smtp->value),
        ];
    }

    /**
     * Smtp 主机地址
     * @return Component
     */
    protected static function getSmtpOptionHostFormComponent(): Component
    {
        return TextInput::make('options.host')
            ->label(__('admin/mail_driver.form_fields.smtp_options.host.label'))
            ->placeholder(__('admin/mail_driver.form_fields.smtp_options.host.placeholder'))
            ->required();
    }

    /**
     * Smtp 连接端口
     * @return Component
     */
    protected static function getSmtpOptionPortFormComponent(): Component
    {
        return TextInput::make('options.port')
            ->label(__('admin/mail_driver.form_fields.smtp_options.port.label'))
            ->placeholder(__('admin/mail_driver.form_fields.smtp_options.port.placeholder'))
            ->numeric()
            ->default(25)
            ->required();
    }

    /**
     * Smtp 加密方式
     * @return Component
     */
    protected static function getSmtpOptionEncryptionFormComponent(): Component
    {
        return Select::make('options.encryption')
            ->label(__('admin/mail_driver.form_fields.smtp_options.encryption.label'))
            ->placeholder(__('admin/mail_driver.form_fields.smtp_options.encryption.placeholder'))
            ->options(['ssl' => 'SSL', 'tls' => 'TLS'])
            ->default('tls')
            ->native(false)
            ->required();
    }

    /**
     * Smtp 用户名
     * @return Component
     */
    protected static function getSmtpOptionUsernameFormComponent(): Component
    {
        return TextInput::make('options.username')
            ->label(__('admin/mail_driver.form_fields.smtp_options.username.label'))
            ->placeholder(__('admin/mail_driver.form_fields.smtp_options.username.placeholder'))
            ->required();
    }

    /**
     * Smtp 密码
     * @return Component
     */
    protected static function getSmtpOptionPasswordFormComponent(): Component
    {
        return TextInput::make('options.password')
            ->label(__('admin/mail_driver.form_fields.smtp_options.password.label'))
            ->placeholder(__('admin/mail_driver.form_fields.smtp_options.password.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * Ses 配置表单
     * @return array<Component>
     */
    protected static function getSesOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getSesOptionServiceAccessKeyIdFormComponent(),
                    self::getSesOptionServiceSecretAccessKeyFormComponent(),
                    self::getSesOptionServiceRegionFormComponent(),
                    self::getSesOptionServiceSessionTokenFormComponent(),
                ]),
            ])->visible(fn(Get $get): bool => $get('options.provider') === MailProvider::Ses->value),
        ];
    }

    /**
     * Ses Service AccessKeyId
     * @return Component
     */
    protected static function getSesOptionServiceAccessKeyIdFormComponent(): Component
    {
        return TextInput::make('options.service.key')
            ->label(__('admin/mail_driver.form_fields.ses_options.service.key.label'))
            ->placeholder(__('admin/mail_driver.form_fields.ses_options.service.key.placeholder'))
            ->required();
    }

    /**
     * Ses SecretAccessKey
     * @return Component
     */
    protected static function getSesOptionServiceSecretAccessKeyFormComponent(): Component
    {
        return TextInput::make('options.service.secret')
            ->label(__('admin/mail_driver.form_fields.ses_options.service.secret.label'))
            ->placeholder(__('admin/mail_driver.form_fields.ses_options.service.secret.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * Ses Service Region
     * @return Component
     */
    protected static function getSesOptionServiceRegionFormComponent(): Component
    {
        return TextInput::make('options.service.region')
            ->label(__('admin/mail_driver.form_fields.ses_options.service.region.label'))
            ->placeholder(__('admin/mail_driver.form_fields.ses_options.service.region.placeholder'))
            ->default('us-east-1');
    }

    /**
     * Ses Service Session Token
     * @return Component
     */
    protected static function getSesOptionServiceSessionTokenFormComponent(): Component
    {
        return TextInput::make('options.service.token')
            ->label(__('admin/mail_driver.form_fields.ses_options.service.token.label'))
            ->placeholder(__('admin/mail_driver.form_fields.ses_options.service.token.placeholder'))
            ->password()
            ->revealable();
    }

    /**
     * Mailgun 配置表单
     * @return array<Component>
     */
    protected static function getMailgunOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                self::getMailgunOptionServiceDomainFormComponent(),
                self::getMailgunOptionServiceSecretFormComponent(),
                self::getMailgunOptionServiceEndpointFormComponent(),
                self::getMailgunOptionServiceSchemeFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === MailProvider::Mailgun->value),
        ];
    }

    /**
     * Mailgun Service Domain
     * @return Component
     */
    protected static function getMailgunOptionServiceDomainFormComponent(): Component
    {
        return TextInput::make('options.service.domain')
            ->label(__('admin/mail_driver.form_fields.mailgun_options.service.domain.label'))
            ->placeholder(__('admin/mail_driver.form_fields.mailgun_options.service.domain.placeholder'));
    }

    /**
     * Mailgun Service Secret
     * @return Component
     */
    protected static function getMailgunOptionServiceSecretFormComponent(): Component
    {
        return TextInput::make('options.service.secret')
            ->label(__('admin/mail_driver.form_fields.mailgun_options.service.secret.label'))
            ->placeholder(__('admin/mail_driver.form_fields.mailgun_options.service.secret.placeholder'))
            ->password()
            ->revealable();
    }

    /**
     * Mailgun Service Endpoint
     * @return Component
     */
    protected static function getMailgunOptionServiceEndpointFormComponent(): Component
    {
        return TextInput::make('options.service.endpoint')
            ->label(__('admin/mail_driver.form_fields.mailgun_options.service.endpoint.label'))
            ->placeholder(__('admin/mail_driver.form_fields.mailgun_options.service.endpoint.placeholder'))
            ->default('api.mailgun.net');
    }

    /**
     * Mailgun Service Scheme
     * @return Component
     */
    protected static function getMailgunOptionServiceSchemeFormComponent(): Component
    {
        return TextInput::make('options.service.scheme')
            ->label(__('admin/mail_driver.form_fields.mailgun_options.service.scheme.label'))
            ->placeholder(__('admin/mail_driver.form_fields.mailgun_options.service.scheme.placeholder'))
            ->default('https');
    }

    /**
     * Postmark 配置表单
     * @return array<Component>
     */
    protected static function getPostmarkOptionFormComponents(): array
    {
        return [
            self::getPostmarkOptionServiceTokenFormComponent()
                ->visible(fn(Get $get): bool => $get('options.provider') === MailProvider::Postmark->value),
        ];
    }

    /**
     * Postmark Token
     * @return Component
     */
    protected static function getPostmarkOptionServiceTokenFormComponent(): Component
    {
        return TextInput::make('options.service.token')
            ->label(__('admin/mail_driver.form_fields.postmark_options.service.token.label'))
            ->placeholder(__('admin/mail_driver.form_fields.postmark_options.service.token.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailDrivers::route('/'),
            'create' => Pages\CreateMailDriver::route('/create'),
            'edit' => Pages\EditMailDriver::route('/{record}/edit'),
        ];
    }
}
