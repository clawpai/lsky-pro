<?php

declare(strict_types=1);

namespace App\Contracts;

use League\Flysystem\FilesystemAdapter;

/**
 * 储存驱动抽象类
 */
abstract class StorageAbstract
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 获取储存适配器
     *
     * @return FilesystemAdapter
     */
    abstract public function getAdapter(): FilesystemAdapter;

    /**
     * @param string|array|int|null $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig($key, $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    protected function getPrefix(): string
    {
        $prefix = $this->getConfig('root') ?: '';
        if ($prefix === '/' || $prefix === '.') {
            return '';
        }

        return $prefix;
    }
}