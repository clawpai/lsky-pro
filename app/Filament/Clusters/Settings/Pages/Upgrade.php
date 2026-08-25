<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Facades\AppService;
use App\Facades\UpgradeService;
use App\Filament\Clusters\Settings;
use App\Jobs\UpgradeJob;
use App\UpgradeStatus;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class Upgrade extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up';

    protected static string $view = 'filament.clusters.settings.pages.upgrade';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 7;

    /**
     * 检测状态
     * @var UpgradeStatus
     */
    public UpgradeStatus $status = UpgradeStatus::IDLE;

    /**
     * 更新进度字符串
     * @var string
     */
    public string $message = '';

    /**
     * 更新日志
     * @var string
     */
    public string $changelog = '';

    /**
     * hashes.json 文件 md5 值，该文件内容每次更新都是不同的，用来比对判断是否更新成功
     * @var string
     */
    public string $hash = '';

    /**
     * 新版本数据
     * @var array|null
     */
    public ?array $version = null;

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.upgrade.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.upgrade.title');
    }

    public function mount(): void
    {
        $this->changelog = @file_get_contents(base_path('CHANGELOG.md')) ?: '';
    }

    public function check(): void
    {
        try {
            // 检测新版本
            if (File::exists(base_path('upgrade.zip'))) {
                $this->version = [
                    'logo' => '',
                    'name' => 'Upgrade.zip',
                    'version' => '',
                    'changelog' => '',
                    'pushed_at' => date('Y-m-d H:i:s', filemtime(base_path('upgrade.zip'))),
                ];
            } else {
                $versions = AppService::getLskyProVersions();

                $lastVersion = $versions[0] ?? null;

                if ($lastVersion) {
                    if (version_compare($lastVersion['version'], config('app.version'), '>')) {
                        $this->version = $lastVersion;
                    }
                    if (is_null($this->version)) {
                        UpgradeService::clear();
                    }
                }
            }

            // 初始化状态
            $this->setStatus(!is_null($this->version) ? UpgradeStatus::AVAILABLE : UpgradeStatus::UP_TO_DATE);

            $this->updateProgress();

        } catch (Throwable $e) {
            $this->setStatus(UpgradeStatus::UP_TO_DATE);
            $this->setMessage($e->getMessage());
            Notification::make()->title(__('admin/setting.upgrade.server_failed'))->body($e->getMessage())->danger()->send();
        }
    }

    /**
     * 执行升级
     * @return void
     */
    public function upgrade(): void
    {
        // 检测是否在更新中
        if (!File::exists(base_path('upgrading.lock'))) {
            // 创建锁文件
            File::put(base_path('upgrading.lock'), '');
        }

        // 提交更新队列
        dispatch(new UpgradeJob(UpgradeService::getHash()))->delay(3);

        $this->setStatus(UpgradeStatus::UPGRADING);
        $this->setMessage('');
    }

    /**
     * 更新进度
     * @return void
     */
    public function updateProgress(): void
    {
        $status = UpgradeService::getStatus();
        $message = UpgradeService::getMessage();

        if ($message) {
            $this->dispatch('update-progress');
            $this->setMessage(Str::replace(PHP_EOL, '<br>', $message));
        }

        if ($status) {
            $this->setStatus($status);
        }

        // 更新完成后清除缓存中的更新进度
        if ($this->status === UpgradeStatus::COMPLETED) {
            UpgradeService::clear();
            $this->dispatch('upgrade-completed');
        }
    }

    private function setStatus(UpgradeStatus $status): void
    {
        $this->status = $status;
    }

    private function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
