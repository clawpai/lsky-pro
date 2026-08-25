<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;

/**
 * Ftp
 */
class FtpStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        return new FtpAdapter(
            connectionOptions: new FtpConnectionOptions(
                host: (string)$this->getConfig('host'),
                root: $this->getPrefix(),
                username: (string)$this->getConfig('username'),
                password: (string)$this->getConfig('password'),
                port: (int)$this->getConfig('port', 21),
                ssl: (bool)$this->getConfig('ssl', false),
                timeout: (int)$this->getConfig('timeout', 90),
                passive: (bool)$this->getConfig('passive', false),
                transferMode: (int)$this->getConfig('transfer_mode', FTP_BINARY),
                ignorePassiveAddress: $this->getConfig('ignore_passive_address'),
                enableTimestampsOnUnixListings: (bool)$this->getConfig('enable_timestamps_on_unix_listings', false),
                recurseManually: (bool)$this->getConfig('recurse_manually', false),
            ),
            visibilityConverter: PortableVisibilityConverter::fromArray([
                'file' => [
                    'public' => 0755,
                    'private' => 0644,
                ],
                'dir' => [
                    'public' => 0755,
                    'private' => 0644,
                ],
            ], Visibility::PUBLIC),
        );
    }
}