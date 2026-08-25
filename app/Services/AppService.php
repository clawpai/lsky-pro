<?php

declare(strict_types=1);

namespace App\Services;

use App\CouponType;
use App\Exceptions\ServiceException;
use App\FeedbackType;
use App\MailProvider;
use App\PageType;
use App\PaymentProvider;
use App\ScanProvider;
use App\Settings\AppSettings;
use App\SmsProvider;
use App\SocialiteProvider;
use App\StorageProvider;
use DateTimeZone;
use Filament\Support\Colors\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Vips\Driver;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\FileExtension;
use Intervention\Image\ImageManager;
use libphonenumber\PhoneNumberUtil;
use Locale;
use Throwable;

class AppService
{
    /**
     * 系统是否安装了
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        return File::exists(base_path('installed.lock'));
    }

    /**
     * 获取软件协议
     * @return string
     */
    public function getAgreement(): string
    {
        return @file_get_contents(base_path('LICENSE.md')) ?: '';
    }

    /**
     * 是否同意了软件协议
     * @return bool
     */
    public function isAgreeAgreement(): bool
    {
        return strlen(@file_get_contents(base_path('installed.lock')) ?: '') > 0;
    }

    /**
     * 初始化 app
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // 初始化配置
        $appSettings = app(AppSettings::class)->toArray();

        // 替换系统配置
        foreach ($appSettings as $key => $value) {
            $name = "app.{$key}";
            if (Config::has($name)) {
                Config::set($name, $value);
            }
        }

        // 替换邮件配置
        Config::set('mail.from.name', $appSettings['mail_from_name']);
        Config::set('mail.from.address', $appSettings['mail_from_address']);

        // 图片处理驱动设置
        Config::set('image.driver', $appSettings['image_driver']);

        // 设置本地默认储存的 url
        $appUrl = Config::get('app.url');
        if ($appUrl) {
            Config::set('filesystems.disks.public.url', $appUrl.'/storage');
        }
    }

    /**
     * 获取主题列表
     *
     * @return array
     */
    public function getThemes(): array
    {
        $directories = File::directories(resource_path('views/themes'));

        $themes = [];

        foreach ($directories as $directory) {
            $config = json_decode(@file_get_contents("{$directory}/config.json") ?: '', true);
            $themes[] = array_merge([
                'id' => basename($directory),
            ], $config);
        }

        return $themes;
    }

    /**
     * 生成 .env 文件
     *
     * @return void
     */
    public function generateEnvFile(): void
    {
        File::copy(base_path('.env.example'), base_path('.env'));
    }

    /**
     * 验证授权
     *
     * @param string $licenseKey 授权 key
     * @param string $domain 授权域名
     * @return bool
     * @throws ServiceException
     */
    public function verifyLicense(string $licenseKey, string $domain): bool
    {
        // 公益版本：直接返回授权验证成功
        return true;
    }

    /**
     * 获取 Lsky Pro+ - 2x.nz特供离线版 产品版本号列表
     *
     * @return array
     * @throws ServiceException
     */
    public function getLskyProVersions(): array
    {
        // 公益版本：返回当前版本信息
        return [
            'current_version' => config('app.version', '2.2.3'),
            'latest_version' => config('app.version', '2.2.3'),
            'is_latest' => true,
            'message' => '公益版本'
        ];
    }

