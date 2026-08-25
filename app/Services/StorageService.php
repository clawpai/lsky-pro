<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Factories\StorageDriverFactory;
use App\Models\Storage;
use App\Settings\AppSettings;
use App\StorageProvider;
use ArrayObject;
use Exception;
use Illuminate\Support\Facades\File;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Glide\Server;
use League\Glide\ServerFactory;
use Throwable;

class StorageService
{
    /**
     * 通过驱动枚举获取适配器
     * @param StorageProvider $provider
     * @param ArrayObject $options
     * @return FilesystemAdapter
     */
    public function getAdapter(StorageProvider $provider, ArrayObject $options): FilesystemAdapter
    {
        return StorageDriverFactory::create($provider, $options->getArrayCopy())->getAdapter();
    }

    /**
     * 获取指定储存的云处理 factory
     * @param Storage $storage 储存
     * @param ArrayObject|array $options 云处理配置
     * @return Server
     */
    public function getProcessServerFactory(Storage $storage, ArrayObject|array $options): Server
    {
        $adapter = $this->getAdapter($storage->provider, $storage->options);

        return ServerFactory::create([
            'driver' => app(AppSettings::class)->image_driver,
            'source' => new Filesystem($adapter),
            'cache' => data_get($options, 'cache'),
            'response' => new (data_get($options, 'response')),
            'max_image_size' => 20000 * 20000,
        ]);
    }

    /**
     * 创建储存的文件夹
     * @param array $options
     * @return void
     */
    public function makeStorageDirectory(array $options): void
    {
        $root = data_get($options, 'root');
        $glideEnable = data_get($options, 'glide.enable', false);
        $glideCacheRoot = data_get($options, 'glide.cache');

        // 储存目录不存在则创建
        if (! File::isDirectory($root)) {
            File::makeDirectory(
                path: $root,
                mode: 0755,
                recursive: true,
                force: true,
            );
        }

        // 云处理缓存目录不存在则创建
        if ($glideEnable && ! File::isDirectory($glideCacheRoot)) {
            File::makeDirectory(
                path: $glideCacheRoot,
                mode: 0755,
                recursive: true,
                force: true,
            );
        }
    }
    
    /**
     * 测试存储连接
     * @param StorageProvider $provider
     * @param array $options
     * @return bool
     * @throws Exception
     */
    public function testConnection(StorageProvider $provider, array $options): bool
    {
        $temp = tmpfile();

        try {
            // 获取适配器
            $adapter = StorageDriverFactory::create($provider, $options)->getAdapter();
            $filesystem = new Filesystem($adapter);

            $testContent = "Testing, testing.";
            fwrite($temp, $testContent);
            rewind($temp);

            $testFileName = 'lsky_connection_test_' . uniqid() . '.txt';

            // 尝试写入测试文件
            $filesystem->writeStream($testFileName, $temp);

            // 七牛云使用私有链接的方式读取文件，避免未配置域名导致测试失败，跳过读取
            if ($provider !== StorageProvider::Qiniu) {
                // 尝试读取文件
                $readContent = $filesystem->read($testFileName);

                // 删除测试文件
                $filesystem->delete($testFileName);

                // 验证内容是否一致
                return ($readContent === $testContent);
            } else {
                // 删除测试文件
                $filesystem->delete($testFileName);
                return true;
            }

        } catch (Throwable $e) {
            throw new ServiceException(__('admin/storage.actions.test_connection.error_message', ['message' => $e->getMessage()]));
        } finally {
            @fclose($temp);
        }
    }
}
