<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use Illuminate\Support\Arr;
use League\Flysystem\FilesystemAdapter;
use Overtrue\Flysystem\Cos\CosAdapter;

/**
 * 腾讯云 COS
 */
class CosStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        $options = Arr::only($this->config, [
            'app_id', 'secret_id', 'secret_key', 'region', 'bucket'
        ]);

        return new CosAdapter(array_filter(array_merge($options, [
            'prefix' => $this->getPrefix(),
        ])));
    }
}