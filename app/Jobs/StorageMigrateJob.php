<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 储存间图片迁移任务：同一源/目标只允许一个队列任务，命令内部另有跨进程锁。
 */
class StorageMigrateJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $timeout = 86400;
    public int $uniqueFor = 86400;

    public function __construct(
        public int $fromId,
        public int $toId,
        public bool $deleteSource = false,
        public int $concurrency = 8,
    ) {
        $this->concurrency = max(1, min(48, $this->concurrency));
    }

    public function uniqueId(): string
    {
        return "storage-migrate:{$this->fromId}:{$this->toId}";
    }

    public function handle(): void
    {
        $arguments = [
            '--from' => $this->fromId,
            '--to' => $this->toId,
            '--concurrency' => $this->concurrency,
        ];
        if ($this->deleteSource) {
            $arguments['--delete-source'] = true;
        }

        Log::info('储存迁移任务开始', ['from' => $this->fromId, 'to' => $this->toId, 'concurrency' => $this->concurrency, 'delete_source' => $this->deleteSource]);
        $exitCode = Artisan::call('app:storage-migrate', $arguments);
        Log::info('储存迁移任务结束', ['from' => $this->fromId, 'to' => $this->toId, 'exit_code' => $exitCode]);

        if ($exitCode !== 0) {
            throw new \RuntimeException('迁移命令未完成，可在网络恢复后用同一参数重新发起以断点续传。');
        }
    }

    public function fail(?Throwable $exception = null): void
    {
        Log::error('储存迁移任务失败', ['from' => $this->fromId, 'to' => $this->toId, 'error' => $exception?->getMessage()]);
    }
}
