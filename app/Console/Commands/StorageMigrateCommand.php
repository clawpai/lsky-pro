<?php

namespace App\Console\Commands;

use App\Facades\StorageService;
use App\Models\Photo;
use App\Models\Storage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

/**
 * 安全的储存间图片迁移。
 *
 * 状态只以 photos.storage_id 为准：成功切换记录代表文件已核验位于目标；
 * 中断后重新执行会只处理仍指向源储存的图片，因此自然断点续传。
 */
#[AsCommand(name: 'app:storage-migrate')]
class StorageMigrateCommand extends Command
{
    protected $signature = 'app:storage-migrate
        {--from= : 源储存 ID}
        {--to= : 目标储存 ID}
        {--delete-source : 记录切换并核验后删除源文件}
        {--limit=0 : 本次最多处理条数（0=不限）}
        {--ids= : 仅处理指定图片 ID（逗号分隔，用于受控验证或恢复）}
        {--skip-ids-file= : 跳过的图片 ID JSON 文件（用于保留已知双边缺失台账）}
        {--concurrency=8 : 并发数（默认 8，适合 CD2/Google Drive）}';

    protected $description = '安全迁移图片（断点续传、目标核验、受控并发、可选删除源文件）';

    private const MAX_CONCURRENCY = 48;
    private const MAX_RETRIES = 3;

