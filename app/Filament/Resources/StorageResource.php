<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\StorageResource\Pages;
use App\Models\Storage;
use App\StorageProvider;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Sabre\DAV\Client;

class StorageResource extends Resource
{
    protected static ?string $model = Storage::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?int $navigationSort = 31;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('admin/storage.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/storage.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Tabs::make()->id('storage-tabs')->persistTab()->schema([
                // 基础设置
                Tabs\Tab::make(__('admin/storage.tabs.general'))->id('general')->schema([
                    Grid::make()->schema([
                        self::getNameFormComponent(),
                        self::getOptionPublicUrlFormComponent(),
                        self::getOptionNamingRuleFormComponent(),
                        self::getPrefixFormComponent(),
                    ]),
                    self::getIntroFormComponent(),
                    self::getGenerateThumbnailFormComponent(),
                    Grid::make()->schema([
                        self::getThumbnailMaxSizeFormComponent(),
                        self::getThumbnailQualityFormComponent(),
                    ]),
                ]),
                // 储存配置
                Tabs\Tab::make(__('admin/storage.tabs.options'))->id('options')->schema([
                    self::getProviderFormComponent(),
                    self::getOptionRootFormComponent(),
                    ...self::getS3OptionFormComponents(),
                    ...self::getOssOptionFormComponents(),
                    ...self::getCosOptionFormComponents(),
                    ...self::getQiniuOptionFormComponents(),
                    ...self::getUpyunOptionFormComponents(),
                    ...self::getSftpOptionFormComponents(),
                    ...self::getFtpOptionFormComponents(),
                    ...self::getWebdavOptionFormComponents(),
                ]),
                // 处理器配置
                Tabs\Tab::make(__('admin/storage.tabs.processor'))->id('processor')->schema([
                    self::getScanDriverFormComponent(),
                    self::getHandleDriverFormComponent(),
                    self::getProcessDriverFormComponent(),
                ]),
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
            ->label(__('admin/storage.form_fields.name.label'))
            ->placeholder(__('admin/storage.form_fields.name.placeholder'))
            ->minLength(1)
            ->maxLength(200)
            ->required();
    }

    /**
     * 访问url
     * @return Component
     */
    protected static function getOptionPublicUrlFormComponent(): Component
    {
        return TextInput::make('options.public_url')
            ->label(__('admin/storage.form_fields.options.public_url.label'))
            ->placeholder(__('admin/storage.form_fields.options.public_url.placeholder'))
            ->minLength(1)
            ->maxLength(600)
            ->url()
            ->required();
    }

    /**
     * 命名规则
     * @return Component
     */
    protected static function getOptionNamingRuleFormComponent(): Component
    {
        return TextInput::make('options.naming_rule')
            ->label(__('admin/storage.form_fields.options.naming_rule.label'))
            ->placeholder(__('admin/storage.form_fields.options.naming_rule.placeholder'))
            ->helperText(new HtmlString(__('admin/storage.form_fields.options.naming_rule.helper_text')))
            ->minLength(1)
            ->maxLength(600)
            ->required();
        // TODO 展示静态表格 v4.0 https://filamentphp.com/community/alexandersix-filament-what-to-expect-in-2024#static-table-data-sources
    }

