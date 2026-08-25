<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;

/**
 * Sftp
 */
class SftpStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        return new SftpAdapter(
            connectionProvider: new SftpConnectionProvider(
                host: (string)$this->getConfig('host'),
                username: (string)$this->getConfig('username'),
                password: $this->getConfig('password'),
                privateKey: $this->getConfig('private_key'),
                passphrase: $this->getConfig('passphrase'),
                port: (int)$this->getConfig('port', 22),
                useAgent: (bool)$this->getConfig('use_agent', false),
                timeout: (int)$this->getConfig('timeout', 10),
                maxTries: (int)$this->getConfig('max_tries', 4),
                hostFingerprint: $this->getConfig('host_fingerprint'),
            ),
            root: $this->getPrefix(),
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