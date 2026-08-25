<?php

namespace App\Console\Commands;

use App\Facades\AppService;
use App\Facades\UserService;
use App\Settings\AppSettings;
use Database\Seeders\InitializeSeeder;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Foundation\Console\StorageLinkCommand;
use Illuminate\Foundation\Console\StorageUnlinkCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

#[AsCommand(name: 'app:install', description: 'Install the application')]
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install
        {--app-name= : Application Name}
        {--app-url= : Application URL}
        {--app-license-key= : License key}
        {--db-connection= : Database Connection}
        {--db-host= : Database Host}
        {--db-port= : Database Port}
        {--db-database= : Database Name}
        {--db-username= : Database User}
        {--db-password= : Database Password}
        {--admin-username= : Admin Name}
        {--admin-email= : Admin Email}
        {--admin-password= : Admin Password}
        {--f|force : Forced overlay installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Lsky Pro+ - 2x.nz特供离线版';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            if (AppService::isInstalled() && !$this->option('force')) {
                error('Lsky Pro+ - 2x.nz特供离线版 已经安装过了，如需重新安装请删除程序根目录 installed.lock 和 .env 文件');
                return CommandAlias::FAILURE;
            }

            File::delete(base_path('.env'));

            // 运行环境检测
            $checks = collect(AppService::getAppRequirements());

            table(['名称', '说明', '检测状态'], $checks->map(function ($value) {
                return [$value['name'], $value['description'], $this->getCheckStatusText($value['check'])];
            })->values()->toArray());

            if ($checks->contains('check', false)) {
                error('The environment does not meet the requirements, please check the environment.');
                return CommandAlias::FAILURE;
            }

            $appName = $this->option('app-name') ?: $this->getAppName();
            $appUrl = $this->option('app-url') ?: $this->getAppUrl();
            $appLicenseKey = $this->option('app-license-key') ?: $this->getLicenseKey();

            $appConfig = [
                'app.name' => $appName,
                'app.url' => $appUrl,
                'app.license_key' => $appLicenseKey,
            ];

            if (! spin(function () use ($appConfig) {
                return AppService::verifyLicense($appConfig['app.license_key'], $appConfig['app.url']);
            }, 'Validating...')) {
                throw new Exception('许可证验证失败，请检查应用域名、许可证密钥是否正确！');
            }

            $env = [
                'APP_ENV' => 'production',
                'APP_NAME' => $appName,
                'APP_URL' => $appUrl,
                'DB_CONNECTION' => $this->option('db-connection') ?: $this->getDbConnection(),
            ];

            $dbConfig = ['driver' => $env['DB_CONNECTION']];

            if ($env['DB_CONNECTION'] !== 'sqlite') {
                $env = array_merge($env, [
                    'DB_HOST' => $this->option('db-host') ?: $this->getDbHost(),
                    'DB_PORT' => $this->option('db-port') ?: $this->getDbPort(),
                    'DB_DATABASE' => $this->option('db-database') ?: $this->getDbDatabase(),
                    'DB_USERNAME' => $this->option('db-username') ?: $this->getDbUsername(),
                    'DB_PASSWORD' => $this->option('db-password') ?: $this->getDbPassword(),
                ]);
                $dbConfig = [
                    'host' => $env['DB_HOST'],
                    'port' => $env['DB_PORT'],
                    'database' => $env['DB_DATABASE'],
                    'username' => $env['DB_USERNAME'],
                    'password' => $env['DB_PASSWORD'],
                ];
            } else {
                // 创建 sqlite 文件
                File::put($this->getSqliteFilepath(), '');
            }

            $user = [
                'username' => $this->option('admin-username') ?: $this->getSuperAdminUsername(),
                'email' => $this->option('admin-email') ?: $this->getSuperAdminEmail(),
                'password' => $this->option('admin-password') ?: $this->getSuperAdminPassword(),
            ];

            $connection = "database.connections.{$env['DB_CONNECTION']}";
            Config::set($connection, array_merge(Config::get($connection), array_filter($dbConfig)));
            Config::set('database.default', $env['DB_CONNECTION']);

            // 设置程序名以及域名
            Config::set('app.name', $appConfig['app.name']);
            Config::set('app.url', $appConfig['app.url']);

            // 确保所有数据库连接都已关闭
            DB::disconnect();

            try {
                DB::connection()->getPdo();
            } catch (Throwable $e) {
                throw new Exception("数据库连接失败: {$e->getMessage()}");
            }

            // 1.生成 .env 文件
            AppService::generateEnvFile();

            // 2.生成 key
            if (CommandAlias::SUCCESS !== $this->call(KeyGenerateCommand::class, ['--force' => true])) {
                throw new Exception('key 生成失败');
            }

            // 3.执行数据库迁移
            if (CommandAlias::SUCCESS !== $this->call(FreshCommand::class, ['--force' => true])) {
                throw new Exception('数据库初始化失败');
            }

            // 4.修改系统配置
            $appSettings = app(AppSettings::class);
            $appSettings->fill(Arr::undot($appConfig)['app']);
            $appSettings->save();

            // 5.填充默认数据
            if (CommandAlias::SUCCESS !== $this->call(SeedCommand::class, ['--class' => InitializeSeeder::class, '--force' => true])) {
                throw new Exception('默认数据填充失败');
            }

            // 6.创建超级管理员
            UserService::createDefaultSuperAdmin(array_merge($user, [
                'name' => $user['username'],
                'password' => Hash::make($user['password']),
                'email_verified_at' => now(),
            ]));

            // 7.重新创建储存默认符号链接
            if (CommandAlias::SUCCESS !== ($this->call(StorageUnlinkCommand::class) + $this->call(StorageLinkCommand::class))) {
                throw new Exception('默认储存符号链接创建失败');
            }

            // 8.写入环境变量
            AppService::writeNewEnvironmentFileWith($env);

            // 9.清理缓存
            // $this->call(OptimizeClearCommand::class);

            File::put(base_path('installed.lock'), '');
        } catch (Throwable $e) {
            $this->init();
            error("安装失败：{$e->getMessage()}");
            error("详情错误信息：{$e->getTraceAsString()}");
            return CommandAlias::FAILURE;
        }

        $this->welcome();

        return CommandAlias::SUCCESS;
    }

    protected function getCheckStatusText(bool $status): string
    {
        $color = $status ? 'green' : 'red';
        $text = $status ? '√' : '×';
        return "<fg={$color}>{$text}</>";
    }

    protected function getAppName(): string
    {
        return text(
            label: '应用名称',
            placeholder: '请输入应用名称',
            default: 'Lsky Pro+ - 2x.nz特供离线版',
            required: true,
        );
    }

    protected function getAppUrl(): string
    {
        return text(
            label: '应用域名',
            placeholder: '请输入应用域名',
            default: 'http://localhost',
            required: true,
            validate: ['url' => 'required|url']
        );
    }

    protected function getLicenseKey(): string
    {
        return text(
            label: '授权密钥：',
            placeholder: '请输入产品授权密钥',
            required: true,
            hint: '购买程序后在官网个人中心可获得',
        );
    }

    protected function getDbConnection(): string
    {
        return select(
            label: '数据库类型：',
            options: [
                'sqlite' => 'SQLite 3.35.0+',
                'mysql' => 'MySQL 5.7+',
                'mariadb' => 'MariaDB 10.3+',
                'pgsql' => 'PostgreSQL 10.0+',
                'sqlsrv' => 'SQL Server 2017+',
            ],
            default: 'sqlite',
            hint: '默认为 Sqlite，使用其他数据库请先手动创建数据。若使用频率较高（大量、频繁的上传图片等）推荐使用 mysql。',
            required: true,
        );
    }

    protected function getDbHost(): string
    {
        return text(
            label: '数据库连接地址（默认 localhost）：',
            placeholder: '请输入数据库连接地址',
            default: 'localhost',
            required: true,
        );
    }

    protected function getDbPort(): string
    {
        return text(
            label: '数据库连接端口（默认 3306）：',
            placeholder: '请输入数据库连接端口',
            default: '3306',
            required: true,
        );
    }

    protected function getDbDatabase(): string
    {
        return text(
            label: '数据库名称：',
            placeholder: '请输入数据库名称',
            required: true,
        );
    }

    protected function getDbUsername(): string
    {
        return text(
            label: '数据库连接用户名：',
            placeholder: '请输入数据库连接用户名',
            default: 'root',
            required: true,
        );
    }

    protected function getDbPassword(): string
    {
        return password(
            label: '数据库连接密码：',
            placeholder: '请输入数据库连接密码',
            required: true,
        );
    }

    protected function getSqliteFilepath(): string
    {
        return Config::get('database.connections.sqlite.database');
    }

    protected function getSuperAdminUsername(): string
    {
        return text(
            label: '管理员用户名',
            placeholder: '请输入管理员用户名',
            default: 'admin',
            required: true,
            validate: ['username' => 'required|alpha_dash']
        );
    }

    protected function getSuperAdminEmail(): string
    {
        return text(
            label: '管理员邮箱',
            placeholder: '请输入管理员邮箱',
            required: true,
            validate: ['email' => 'required|email']
        );
    }

    protected function getSuperAdminPassword(): string
    {
        return password(
            label: '管理员密码',
            placeholder: '请输入管理员密码',
            required: true,
        );
    }

    /**
     * 安装前初始化
     *
     * @return void
     */
    protected function init(): void
    {
        File::delete([
            base_path('.env'), // 删除 .env 文件
            $this->getSqliteFilepath(), // 删除 SQLite 数据库文件
            base_path('installed.lock'), // 删除锁文件
        ]);
    }

    protected function welcome(): void
    {
        $version = config('app.version');
        $url = trim(app(AppSettings::class)->url, '/').'/admin';

        info(<<<EOF
               __         __           ___               __ 
              / /   ___  / /__ __ __  / _ \ ____ ___  __/ /_
             / /__ (_-< /  '_// // / / ___// __// _ \/_  __/
            /____//___//_/\_\ \_, / /_/   /_/   \___/ /_/   
                             /___/                          
        EOF
        );

        note(<<<EOF
            欢迎使用 Lsky Pro+ - 2x.nz特供离线版 {$version}，程序已安装成功，后台地址：{$url}
            注意，若要正常使用，还需要根据文档 https://docs.lsky.pro/guide/install 配置消息队列，否则会导致图片无法正常删除、无法发送邮件、无法生成缩略图等异常。
            如果需要重新安装请删除程序根目录 installed.lock 和 .env 文件，使用过程中遇到问题请访问 https://docs.lsky.pro 获取帮助。
        EOF
        );
    }
}
