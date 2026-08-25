<?php

namespace App\Jobs;

use App\Console\Commands\UpdateCommand;
use App\Console\Commands\UpgradeCommand;
use App\Facades\UpgradeService;
use App\UpgradeStatus;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Output\BufferedOutput;

class UpgradeJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Queueable;

    public int $timeout = 600;

    public int $uniqueFor = 600;

    public int $tries = 1;

    public function __construct(public string $hash)
    {
        UpgradeService::clear();
        UpgradeService::setStatus(UpgradeStatus::UPGRADING);
        UpgradeService::setMessage(__('admin/setting.upgrade.processing'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $output = new BufferedOutput();

        // 下载安装包
        if (!File::exists(base_path('upgrade.zip'))) {
            UpgradeService::putMessage(__('admin/setting.upgrade.downloading'));

            if (CommandAlias::FAILURE == Artisan::call(UpdateCommand::class, outputBuffer: $output)) {
                UpgradeService::setStatus(UpgradeStatus::FAILED);
            }

            $updateOutput = $output->fetch();
            UpgradeService::putMessage($updateOutput);
            Log::info('下载更新包', [
                'output' => $updateOutput,
            ]);
        }

        UpgradeService::putMessage(__('admin/setting.upgrade.installing'));

        // 清除之前的输出
        $output->fetch();

        // 执行安装
        if (CommandAlias::FAILURE == Artisan::call(UpgradeCommand::class, outputBuffer: $output)) {
            UpgradeService::setStatus(UpgradeStatus::FAILED);
        }

        $upgradeOutput = $output->fetch();
        UpgradeService::putMessage($upgradeOutput);

        // 检查升级是否成功：通过比较 hash 值判断
        if ($this->hash !== UpgradeService::getHash()) {
            UpgradeService::setStatus(UpgradeStatus::COMPLETED);
        } else {
            // 如果 hash 值没有变化，说明升级失败
            UpgradeService::setStatus(UpgradeStatus::FAILED);
            UpgradeService::putMessage(__('admin/setting.upgrade.hash_unchanged'));
        }

        Log::info('程序发起更新', [
            'output' => $upgradeOutput,
        ]);
    }

    public function uniqueId(): string
    {
        return 'app-upgrade';
    }
}
