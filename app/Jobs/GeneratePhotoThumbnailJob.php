<?php

namespace App\Jobs;

use App\Facades\PhotoService;
use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 生成图片缩略图
 */
class GeneratePhotoThumbnailJob implements ShouldQueue
{
    use Queueable;

    public Photo $photo;

    /**
     * Create a new job instance.
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->photo->loadMissing('storage');
        
        $maxSize = data_get($this->photo->storage->options, 'thumbnail_max_size', 800);
        $quality = data_get($this->photo->storage->options, 'thumbnail_quality', 90);
        
        PhotoService::generateThumbnail($this->photo, $maxSize, $quality);
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("图片生成缩略图生成失败，{$exception?->getMessage()}", [
            'photo' => $this->photo,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
