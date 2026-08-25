<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\StorageAbstract;
use App\Drivers\Storage\CosStorage;
use App\Drivers\Storage\FtpStorage;
use App\Drivers\Storage\LocalStorage;
use App\Drivers\Storage\OssStorage;
use App\Drivers\Storage\QiniuStorage;
use App\Drivers\Storage\S3Storage;
use App\Drivers\Storage\SftpStorage;
use App\Drivers\Storage\UpyunStorage;
use App\Drivers\Storage\WebdavStorage;
use App\StorageProvider;
use InvalidArgumentException;

/**
 * 储存工厂类
 */
class StorageDriverFactory
{
    public static function create(StorageProvider $provider, array $config): StorageAbstract
    {
        return match ($provider) {
            StorageProvider::Local => new LocalStorage($config),
            StorageProvider::S3 => new S3Storage($config),
            StorageProvider::Cos => new CosStorage($config),
            StorageProvider::Oss => new OssStorage($config),
            StorageProvider::Qiniu => new QiniuStorage($config),
            StorageProvider::Upyun => new UpyunStorage($config),
            StorageProvider::Sftp => new SftpStorage($config),
            StorageProvider::Ftp => new FtpStorage($config),
            StorageProvider::Webdav => new WebdavStorage($config),
            default => throw new InvalidArgumentException("Unsupported storage driver: {$provider->name}"),
        };
    }
}