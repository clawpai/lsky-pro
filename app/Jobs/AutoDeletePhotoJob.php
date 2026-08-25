<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 图片自动删除队列
 */
class AutoDeletePhotoJob implements ShouldQueue
{
    use Queueable;

    public Photo $photo;

    /**
     * Create a new job instance.
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo->withoutRelations();

        // 设置延时执行
        if (!is_null($this->photo->expired_at)) {
            $this->delay = $this->photo->expired_at->getTimestamp() - now()->getTimestamp();
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!is_null($this->photo->expired_at)) {
            $this->photo->delete();
        }
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("照片自动删除执行失败，{$exception?->getMessage()}", [
            'photo' => $this->photo,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
