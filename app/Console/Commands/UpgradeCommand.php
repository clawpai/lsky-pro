<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Queue\Console\RestartCommand;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;
use ZipArchive;

/**
 * 更新系统文件
 */
#[AsCommand(name: 'app:upgrade', description: 'Upgrade the application')]
class UpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upgrade {path? : The path relative to the program root}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade the application to the latest version';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $zipFilePath = base_path($this->argument('path') ?: 'upgrade.zip');

        // 创建一个临时目录解压缩文件
        $tempDir = storage_path('app/upgrade-temp');

        try {
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            File::makeDirectory($tempDir);

            // 解压缩文件到临时目录
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath) === true) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                throw new Exception('Failed to open the zip file.');
            }

            // 读取新版本的 hashes.json 文件
            $newHashes = json_decode(File::get($tempDir . '/hashes.json'), true);

            // 检查写入和删除权限
            if (!$this->checkPermissions($newHashes)) {
                throw new Exception('Insufficient permissions for some files or directories.');
            }

            // 读取旧版本的 hashes.json 文件
            $oldHashes = json_decode(File::get(base_path('hashes.json')), true);

            $baseDir = base_path();

            try {
                // 更新文件
                foreach ($newHashes as $relativePath => $hash) {
                    $targetPath = $baseDir . DIRECTORY_SEPARATOR . $relativePath;
                    $tempPath = $tempDir . DIRECTORY_SEPARATOR . $relativePath;

                    // 使用旧版本的 hashes.json 文件进行比较
                    if (isset($oldHashes[$relativePath])) {
                        if ($oldHashes[$relativePath] !== $hash) {
                            File::copy($tempPath, $targetPath);
                            $this->info("Updated: {$relativePath}...100%");
                        }
                    } else {
                        File::ensureDirectoryExists(dirname($targetPath));
                        File::copy($tempPath, $targetPath);
                        $this->info("Added: {$relativePath}...100%");
                    }
                }

                // 删除旧文件
                foreach ($oldHashes as $relativePath => $hash) {
                    if (!isset($newHashes[$relativePath])) {
                        $targetPath = $baseDir . DIRECTORY_SEPARATOR . $relativePath;
                        if (File::exists($targetPath)) {
                            File::delete($targetPath);
                            $this->info("Deleted: {$relativePath}...100%");
                        }
                    }
                }
            } catch (Throwable $e) {
                // 失败后继续执行后续的操作
                $this->error($e->getMessage());
            }

            // 执行数据库迁移
            $this->call(MigrateCommand::class, ['--force' => true]);

            // 重启队列
            $this->call(RestartCommand::class);

            // 删除安装包
            File::delete(base_path('upgrade.zip'));

            $this->info('升级成功！');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return CommandAlias::FAILURE;
        } finally {
            // 删除锁文件
            File::delete(base_path('upgrading.lock'));
            // 删除临时目录
            File::deleteDirectory($tempDir);
        }

        return CommandAlias::SUCCESS;
    }

    /**
     * 检查文件和目录的写入权限
     */
    private function checkPermissions(array $hashes): bool
    {
        $baseDir = base_path();

        foreach ($hashes as $relativePath => $hash) {
            $targetPath = $baseDir . DIRECTORY_SEPARATOR . $relativePath;

            // 如果文件已存在，检查文件是否可写
            if (File::exists($targetPath) && !File::isWritable($targetPath)) {
                $this->error("File not writable: $targetPath");
                return false;
            }
        }

        return true;
    }
}
