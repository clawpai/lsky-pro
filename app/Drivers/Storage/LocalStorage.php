<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use Illuminate\Support\Facades\File;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * 本地储存
 */
class LocalStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        // 如果文件夹不存在则使用默认的 public 驱动根目录，防止出现异常
        $folder = $this->getPrefix();
        $root = File::exists($folder) ? $folder : config('filesystems.disks.public.root');
        return new LocalFilesystemAdapter($root);
    }
}