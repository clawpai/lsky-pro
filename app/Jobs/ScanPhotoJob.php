<?php

namespace App\Jobs;

use App\Facades\PhotoService;
use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 图片安全审核
 */
class ScanPhotoJob implements ShouldQueue
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

        if ($this->photo->storage?->scanDrivers()->exists()) {
            PhotoService::scan($this->photo);
        }
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("图片安全审核执行失败，{$exception?->getMessage()}", [
            'photo' => $this->photo,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