    /**
     * 通过 url 获取域名，包含端口
     *
     * @param string|null $url
     * @return string
     */
    public function getDomain(?string $url): string
    {
        if (! $url) {
            return '';
        }

        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['host'])) {
            return '';
        }

        $host = $parsedUrl['host'];
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

        return $host . $port;
    }

    /**
     * 获取系统安装要求
     *
     * @return array
     */
    public function getAppRequirements(): array
    {
        return [
            [
                'name' => 'PHP >= 8.2',
                'check' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'description' => 'PHP 版本必须大于等于 8.2'
            ],
            [
                'name' => 'Ctype PHP 扩展',
                'check' => extension_loaded('ctype'),
                'description' => '用于字符类型检测'
            ],
            [
                'name' => 'cURL PHP 扩展',
                'check' => extension_loaded('curl'),
                'description' => '用于发送 HTTP 请求'
            ],
            [
                'name' => 'DOM PHP 扩展',
                'check' => extension_loaded('dom'),
                'description' => '用于操作 XML 文档'
            ],
            [
                'name' => 'Fileinfo PHP 扩展',
                'check' => extension_loaded('fileinfo'),
                'description' => '用于检测文件的 MIME 类型'
            ],
            [
                'name' => 'Filter PHP 扩展',
                'check' => extension_loaded('filter'),
                'description' => '用于数据过滤'
            ],
            [
                'name' => 'Hash PHP 扩展',
                'check' => extension_loaded('hash'),
                'description' => '用于数据散列'
            ],
            [
                'name' => 'Mbstring PHP 扩展',
                'check' => extension_loaded('mbstring'),
                'description' => '用于多字节字符串处理'
            ],
            [
                'name' => 'OpenSSL PHP 扩展',
                'check' => extension_loaded('openssl'),
                'description' => '用于数据加密'
            ],
            [
                'name' => 'PCRE PHP 扩展',
                'check' => extension_loaded('pcre'),
                'description' => '用于正则表达式处理'
            ],
            [
                'name' => 'PDO PHP 扩展',
                'check' => extension_loaded('pdo'),
                'description' => '用于数据库访问'
            ],
            [
                'name' => 'Session PHP 扩展',
                'check' => extension_loaded('session'),
                'description' => '用于会话管理'
            ],
            [
                'name' => 'Tokenizer PHP 扩展',
                'check' => extension_loaded('tokenizer'),
                'description' => '用于 PHP 代码的标记化'
            ],
            [
                'name' => 'XML PHP 扩展',
                'check' => extension_loaded('xml'),
                'description' => '用于解析 XML'
            ],
            [
                'name' => 'PCNTL PHP 拓展',
                'check' => extension_loaded('pcntl'),
                'description' => '用于处理信号和异步任务'
            ],
            [
                'name' => 'Posix PHP 拓展',
                'check' => extension_loaded('posix'),
                'description' => '用于处理进程'
            ],
            [
                'name' => 'Zip PHP 拓展',
                'check' => extension_loaded('zip'),
                'description' => '用于处理 zip 文件'
            ],
            [
                'name' => 'ImageMagick PHP 拓展',
                'check' => extension_loaded('imagick'),
                'description' => '用于处理图片文件'
            ],
            [
                'name' => 'proc_* 函数',
                'check' => function_exists('proc_open') &&
                    function_exists('proc_close') &&
                    function_exists('proc_get_status') &&
                    function_exists('proc_terminate'),
                'description' => '用于队列任务的生命周期管理'
            ],
            [
                'name' => 'pcntl_* 函数',
                'check' => function_exists('pcntl_fork') && function_exists('pcntl_wait'),
                'description' => '用于进程管理'
            ],
            [
                'name' => 'exec、shell_exec、system 函数',
                'check' => function_exists('exec') && function_exists('shell_exec') && function_exists('system'),
                'description' => '执行系统命令'
            ],
            [
                'name' => 'symlink、readlink 函数',
                'check' => function_exists('symlink') && function_exists('readlink'),
                'description' => '创建、读取符号连接'
            ],
        ];
    }

    /**
     * 写入配置到当前环境变量
     *
     * @param array $data
     * @return void
     */
    public function writeNewEnvironmentFileWith(array $data): void
    {
        $envFilePath = app()->environmentFilePath();
        $envFileContent = file($envFilePath, FILE_IGNORE_NEW_LINES);

        foreach ($data as $key => $value) {
            // 检查值中是否包含特殊字符或空格
            if (preg_match('/\s|["\']/', $value)) {
                $value = '"' . addcslashes($value, '"') . '"';
            }

            $found = false;
            foreach ($envFileContent as &$line) {
                if (preg_match("/^#?\s*{$key}=.*/", $line)) {
                    $line = "{$key}={$value}";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $envFileContent[] = "{$key}={$value}";
            }
        }

        file_put_contents($envFilePath, implode(PHP_EOL, $envFileContent) . PHP_EOL);
    }

    /**
     * 获取请求 ip
     * @param Request $request
     * @return string
     */
    public function getRequestIp(Request $request): string
    {
        $ip = null;
        $priority = [app(AppSettings::class)->ip_gain_method];

        foreach ($priority as $key) {
            switch ($key) {
                case 'X-Forwarded-For':
                    if ($request->hasHeader('X-Forwarded-For')) {
                        $ip = $request->header('X-Forwarded-For');
                    }
                    break;
                case 'X-Real-Ip':
                    if ($request->hasHeader('X-Real-Ip')) {
                        $ip = $request->header('X-Real-Ip');
                    }
                    break;
                case 'CF-Connecting-IP':
                    if ($request->hasHeader('CF-Connecting-IP')) {
                        $ip = $request->header('CF-Connecting-IP');
                    }
                    break;
                case 'True-Client-IP':
                    if ($request->hasHeader('True-Client-IP')) {
                        $ip = $request->header('True-Client-IP');
                    }
                    break;
                case 'X-Cluster-Client-IP':
                    if ($request->hasHeader('X-Cluster-Client-IP')) {
                        $ip = $request->header('X-Cluster-Client-IP');
                    }
                    break;
                case 'Forwarded':
                    if ($request->hasHeader('Forwarded')) {
                        $ip = $request->header('Forwarded');
                    }
                    break;
                case 'Remote-Addr':
                case 'auto':
                    $ip = $request->ip();
                    break;
            }

            // 如果找到一个 IP，退出循环
            if ($ip) {
                break;
            }
        }

        // 处理 X-Forwarded-For 可能包含多个 IP 地址的情况
        if ($ip && str_contains($ip, ',')) {
            $ip = explode(',', $ip)[0];
        }

        // 处理 Forwarded 头字段可能包含多个部分的情况
        if ($ip && str_contains($ip, ';')) {
            $ip = explode(';', $ip)[0];
        }

        return $ip;
    }

    /**
     * 获取所有国家代码和国家
     * @return array<string, string>
     */
    public function getAllCountryCodes(): array
    {
        return collect($this->getAllCountries())->pluck('name', 'id')->toArray();
    }

    /**
     * 获取所有国家
     * @return array
     */
    public function getAllCountries(): array
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        return collect($phoneUtil->getSupportedRegions())->map(function (string $country) use ($phoneUtil) {
            return [
                'id' => strtolower($country),
                'name' => Locale::getDisplayRegion('-' . $country, 'en'),
                'code' => $phoneUtil->getCountryCodeForRegion(strtolower($country))
            ];
        })->toArray();
    }

    /**
     * 获取支付异步回调地址
     *
     * @param string $provider
     * @return string
     */
    public function getPaymentNotifyUrl(string $provider): string
    {
        return match ($provider) {
            PaymentProvider::Alipay->value,
            PaymentProvider::UniPay->value,
            PaymentProvider::Wechat->value,
            PaymentProvider::EPay->value => url('payment/callback'),
        };
    }

    /**
     * 获取所有优惠券类型
     *
     * @return array
     */
    public function getAllCouponTypes(): array
    {
        return collect(CouponType::cases())->map(function (CouponType $type) {
            return ['value' => $type->value, 'name' => __('admin.coupon_types.' . $type->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有页面类型
     *
     * @return array
     */
    public function getAllPageTypes(): array
    {
        return collect(PageType::cases())->map(function (PageType $type) {
            return ['value' => $type->value, 'name' => __('admin.page_types.' . $type->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有意见与反馈类型
     *
     * @return array
     */
    public function getAllFeedbackTypes(): array
    {
        return collect(FeedbackType::cases())->map(function (FeedbackType $type) {
            return ['value' => $type->value, 'name' => __('admin.feedback_types.' . $type->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有社会化登录驱动
     *
     * @return array
     */
    public function getAllSocialiteProviders(): array
    {
        return collect(SocialiteProvider::cases())->map(function (SocialiteProvider $provider) {
            return ['value' => $provider->value, 'name' => __('admin.socialite_providers.' . $provider->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有支付驱动
     *
     * @return array
     */
    public function getAllPaymentProviders(): array
    {
        return collect(PaymentProvider::cases())->map(function (PaymentProvider $driver) {
            return ['value' => $driver->value, 'name' => __('admin.payment_providers.' . $driver->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有审核驱动
     *
     * @return array
     */
    public function getAllScanProviders(): array
    {
        return collect(ScanProvider::cases())->map(function (ScanProvider $driver) {
            return ['value' => $driver->value, 'name' => __('admin.scan_providers.' . $driver->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有短信驱动网关
     *
     * @return array
     */
    public function getAllSmsProviders(): array
    {
        return collect(SmsProvider::cases())->map(function (SmsProvider $type) {
            return ['value' => $type->value, 'name' => __('admin.sms_providers.' . $type->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有支持的邮件驱动
     *
     * @return array
     */
    public function getAllMailProviders(): array
    {
        return collect(MailProvider::cases())->map(function (MailProvider $driver) {
            return ['value' => $driver->value, 'name' => Str::studly($driver->name)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有储存驱动类型
     *
     * @return array
     */
    public function getAllStorageProviders(): array
    {
        return collect(StorageProvider::cases())->map(function (StorageProvider $type) {
            return ['value' => $type->value, 'name' => __('admin.storage_providers.' . $type->value)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有ip获取方式
     *
     * @return array<string, string>
     */
    public function getAllIpGainMethods(): array
    {
        return [
            'auto' => 'Auto',
            'X-Forwarded-For' => 'X-Forwarded-For',
            'X-Real-Ip' => 'X-Real-Ip',
            'Remote-Addr' => 'Remote-Addr',
            'CF-Connecting-IP' => 'CF-Connecting-IP',
            'True-Client-IP' => 'True-Client-IP',
            'X-Cluster-Client-IP' => 'X-Cluster-Client-IP',
            'Forwarded' => 'Forwarded',
        ];
    }

    /**
     * 获取系统所有配色
     * @return array<string, string>
     */
    public function getAllColors(): array
    {
        return collect(Color::all())->map(function (array $color, $key) {
            return ['value' => $key, 'name' => Str::studly($key)];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取所有支持的时区
     *
     * @return array<string, string>
     */
    public function getAllTimezones(): array
    {
        return collect(DateTimeZone::listIdentifiers())->transform(function ($item) {
            return ['value' => $item, 'name' => $item];
        })->pluck('name', 'value')->toArray();
    }

    /**
     * 获取系统支持的语言
     *
     * @return array<string, string>
     */
    public function getAllLanguages(): array
    {
        return ['en' => 'English', 'zh_CN' => '简体中文'];
    }

    /**
     * 使用 Intervention Image 库获取支持的图片类型
     *
     * @return array
     */
    public function getAllSupportedImageTypes(): array
    {
        $manager = ImageManager::withDriver(config('image.driver'));

        return collect($this->getAllImageTypes())
            ->filter(function (string $value) use ($manager) {
                return $manager->driver()->supports($value);
            })->toArray();
    }

    /**
     * 使用 Intervention Image 库获取所有图片类型
     *
     * @return array<string, string>
     */
    public function getAllImageTypes(): array
    {
        return collect(FileExtension::cases())->map(fn(FileExtension $item) => $item->value)->toArray();
    }

    /**
     * 获取系统支持的处理库驱动
     *
     * @return array
     */
    public function getInterventionImageDrivers(): array
    {
        $drivers = [
            \Intervention\Image\Drivers\Imagick\Driver::class => 'Imagick',
        ];

        // 检测是否 vips
        try {
            (new Driver())->checkHealth();
            $drivers[Driver::class] = 'Libvips';
        } catch (DriverException $e) {
        }

        return $drivers;
    }

    /**
     * 获取社会化登录回调地址
     *
     * @param string $provider
     * @return string
     */
    public function getSocialiteRedirectUrl(string $provider): string
    {
        $domain = trim(app(AppSettings::class)->url, '/');
        return match ($provider) {
            SocialiteProvider::Github->value => $domain,
            SocialiteProvider::QQ->value => "{$domain}/login;{$domain}/register;{$domain}/user/profile",
        };
    }

    /**
     * 获取全部文件命名规则
     *
     * @return array
     */
    public function getAllFileNamingRules(): array
    {
        return [
            '{Y}' => [
                'name' => __('admin.storage_naming_rule.Y.name'),
                'description' => __('admin.storage_naming_rule.Y.description'),
            ],
            '{y}' => [
                'name' => __('admin.storage_naming_rule.y.name'),
                'description' => __('admin.storage_naming_rule.y.description'),
            ],
            '{m}' => [
                'name' => __('admin.storage_naming_rule.m.name'),
                'description' => __('admin.storage_naming_rule.m.description'),
            ],
            '{d}' => [
                'name' => __('admin.storage_naming_rule.d.name'),
                'description' => __('admin.storage_naming_rule.d.description'),
            ],
            '{Ymd}' => [
                'name' => __('admin.storage_naming_rule.Ymd.name'),
                'description' => __('admin.storage_naming_rule.Ymd.description'),
            ],
            '{filename}' => [
                'name' => __('admin.storage_naming_rule.filename.name'),
                'description' => __('admin.storage_naming_rule.filename.description'),
            ],
            '{ext}' => [
                'name' => __('admin.storage_naming_rule.ext.name'),
                'description' => __('admin.storage_naming_rule.ext.description'),
            ],
            '{time}' => [
                'name' => __('admin.storage_naming_rule.time.name'),
                'description' => __('admin.storage_naming_rule.time.description'),
            ],
            '{uniqid}' => [
                'name' => __('admin.storage_naming_rule.uniqid.name'),
                'description' => __('admin.storage_naming_rule.uniqid.description'),
            ],
            '{md5}' => [
                'name' => __('admin.storage_naming_rule.md5.name'),
                'description' => __('admin.storage_naming_rule.md5.description'),
            ],
            '{sha1}' => [
                'name' => __('admin.storage_naming_rule.sha1.name'),
                'description' => __('admin.storage_naming_rule.sha1.description'),
            ],
            '{uuid}' => [
                'name' => __('admin.storage_naming_rule.uuid.name'),
                'description' => __('admin.storage_naming_rule.uuid.description'),
            ],
            '{uid}' => [
                'name' => __('admin.storage_naming_rule.uid.name'),
                'description' => __('admin.storage_naming_rule.uid.description'),
            ],
        ];
    }

    /**
     * 获取云处理规则所有支持的参数
     *
     * @return array
     */
    public function getProcessSupportedParams(): array
    {
        return Arr::map([
            ['name' => 'or', 'description' => '旋转图像。'],
            ['name' => 'flip', 'description' => '翻转图像。'],
            ['name' => 'crop', 'description' => '将图像裁剪为特定尺寸。'],
            ['name' => 'w', 'description' => '	设置图像的宽度（以像素为单位）。'],
            ['name' => 'h', 'description' => '设置图像的高度（以像素为单位）。'],
            ['name' => 'fit', 'description' => '设置图像如何适应其目标尺寸。'],
            ['name' => 'dpr', 'description' => '使整体图像尺寸增大几倍。'],
            ['name' => 'bri', 'description' => '调整图像亮度。'],
            ['name' => 'con', 'description' => '调整图像对比度。'],
            ['name' => 'gam', 'description' => '调整图像伽玛。'],
            ['name' => 'sharp', 'description' => '使图像更加清晰。'],
            ['name' => 'blur', 'description' => '为图像添加模糊效果。'],
            ['name' => 'pixel', 'description' => '对图像应用像素化效果。'],
            ['name' => 'filt', 'description' => '对图像应用滤镜效果。'],
            ['name' => 'bg', 'description' => '设置图像的背景颜色。'],
            ['name' => 'border', 'description' => '为图像添加边框。'],
            ['name' => 'q', 'description' => '定义图像的质量。'],
            ['name' => 'fm', 'description' => '将图像编码为特定格式。'],
        ], function (array $item) {
            $link = "<a target='_blank' href=\"https://docs.lsky.pro/guide/process#{$item['name']}\">查看详情</a>";
            return ['name' => $item['name'], 'description' => $item['description'].$link];
        });
    }
}
