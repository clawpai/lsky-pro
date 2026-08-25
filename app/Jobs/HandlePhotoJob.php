<?php

namespace App\Jobs;

use App\DriverType;
use App\Facades\PhotoHandleService;
use App\Models\Driver;
use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 处理图片
 */
class HandlePhotoJob implements ShouldQueue
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

        /** @var Driver $driver */
        $driver = $this->photo->storage?->handleDrivers()->wherePivot('type', DriverType::Handle)->first();
        if (! is_null($driver)) {
            PhotoHandleService::format($this->photo, $driver->options);
        }
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("图片处理执行失败，{$exception?->getMessage()}", [
            'photo' => $this->photo,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
