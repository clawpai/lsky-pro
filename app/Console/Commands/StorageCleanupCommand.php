<?php

namespace App\Console\Commands;

use App\Facades\StorageService;
use App\Models\Photo;
use App\Models\Storage;
use Illuminate\Console\Command;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

/**
 * 在迁移完成后安全清理源储存冗余副本。
 * 每个删除前强制验证图片记录已指向目标、目标文件存在且大小与源文件一致。
 */
#[AsCommand(name: 'app:storage-cleanup')]
class StorageCleanupCommand extends Command
{
    protected $signature = 'app:storage-cleanup
        {--from= : 源储存 ID}
        {--to= : 目标储存 ID}
        {--limit=0 : 本次最多清理条数（0=不限）}
        {--ids= : 仅清理指定图片 ID（逗号分隔，用于受控验证）}';

    protected $description = '安全清理已迁移图片的源储存冗余副本（先核验目标，后删除源）';

    public function handle(): int
    {
        $fromId = (int) $this->option('from');
        $toId = (int) $this->option('to');
        $limit = max(0, (int) $this->option('limit'));
        $requestedIds = array_values(array_filter(array_map('intval', explode(',', (string) $this->option('ids')))));

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

        $fromFs = new Filesystem(StorageService::getAdapter($from->provider, $from->options));
        $toFs = new Filesystem(StorageService::getAdapter($to->provider, $to->options));
        $photos = Photo::query()->where('storage_id', $toId)
            ->when(! empty($requestedIds), fn ($query) => $query->whereIn('id', $requestedIds))
            ->orderBy('id')
            ->when($limit > 0, fn ($query) => $query->limit($limit))
            ->get(['id', 'pathname']);

        $deleted = 0;
        $skipped = 0;
        $failed = 0;
        $bar = $this->output->createProgressBar($photos->count());
        $bar->start();

        foreach ($photos as $photo) {
            $bar->advance();
            try {
                if (! $fromFs->fileExists($photo->pathname)) {
                    $skipped++;
                    continue;
                }

                $sourceSize = $fromFs->fileSize($photo->pathname);
                if (! $toFs->fileExists($photo->pathname) || $toFs->fileSize($photo->pathname) !== $sourceSize) {
                    $failed++;
                    $this->newLine();
                    $this->warn("跳过 #{$photo->id}：目标文件不存在或大小不一致，源文件已保留。");
                    continue;
                }

                $fromFs->delete($photo->pathname);
                $deleted++;
            } catch (Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn("清理失败 #{$photo->id}：{$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("清理完成：删除 {$deleted}，源文件本已不存在 {$skipped}，保留待处理 {$failed}。");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
