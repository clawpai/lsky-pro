<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use App\Contracts\StorageAbstract;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

/**
 * Webdav
 */
class WebdavStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        return new WebDAVAdapter(
            client: new Client([
                'baseUri' => $this->getConfig('base_uri'),
                'userName' => $this->getConfig('username'),
                'password' => $this->getConfig('password'),
                'authType' => ($this->getConfig('auth_type')) ?: null,
            ]),
            prefix: $this->getPrefix(),
        );
    }
}