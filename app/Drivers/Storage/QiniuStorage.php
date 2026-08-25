<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use League\Flysystem\FilesystemAdapter;
use Overtrue\Flysystem\Qiniu\QiniuAdapter;

/**
 * 七牛云 kodo
 */
class QiniuStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        return new QiniuAdapter(
            accessKey: (string)$this->getConfig('access_key', ''),
            secretKey: (string)$this->getConfig('secret_key', ''),
            bucket: (string)$this->getConfig('bucket', ''),
            domain: (string)$this->getConfig('domain', ''),
        );
    }
}