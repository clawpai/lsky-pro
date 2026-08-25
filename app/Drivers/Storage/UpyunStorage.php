<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use League\Flysystem\FilesystemAdapter;
use WispX\Flysystem\Upyun\UpyunAdapter;

/**
 * 又拍云 USS
 */
class UpyunStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        return new UpyunAdapter(
            service: (string)$this->getConfig('service', ''),
            operator: (string)$this->getConfig('operator', ''),
            password: (string)$this->getConfig('password', ''),
            domain: (string)$this->getConfig('domain', ''),
        );
    }
}