<?php

namespace App\Listeners;

use App\DriverType;
use App\Events\UploadFinished;
use App\Jobs\AutoDeletePhotoJob;
use App\Jobs\GeneratePhotoThumbnailJob;
use App\Jobs\HandlePhotoJob;
use App\Jobs\ScanPhotoJob;
use Illuminate\Support\Facades\Bus;

/**
 * 图片上传完成事件监听器
 */
class PhotoUploadComplete
{
    public function handle(UploadFinished $event): void
    {
        // TODO 使用作业链还是作业批处理？
        // 使用作业链处理执行队列
        // 队列依次执行顺序是 图片安全审核->图片处理->自动删除图片->生成缩略图

        $jobs = [];

        if (! in_array(DriverType::Scan, $event->processedDrivers)) {
            $jobs[] = new ScanPhotoJob($event->photo);
        }

        if (! in_array(DriverType::Handle, $event->processedDrivers)) {
            $jobs[] = new HandlePhotoJob($event->photo);
        }

        $jobs[] = new AutoDeletePhotoJob($event->photo);
        
        // 检查储存配置是否需要生成缩略图
        $event->photo->loadMissing('storage');
        $generateThumbnail = data_get($event->photo->storage->options, 'generate_thumbnail', true);
        
        if ($generateThumbnail) {
            $jobs[] = new GeneratePhotoThumbnailJob($event->photo);
        }

        Bus::chain($jobs)->dispatch();
    }
}
