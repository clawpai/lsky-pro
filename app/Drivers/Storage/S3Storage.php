<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Illuminate\Support\Arr;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Visibility;

/**
 * AWS S3
 */
class S3Storage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        return new AwsS3V3Adapter(
            client: new S3Client([
                'credentials' => new Credentials($this->getConfig('access_key_id'), $this->getConfig('secret_access_key')),
                'endpoint' => $this->getConfig('endpoint'),
                'region' => $this->getConfig('region'),
                'version' => 'latest',
                'use_path_style_endpoint' => (bool)$this->getConfig('use_path_style_endpoint'),
            ]),
            bucket: (string)$this->getConfig('bucket'),
            prefix: $this->getPrefix(),
            visibility: new PortableVisibilityConverter(Visibility::PUBLIC),
            options: Arr::undot($this->getConfig('options', [])),
        );
    }
}