    public function handle(): int
    {
        $fromId = (int) $this->option('from');
        $toId = (int) $this->option('to');
        $limit = max(0, (int) $this->option('limit'));
        $requestedIds = array_values(array_filter(array_map('intval', explode(',', (string) $this->option('ids')))));
        $skipIdsFile = (string) $this->option('skip-ids-file');
        $skippedIds = [];
        if ($skipIdsFile !== '') {
            $decoded = json_decode((string) @file_get_contents($skipIdsFile), true);
            if (! is_array($decoded)) {
                $this->error('跳过 ID 文件无效：' . $skipIdsFile);
                return self::FAILURE;
            }
            $skippedIds = array_values(array_filter(array_map('intval', $decoded)));
        }
        $concurrency = min(self::MAX_CONCURRENCY, max(1, (int) $this->option('concurrency')));
        $deleteSource = (bool) $this->option('delete-source');

        if ($fromId <= 0 || $toId <= 0 || $fromId === $toId) {
            $this->error('请指定两个不同的源/目标储存。');
            return self::FAILURE;
        }

        $from = Storage::find($fromId);
        $to = Storage::find($toId);
        if (is_null($from) || is_null($to)) {
            $this->error('源储存或目标储存不存在。');
            return self::FAILURE;
        }

        $lock = Cache::lock("storage-migrate:{$fromId}:{$toId}", 86400);
        if (! $lock->get()) {
            $this->error('同一源/目标储存的迁移任务正在运行，请等待完成或确认异常任务已停止后再继续。');
            return self::FAILURE;
        }

        try {
            $ids = Photo::query()
                ->where('storage_id', $fromId)
                ->when(! empty($requestedIds), fn ($query) => $query->whereIn('id', $requestedIds))
                ->when(! empty($skippedIds), fn ($query) => $query->whereNotIn('id', $skippedIds))
                ->orderBy('id')
                ->when($limit > 0, fn ($query) => $query->limit($limit))
                ->pluck('id')
                ->all();

            $this->info("源储存: [{$from->id}] {$from->name}");
            $this->info("目标储存: [{$to->id}] {$to->name}");
            $this->info('模式: ' . ($deleteSource ? '第一阶段仅复制并切换记录（源文件将在全部成功后单独清理）' : '复制（保留源文件）'));
            $this->info("并发: {$concurrency}（CD2/Google Drive 默认安全值为 8）");
            $this->info('待处理: ' . count($ids));

            if (empty($ids)) {
                return self::SUCCESS;
            }

            $resultDir = storage_path('app/migration-results');
            if (! is_dir($resultDir) && ! mkdir($resultDir, 0755, true) && ! is_dir($resultDir)) {
                throw new \RuntimeException('无法创建迁移结果目录');
            }

            $results = ['migrated' => 0, 'reused' => 0, 'skipped' => 0, 'failed' => 0];
            $running = [];
            $cursor = 0;
            $bar = $this->output->createProgressBar(count($ids));
            $bar->start();

            while ($cursor < count($ids) || ! empty($running)) {
                while ($cursor < count($ids) && count($running) < $concurrency) {
                    $photoId = (int) $ids[$cursor++];
                    $resultPath = "{$resultDir}/{$fromId}-{$toId}-{$photoId}-" . bin2hex(random_bytes(6)) . '.json';
                    $pid = pcntl_fork();

                    if ($pid === -1) {
                        throw new \RuntimeException('无法创建迁移工作进程');
                    }

                    if ($pid === 0) {
                        try {
                            DB::disconnect();
                            $result = $this->migrateOne($photoId, $fromId, $toId, $deleteSource);
                        } catch (Throwable $e) {
                            $result = ['status' => 'failed', 'photo_id' => $photoId, 'error' => $e->getMessage()];
                        }

                        file_put_contents($resultPath, json_encode($result, JSON_UNESCAPED_UNICODE));
                        exit($result['status'] === 'failed' ? 1 : 0);
                    }

                    $running[$pid] = $resultPath;
                }

                $pid = pcntl_wait($status);
                if ($pid <= 0) {
                    continue;
                }

                $resultPath = $running[$pid] ?? null;
                unset($running[$pid]);
                $result = is_string($resultPath) && is_file($resultPath)
                    ? json_decode((string) file_get_contents($resultPath), true)
                    : ['status' => 'failed', 'error' => '工作进程未返回迁移结果'];
                if (is_string($resultPath)) {
                    @unlink($resultPath);
                }

                $key = $result['status'] ?? 'failed';
                $key = array_key_exists($key, $results) ? $key : 'failed';
                $results[$key]++;
                if ($key === 'failed') {
                    Log::warning('图片迁移失败', $result);
                    $this->newLine();
                    $this->warn("图片 #" . ($result['photo_id'] ?? '?') . ' 迁移失败：' . ($result['error'] ?? '未知错误'));
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("完成：迁移 {$results['migrated']}，复用 {$results['reused']}，跳过 {$results['skipped']}，失败 {$results['failed']}。");
            if ($results['failed'] > 0) {
                $this->warn('失败项仍保持源储存归属和源文件；网络恢复后以相同参数重跑即可断点续传。');
                return self::FAILURE;
            }

            return self::SUCCESS;
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * 迁移单张图：目标文件核验成功后才切换数据库归属，源删除始终在最后。
     *
     * 不把“不同 pathname 的相同 md5”复用为同一文件：那会改变旧 URL 的 pathname，
     * 与链接永久稳定的要求冲突。仅同 pathname 已存在且大小一致时复用目标文件。
     */
    private function migrateOne(int $photoId, int $fromId, int $toId, bool $deleteSource): array
    {
        $photo = Photo::find($photoId);
        if (is_null($photo) || (int) $photo->storage_id !== $fromId) {
            return ['status' => 'skipped', 'photo_id' => $photoId];
        }

        $from = Storage::findOrFail($fromId);
        $to = Storage::findOrFail($toId);
        $fromFs = new Filesystem(StorageService::getAdapter($from->provider, $from->options));
        $toFs = new Filesystem(StorageService::getAdapter($to->provider, $to->options));
        $pathname = $photo->pathname;

        if (! $fromFs->fileExists($pathname)) {
            throw new \RuntimeException('源文件不存在：' . $pathname);
        }
        $sourceSize = $fromFs->fileSize($pathname);
        $reused = $toFs->fileExists($pathname) && $toFs->fileSize($pathname) === $sourceSize;

        if (! $reused) {
            $this->copyAndVerify($fromFs, $toFs, $pathname, $sourceSize, $photoId);
        }

        // 目标完整性必须在切换记录前确认；之后所有失败只会保留源文件，绝不造成记录指向不存在目标。
        if (! $this->verifyTargetFile($toFs, $pathname, $sourceSize)) {
            throw new \RuntimeException('目标文件复验失败，未切换图片记录：' . $pathname);
        }

        // 条件更新防止与另一任务竞争；更新失败时绝不删源。
        $updated = Photo::query()->whereKey($photoId)->where('storage_id', $fromId)->update(['storage_id' => $toId]);
        if ($updated !== 1) {
            return ['status' => 'skipped', 'photo_id' => $photoId];
        }

        // 第一阶段绝不删除源文件。并发进程可随时中断，源清理由全量验证后的独立命令执行。
        // $deleteSource 参数保留仅为兼容后台已有任务，实际删除须显式执行 app:storage-cleanup。

        return ['status' => $reused ? 'reused' : 'migrated', 'photo_id' => $photoId];
    }

    private function verifyTargetFile(Filesystem $filesystem, string $pathname, int $expectedSize): bool
    {
        // CD2/WebDAV 写入后可能有短暂最终一致性延迟；连续核验成功后才允许切换记录。
        foreach ([0, 2, 4, 6] as $delay) {
            if ($delay > 0) {
                sleep($delay);
            }
            try {
                if ($filesystem->fileExists($pathname) && $filesystem->fileSize($pathname) === $expectedSize) {
                    return true;
                }
            } catch (Throwable) {
                // 下一次延迟重试
            }
        }

        return false;
    }

    private function copyAndVerify(Filesystem $fromFs, Filesystem $toFs, string $pathname, int $sourceSize, int $photoId): void
    {
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            $stream = null;
            try {
                // CD2 WebDAV 不支持 MOVE（405）。pathname 在照片表中唯一，直接写入最终路径即可避免临时文件改名。
                $stream = $fromFs->readStream($pathname);
                if (! is_resource($stream)) {
                    throw new \RuntimeException('无法读取源文件流');
                }
                $toFs->writeStream($pathname, $stream);
                fclose($stream);
                $stream = null;

                if (! $this->verifyTargetFile($toFs, $pathname, $sourceSize)) {
                    throw new \RuntimeException('目标文件最终校验失败');
                }
                return;
            } catch (Throwable $e) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
                if ($attempt === self::MAX_RETRIES) {
                    throw $e;
                }
                sleep($attempt * 2);
            }
        }
    }
}