    /**
     * 储存文件访问前缀
     * @return Component
     */
    protected static function getPrefixFormComponent(): Component
    {
        return TextInput::make('prefix')
            ->label(__('admin/storage.form_fields.prefix.label'))
            ->placeholder(__('admin/storage.form_fields.prefix.placeholder'))
            ->helperText(__('admin/storage.form_fields.prefix.helper_text'))
            ->rule(fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                // 系统保留路径
                if (in_array($value, ['admin', 'css', 'js', 'storage', 'assets', 'themes', 'build', 'vendor'])) {
                    $fail(__('admin/storage.form_fields.prefix.validation.retention'));
                }

                // 判断访问前缀是否存在
                if (Storage::where('id', '<>', $get('id'))->where('prefix', $get('prefix'))->exists()) {
                    $fail(__('admin/storage.form_fields.prefix.validation.exists'));
                }
            })
            ->default('uploads')
            ->alphaDash()
            ->dehydrateStateUsing(fn($state) => (string)$state)
            // 本地和开启了云处理的储存驱动器必须需要前缀
            ->required(fn(Get $get): bool => $get('process_driver_id') || $get('provider') === StorageProvider::Local->value);
    }

    /**
     * 简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/storage.form_fields.intro.label'))
            ->placeholder(__('admin/storage.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 缩略图最大尺寸
     * @return Component
     */
    protected static function getThumbnailMaxSizeFormComponent(): Component
    {
        return TextInput::make('options.thumbnail_max_size')
            ->label(__('admin/storage.form_fields.thumbnail.max_size.label'))
            ->placeholder(__('admin/storage.form_fields.thumbnail.max_size.placeholder'))
            ->helperText(__('admin/storage.form_fields.thumbnail.max_size.helper_text'))
            ->numeric()
            ->minValue(100)
            ->maxValue(4000)
            ->default(800)
            ->visible(fn(Get $get): bool => $get('options.generate_thumbnail') !== false);
    }

    /**
     * 缩略图质量
     * @return Component
     */
    protected static function getThumbnailQualityFormComponent(): Component
    {
        return TextInput::make('options.thumbnail_quality')
            ->label(__('admin/storage.form_fields.thumbnail.quality.label'))
            ->placeholder(__('admin/storage.form_fields.thumbnail.quality.placeholder'))
            ->helperText(__('admin/storage.form_fields.thumbnail.quality.helper_text'))
            ->numeric()
            ->minValue(10)
            ->maxValue(100)
            ->default(90)
            ->visible(fn(Get $get): bool => $get('options.generate_thumbnail') !== false);
    }

    /**
     * 是否生成缩略图
     * @return Component
     */
    protected static function getGenerateThumbnailFormComponent(): Component
    {
        return Toggle::make('options.generate_thumbnail')
            ->label(__('admin/storage.form_fields.thumbnail.generate_thumbnail.label'))
            ->helperText(__('admin/storage.form_fields.thumbnail.generate_thumbnail.helper_text'))
            ->inline(false)
            ->default(true)
            ->live();
    }

    /**
     * 储存驱动器
     * @return Component
     */
    protected static function getProviderFormComponent(): Component
    {
        return Select::make('provider')
            ->label(__('admin/storage.form_fields.provider.label'))
            ->placeholder(__('admin/storage.form_fields.provider.placeholder'))
            ->options(AppService::getAllStorageProviders())
            ->live()
            ->required()
            ->searchable()
            ->default(StorageProvider::Local->value)
            ->afterStateUpdated(function(string $state, Set $set) {
                $set('options.root', $state === StorageProvider::Local->value ? storage_path('app/uploads') : DIRECTORY_SEPARATOR);
            })
            ->native(false);
    }

    /**
     * 本地储存文件根目录
     * @return Component
     */
    protected static function getOptionRootFormComponent(): Component
    {
        return TextInput::make('options.root')
            ->label(__('admin/storage.form_fields.local_options.root.label'))
            ->placeholder(__('admin/storage.form_fields.local_options.root.placeholder'))
            ->helperText(__('admin/storage.form_fields.local_options.root.helper_text'))
            ->default('')
            ->visible(function (Get $get): bool {
                // 排除不支持设置根路径的储存
                return ! in_array($get('provider'), [
                    StorageProvider::Qiniu->value,
                    StorageProvider::Upyun->value,
                ]);
            })
            ->required();
    }

    /**
     * AWS S3 储存配置表单
     * @return array<Component>
     */
    protected static function getS3OptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                self::getAwsS3OptionEndpointFormComponent(),
                Grid::make()->schema([
                    self::getAwsS3OptionAccessKeyIdFormComponent(),
                    self::getAwsS3OptionSecretAccessKeyFormComponent(),
                    self::getAwsS3OptionRegionFormComponent(),
                    self::getAwsS3OptionBucketFormComponent(),
                ]),
                self::getAwsS3OptionUsePathStyleEndpointFormComponent(),
                self::getOptionCustomOptionsFormComponent('options.options'),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::S3->value),
        ];
    }

    /**
     * AWS S3 储存 Endpoint
     * @return Component
     */
    protected static function getAwsS3OptionEndpointFormComponent(): Component
    {
        return TextInput::make('options.endpoint')
            ->label(__('admin/storage.form_fields.s3_options.endpoint.label'))
            ->placeholder(__('admin/storage.form_fields.s3_options.endpoint.placeholder'))
            ->required();
    }

    /**
     * AWS S3 储存 AccessKeyId
     * @return Component
     */
    protected static function getAwsS3OptionAccessKeyIdFormComponent(): Component
    {
        return TextInput::make('options.access_key_id')
            ->label(__('admin/storage.form_fields.s3_options.access_key_id.label'))
            ->placeholder(__('admin/storage.form_fields.s3_options.access_key_id.placeholder'))
            ->required();
    }

    /**
     * AWS S3 储存 SecretAccessKey
     * @return Component
     */
    protected static function getAwsS3OptionSecretAccessKeyFormComponent(): Component
    {
        return TextInput::make('options.secret_access_key')
            ->label(__('admin/storage.form_fields.s3_options.secret_access_key.label'))
            ->placeholder(__('admin/storage.form_fields.s3_options.secret_access_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * AWS S3 储存 Region
     * @return Component
     */
    protected static function getAwsS3OptionRegionFormComponent(): Component
    {
        return TextInput::make('options.region')
            ->label(__('admin/storage.form_fields.s3_options.region.label'))
            ->placeholder(__('admin/storage.form_fields.s3_options.region.placeholder'));
    }

    /**
     * AWS S3 储存 Bucket
     * @return Component
     */
    protected static function getAwsS3OptionBucketFormComponent(): Component
    {
        return TextInput::make('options.bucket')
            ->label(__('admin/storage.form_fields.s3_options.bucket.label'))
            ->placeholder(__('admin/storage.form_fields.s3_options.bucket.placeholder'));
    }

    /**
     * AWS S3 储存 是否使用路径样式
     * @return Component
     */
    protected static function getAwsS3OptionUsePathStyleEndpointFormComponent(): Component
    {
        return Toggle::make('options.use_path_style_endpoint')
            ->label(__('admin/storage.form_fields.s3_options.use_path_style_endpoint.label'))
            ->helperText(new HtmlString(__('admin/storage.form_fields.s3_options.use_path_style_endpoint.helper_text')))
            ->inline(false)
            ->default(false);
    }

    /**
     * 储存自定义配置
     * @param string $name
     * @return Component
     */
    protected static function getOptionCustomOptionsFormComponent(string $name): Component
    {
        return KeyValue::make($name)
            ->label(__('admin/storage.form_fields.options.options.label'))
            ->keyLabel(__('admin/storage.form_fields.options.options.key_label'))
            ->valueLabel(__('admin/storage.form_fields.options.options.value_label'))
            ->keyPlaceholder(__('admin/storage.form_fields.options.options.key_placeholder'))
            ->valuePlaceholder(__('admin/storage.form_fields.options.options.value_placeholder'))
            ->helperText(__('admin/storage.form_fields.options.options.helper_text'));
    }

    /**
     * 阿里云 OSS 配置表单
     * @return array<Component>
     */
    protected static function getOssOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getOssOptionEndpointFormComponent(),
                    self::getOssOptionInternalFormComponent(),
                    self::getOssOptionAccessKeyIdFormComponent(),
                    self::getOssOptionAccessKeySecretFormComponent(),
                    self::getOssOptionBucketFormComponent(),
                    self::getOssOptionRegionFormComponent(),
                ]),
                self::getOssOptionIsCnameFormComponent(),
                self::getOptionCustomOptionsFormComponent('options.options'),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::Oss->value),
        ];
    }

    /**
     * 阿里云 OSS 储存 Endpoint
     * @return Component
     */
    protected static function getOssOptionEndpointFormComponent(): Component
    {
        return TextInput::make('options.endpoint')
            ->label(__('admin/storage.form_fields.oss_options.endpoint.label'))
            ->placeholder(__('admin/storage.form_fields.oss_options.endpoint.placeholder'))
            ->required();
    }

    /**
     * 阿里云 OSS 储存 Internal
     * @return Component
     */
    protected static function getOssOptionInternalFormComponent(): Component
    {
        return TextInput::make('options.internal')
            ->label(__('admin/storage.form_fields.oss_options.internal.label'))
            ->placeholder(__('admin/storage.form_fields.oss_options.internal.placeholder'));
    }

    /**
     * 阿里云 OSS 储存 AccessKeyId
     * @return Component
     */
    protected static function getOssOptionAccessKeyIdFormComponent(): Component
    {
        return TextInput::make('options.access_key_id')
            ->label(__('admin/storage.form_fields.oss_options.access_key_id.label'))
            ->placeholder(__('admin/storage.form_fields.oss_options.access_key_id.placeholder'))
            ->required();
    }

    /**
     * 阿里云 OSS 储存 AccessKeySecret
     * @return Component
     */
    protected static function getOssOptionAccessKeySecretFormComponent(): Component
    {
        return TextInput::make('options.access_key_secret')
            ->label(__('admin/storage.form_fields.oss_options.access_key_secret.label'))
            ->placeholder(__('admin/storage.form_fields.oss_options.access_key_secret.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 阿里云 OSS 储存 Bucket
     * @return Component
     */
    protected static function getOssOptionBucketFormComponent(): Component
    {
        return TextInput::make('options.bucket')
            ->label(__('admin/storage.form_fields.oss_options.bucket.label'))
            ->placeholder(__('admin/storage.form_fields.oss_options.bucket.placeholder'))
            ->required();
    }

    /**
     * 阿里云 OSS 储存 Region
     * @return Component
     */
    protected static function getOssOptionRegionFormComponent(): Component
    {
        return TextInput::make('options.region')
            ->label(__('admin/storage.form_fields.oss_options.region.label'))
            ->placeholder(__('admin/storage.form_fields.oss_options.region.placeholder'));
    }

    /**
     * 阿里云 OSS 储存 Is Cname
     * @return Component
     */
    protected static function getOssOptionIsCnameFormComponent(): Component
    {
        return Toggle::make('options.is_cname')
            ->label(__('admin/storage.form_fields.oss_options.is_cname.label'))
            ->helperText(__('admin/storage.form_fields.oss_options.is_cname.helper_text'))
            ->inline(false)
            ->default(false);
    }

    /**
     * 腾讯云 COS 配置表单
     * @return array<Component>
     */
    protected static function getCosOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getCosOptionAppIdFormComponent(),
                    self::getCosOptionBucketFormComponent(),
                    self::getCosOptionSecretIdFormComponent(),
                    self::getCosOptionSecretKeyFormComponent(),
                ]),
                self::getCosOptionRegionFormComponent(),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::Cos->value),
        ];
    }

    /**
     * 腾讯云 COS 储存 AppId
     * @return Component
     */
    protected static function getCosOptionAppIdFormComponent(): Component
    {
        return TextInput::make('options.app_id')
            ->label(__('admin/storage.form_fields.cos_options.app_id.label'))
            ->placeholder(__('admin/storage.form_fields.cos_options.app_id.placeholder'))
            ->required();
    }

    /**
     * 腾讯云 COS 储存 Bucket
     * @return Component
     */
    protected static function getCosOptionBucketFormComponent(): Component
    {
        return TextInput::make('options.bucket')
            ->label(__('admin/storage.form_fields.cos_options.bucket.label'))
            ->placeholder(__('admin/storage.form_fields.cos_options.bucket.placeholder'))
            ->required();
    }

    /**
     * 腾讯云 COS 储存 SecretId
     * @return Component
     */
    protected static function getCosOptionSecretIdFormComponent(): Component
    {
        return TextInput::make('options.secret_id')
            ->label(__('admin/storage.form_fields.cos_options.secret_id.label'))
            ->placeholder(__('admin/storage.form_fields.cos_options.secret_id.placeholder'))
            ->required();
    }

    /**
     * 腾讯云 COS 储存 SecretKey
     * @return Component
     */
    protected static function getCosOptionSecretKeyFormComponent(): Component
    {
        return TextInput::make('options.secret_key')
            ->label(__('admin/storage.form_fields.cos_options.secret_key.label'))
            ->placeholder(__('admin/storage.form_fields.cos_options.secret_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 腾讯云 COS 储存 Region
     * @return Component
     */
    protected static function getCosOptionRegionFormComponent(): Component
    {
        return TextInput::make('options.region')
            ->label(__('admin/storage.form_fields.cos_options.region.label'))
            ->placeholder(__('admin/storage.form_fields.cos_options.region.placeholder'));
    }

    /**
     * 七牛云 Kodo 配置表单
     * @return array<Component>
     */
    protected static function getQiniuOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getQiniuOptionAccessKeyFormComponent(),
                    self::getQiniuOptionSecretKeyFormComponent(),
                    self::getQiniuOptionBucketFormComponent(),
                    self::getQiniuOptionDomainFormComponent(),
                ]),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::Qiniu->value),
        ];
    }

    /**
     * 七牛云 Kodo 储存 AccessKey
     * @return Component
     */
    protected static function getQiniuOptionAccessKeyFormComponent(): Component
    {
        return TextInput::make('options.access_key')
            ->label(__('admin/storage.form_fields.qiniu_options.access_key.label'))
            ->placeholder(__('admin/storage.form_fields.qiniu_options.access_key.placeholder'))
            ->required();
    }

    /**
     * 七牛云 Kodo 储存 SecretKey
     * @return Component
     */
    protected static function getQiniuOptionSecretKeyFormComponent(): Component
    {
        return TextInput::make('options.secret_key')
            ->label(__('admin/storage.form_fields.cos_options.secret_key.label'))
            ->placeholder(__('admin/storage.form_fields.cos_options.secret_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 七牛云 kodo 储存 Bucket
     * @return Component
     */
    protected static function getQiniuOptionBucketFormComponent(): Component
    {
        return TextInput::make('options.bucket')
            ->label(__('admin/storage.form_fields.qiniu_options.bucket.label'))
            ->placeholder(__('admin/storage.form_fields.qiniu_options.bucket.placeholder'))
            ->required();
    }

    /**
     * 七牛云 kodo 储存 域名
     * @return Component
     */
    protected static function getQiniuOptionDomainFormComponent(): Component
    {
        return TextInput::make('options.domain')
            ->label(__('admin/storage.form_fields.qiniu_options.domain.label'))
            ->placeholder(__('admin/storage.form_fields.qiniu_options.domain.placeholder'))
            ->helperText(__('admin/storage.form_fields.qiniu_options.domain.helper_text'));
    }

    /**
     * 又拍云 USS 配置表单
     * @return array<Component>
     */
    protected static function getUpyunOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                self::getUpyunOptionServiceFormComponent(),
                Grid::make()->schema([
                    self::getUpyunOptionOperatorFormComponent(),
                    self::getUpyunOptionPasswordFormComponent(),
                ]),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::Upyun->value),
        ];
    }

    /**
     * 又拍云 Uss 储存 Service
     * @return Component
     */
    protected static function getUpyunOptionServiceFormComponent(): Component
    {
        return TextInput::make('options.service')
            ->label(__('admin/storage.form_fields.upyun_options.service.label'))
            ->placeholder(__('admin/storage.form_fields.upyun_options.service.placeholder'))
            ->required();
    }

    /**
     * 又拍云 Uss 储存 Operator
     * @return Component
     */
    protected static function getUpyunOptionOperatorFormComponent(): Component
    {
        return TextInput::make('options.operator')
            ->label(__('admin/storage.form_fields.upyun_options.operator.label'))
            ->placeholder(__('admin/storage.form_fields.upyun_options.operator.placeholder'))
            ->required();
    }

    /**
     * 又拍云 Uss 储存 Password
     * @return Component
     */
    protected static function getUpyunOptionPasswordFormComponent(): Component
    {
        return TextInput::make('options.password')
            ->label(__('admin/storage.form_fields.upyun_options.password.label'))
            ->placeholder(__('admin/storage.form_fields.upyun_options.password.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * Sftp 配置表单
     * @return array<Component>
     */
    protected static function getSftpOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getSftpOptionHostFormComponent(),
                    self::getSftpOptionPortFormComponent(),
                    self::getSftpOptionUsernameFormComponent(),
                    self::getSftpOptionPasswordFormComponent(),
                    self::getSftpOptionTimeoutFormComponent(),
                    self::getSftpOptionMaxTriesFormComponent(),
                ]),
                self::getSftpOptionPrivateKeyFormComponent(),
                Grid::make()->schema([
                    self::getSftpOptionPassphraseFormComponent(),
                    self::getSftpOptionHostFingerprintFormComponent(),
                ]),
                self::getSftpOptionUseAgentFormComponent(),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::Sftp->value),
        ];
    }

    /**
     * Sftp 储存 主机地址
     * @return Component
     */
    protected static function getSftpOptionHostFormComponent(): Component
    {
        return TextInput::make('options.host')
            ->label(__('admin/storage.form_fields.sftp_options.host.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.host.placeholder'))
            ->required();
    }

    /**
     * Sftp 储存 端口
     * @return Component
     */
    protected static function getSftpOptionPortFormComponent(): Component
    {
        return TextInput::make('options.port')
            ->label(__('admin/storage.form_fields.sftp_options.port.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.port.placeholder'))
            ->numeric()
            ->default(22)
            ->required();
    }

    /**
     * Sftp 储存 账号
     * @return Component
     */
    protected static function getSftpOptionUsernameFormComponent(): Component
    {
        return TextInput::make('options.username')
            ->label(__('admin/storage.form_fields.sftp_options.username.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.username.placeholder'));
    }

    /**
     * Sftp 储存 密码
     * @return Component
     */
    protected static function getSftpOptionPasswordFormComponent(): Component
    {
        return TextInput::make('options.password')
            ->label(__('admin/storage.form_fields.sftp_options.password.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.password.placeholder'))
            ->password()
            ->revealable()
            ->password();
    }

    /**
     * Sftp 储存 连接超时时间
     * @return Component
     */
    protected static function getSftpOptionTimeoutFormComponent(): Component
    {
        return TextInput::make('options.timeout')
            ->label(__('admin/storage.form_fields.sftp_options.timeout.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.timeout.placeholder'))
            ->numeric()
            ->default(10)
            ->required();
    }

    /**
     * Sftp 储存 最大重试次数
     * @return Component
     */
    protected static function getSftpOptionMaxTriesFormComponent(): Component
    {
        return TextInput::make('options.max_tries')
            ->label(__('admin/storage.form_fields.sftp_options.max_tries.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.max_tries.placeholder'))
            ->numeric()
            ->default(4)
            ->required();
    }

    /**
     * Sftp 储存 私钥
     * @return Component
     */
    protected static function getSftpOptionPrivateKeyFormComponent(): Component
    {
        return Textarea::make('options.private_key')
            ->label(__('admin/storage.form_fields.sftp_options.private_key.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.private_key.placeholder'));
    }

    /**
     * Sftp 储存 私钥口令
     * @return Component
     */
    protected static function getSftpOptionPassphraseFormComponent(): Component
    {
        return TextInput::make('options.passphrase')
            ->label(__('admin/storage.form_fields.sftp_options.passphrase.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.passphrase.placeholder'))
            ->password()
            ->revealable();
    }

    /**
     * Sftp 储存 主机指纹
     * @return Component
     */
    protected static function getSftpOptionHostFingerprintFormComponent(): Component
    {
        return TextInput::make('options.host_fingerprint')
            ->label(__('admin/storage.form_fields.sftp_options.host_fingerprint.label'))
            ->placeholder(__('admin/storage.form_fields.sftp_options.host_fingerprint.placeholder'))
            ->password()
            ->revealable();
    }

    /**
     * Sftp 储存 是否使用代理
     * @return Component
     */
    protected static function getSftpOptionUseAgentFormComponent(): Component
    {
        return Toggle::make('options.use_agent')
            ->label(__('admin/storage.form_fields.sftp_options.use_agent.label'))
            ->inline(false)
            ->default(false);
    }

    /**
     * Ftp 配置表单
     * @return array<Component>
     */
    protected static function getFtpOptionFormComponents(): array
    {
        return [
            Grid::make()->columns(1)->schema([
                self::getFtpOptionHostFormComponent(),
                Grid::make()->schema([
                    self::getFtpOptionUsernameFormComponent(),
                    self::getFtpOptionPasswordFormComponent(),
                    self::getFtpOptionPortFormComponent(),
                    self::getFtpOptionTransferModeFormComponent(),
                ]),
                self::getFtpOptionTimeoutFormComponent(),
                self::getFtpOptionPassiveFormComponent(),
                self::getFtpOptionSslFormComponent(),
                self::getFtpOptionIgnorePassiveAddressFormComponent(),
                self::getFtpOptionEnableTimestampsOnUnixListingsFormComponent(),
                self::getFtpOptionUtf8FormComponent(),
                self::getFtpOptionRecurseManuallyFormComponent(),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::Ftp->value),
        ];
    }

    /**
     * Ftp 储存 主机地址
     * @return Component
     */
    protected static function getFtpOptionHostFormComponent(): Component
    {
        return TextInput::make('options.host')
            ->label(__('admin/storage.form_fields.ftp_options.host.label'))
            ->placeholder(__('admin/storage.form_fields.ftp_options.host.placeholder'))
            ->required();
    }

    /**
     * Ftp 储存 账号
     * @return Component
     */
    protected static function getFtpOptionUsernameFormComponent(): Component
    {
        return TextInput::make('options.username')
            ->label(__('admin/storage.form_fields.ftp_options.username.label'))
            ->placeholder(__('admin/storage.form_fields.ftp_options.username.placeholder'))
            ->required();
    }

    /**
     * Ftp 储存 密码
     * @return Component
     */
    protected static function getFtpOptionPasswordFormComponent(): Component
    {
        return TextInput::make('options.password')
            ->label(__('admin/storage.form_fields.ftp_options.password.label'))
            ->placeholder(__('admin/storage.form_fields.ftp_options.password.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * Ftp 储存 端口
     * @return Component
     */
    protected static function getFtpOptionPortFormComponent(): Component
    {
        return TextInput::make('options.port')
            ->label(__('admin/storage.form_fields.ftp_options.port.label'))
            ->placeholder(__('admin/storage.form_fields.ftp_options.port.placeholder'))
            ->numeric()
            ->default(21)
            ->required();
    }

    /**
     * Ftp 储存 传输模式
     * @return Component
     */
    protected static function getFtpOptionTransferModeFormComponent(): Component
    {
        return Select::make('options.transfer_mode')
            ->label(__('admin/storage.form_fields.ftp_options.transfer_mode.label'))
            ->placeholder(__('admin/storage.form_fields.ftp_options.transfer_mode.placeholder'))
            ->options([FTP_BINARY => 'FTP_BINARY', FTP_ASCII => 'FTP_ASCII'])
            ->native(false)
            ->default(FTP_BINARY)
            ->required();
    }

    /**
     * Ftp 储存 连接超时时间
     * @return Component
     */
    protected static function getFtpOptionTimeoutFormComponent(): Component
    {
        return TextInput::make('options.timeout')
            ->label(__('admin/storage.form_fields.ftp_options.timeout.label'))
            ->placeholder(__('admin/storage.form_fields.ftp_options.timeout.placeholder'))
            ->numeric()
            ->default(90)
            ->required();
    }

    /**
     * Ftp 储存 是否使用被动模式
     * @return Component
     */
    protected static function getFtpOptionPassiveFormComponent(): Component
    {
        return Toggle::make('options.passive')
            ->label(__('admin/storage.form_fields.ftp_options.passive.label'))
            ->inline(false)
            ->default(false);
    }

    /**
     * Ftp 储存 是否使用 SSL 连接
     * @return Component
     */
    protected static function getFtpOptionSslFormComponent(): Component
    {
        return Toggle::make('options.ssl')
            ->label(__('admin/storage.form_fields.ftp_options.ssl.label'))
            ->inline(false)
            ->default(false);
    }

    /**
     * Ftp 储存 是否忽略被动模式下的远程 IP 地址
     * @return Component
     */
    protected static function getFtpOptionIgnorePassiveAddressFormComponent(): Component
    {
        return Toggle::make('options.ignore_passive_address')
            ->label(__('admin/storage.form_fields.ftp_options.ignore_passive_address.label'))
            ->inline(false)
            ->default(null);
    }

    /**
     * Ftp 储存 是否启用 Unix 时间戳
     * @return Component
     */
    protected static function getFtpOptionEnableTimestampsOnUnixListingsFormComponent(): Component
    {
        return Toggle::make('options.enable_timestamps_on_unix_listings')
            ->label(__('admin/storage.form_fields.ftp_options.enable_timestamps_on_unix_listings.label'))
            ->inline(false)
            ->default(false);
    }

    /**
     * Ftp 储存 是否启用 UTF-8 编码
     * @return Component
     */
    protected static function getFtpOptionUtf8FormComponent(): Component
    {
        return Toggle::make('options.utf8')
            ->label(__('admin/storage.form_fields.ftp_options.utf8.label'))
            ->inline(false)
            ->default(false);
    }

    /**
     * Ftp 储存 手动递归
     * @return Component
     */
    protected static function getFtpOptionRecurseManuallyFormComponent(): Component
    {
        return Toggle::make('options.recurse_manually')
            ->label(__('admin/storage.form_fields.ftp_options.recurse_manually.label'))
            ->inline(false)
            ->default(false);
    }

    /**
     * Webdav 配置表单
     * @return array<Component>
     */
    protected static function getWebdavOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                self::getWebdavOptionBaseUriFormComponent(),
                self::getWebdavOptionAuthTypeFormComponent(),
                self::getWebdavOptionUsernameFormComponent(),
                self::getWebdavOptionPasswordFormComponent(),
            ])->visible(fn(Get $get): bool => $get('provider') === StorageProvider::Webdav->value),
        ];
    }

    /**
     * Webdav 储存 Base Uri
     * @return Component
     */
    protected static function getWebdavOptionBaseUriFormComponent(): Component
    {
        return TextInput::make('options.base_uri')
            ->label(__('admin/storage.form_fields.webdav_options.base_uri.label'))
            ->placeholder(__('admin/storage.form_fields.webdav_options.base_uri.placeholder'))
            ->required();
    }

    /**
     * Webdav 储存 认证方式
     * @return Component
     */
    protected static function getWebdavOptionAuthTypeFormComponent(): Component
    {
        return Select::make('options.auth_type')
            ->label(__('admin/storage.form_fields.webdav_options.auth_type.label'))
            ->placeholder(__('admin/storage.form_fields.webdav_options.auth_type.placeholder'))
            ->options([
                null => 'Auto',
                Client::AUTH_BASIC => 'Basic',
                Client::AUTH_DIGEST => 'Digest',
                Client::AUTH_NTLM => 'Ntlm',
            ])
            ->native(false)
            ->default(null)
            ->required();
    }

    /**
     * Webdav 储存 用户名
     * @return Component
     */
    protected static function getWebdavOptionUsernameFormComponent(): Component
    {
        return TextInput::make('options.username')
            ->label(__('admin/storage.form_fields.webdav_options.username.label'))
            ->placeholder(__('admin/storage.form_fields.webdav_options.username.placeholder'))
            ->required();
    }

    /**
     * Webdav 储存 密码
     * @return Component
     */
    protected static function getWebdavOptionPasswordFormComponent(): Component
    {
        return TextInput::make('options.password')
            ->label(__('admin/storage.form_fields.webdav_options.password.label'))
            ->placeholder(__('admin/storage.form_fields.webdav_options.password.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 审核驱动
     * @return Component
     */
    protected static function getScanDriverFormComponent(): Component
    {
        return Select::make('scan_driver_id')
            ->label(__('admin/storage.form_fields.scan_driver_id.label'))
            ->placeholder(__('admin/storage.form_fields.scan_driver_id.placeholder'))
            ->helperText(__('admin/storage.form_fields.scan_driver_id.helper_text'))
            ->relationship(
                name: 'scanDrivers',
                titleAttribute: 'name',
                modifyQueryUsing: fn(Builder $query) => $query->where('drivers.type', DriverType::Scan)
            );
    }

    /**
     * 云处理驱动
     * @return Component
     */
    protected static function getProcessDriverFormComponent(): Component
    {
        return Select::make('process_driver_id')
            ->label(__('admin/storage.form_fields.process_driver_id.label'))
            ->placeholder(__('admin/storage.form_fields.process_driver_id.placeholder'))
            ->helperText(__('admin/storage.form_fields.process_driver_id.helper_text'))
            ->relationship(
                name: 'processDrivers',
                titleAttribute: 'name',
                modifyQueryUsing: fn(Builder $query) => $query->where('drivers.type', DriverType::Process)
            );
    }

    /**
     * 图片处理驱动
     * @return Component
     */
    protected static function getHandleDriverFormComponent(): Component
    {
        return Select::make('handle_driver_id')
            ->label(__('admin/storage.form_fields.handle_driver_id.label'))
            ->placeholder(__('admin/storage.form_fields.handle_driver_id.placeholder'))
            ->helperText(__('admin/storage.form_fields.handle_driver_id.helper_text'))
            ->relationship(
                name: 'handleDrivers',
                titleAttribute: 'name',
                modifyQueryUsing: fn(Builder $query) => $query->where('drivers.type', DriverType::Handle)
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorages::route('/'),
            'create' => Pages\CreateStorage::route('/create'),
            'edit' => Pages\EditStorage::route('/{record}/edit'),
        ];
    }
}
