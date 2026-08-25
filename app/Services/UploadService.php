<?php

declare(strict_types=1);

namespace App\Services;

use App\DriverType;
use App\Events\UploadFinished;
use App\Exceptions\ServiceException;
use App\Facades\AppService;
use App\Facades\PhotoHandleService;
use App\Facades\PhotoService;
use App\Facades\ScanService;
use App\Facades\StorageService;
use App\Models\Driver;
use App\Models\Group;
use App\Models\Photo;
use App\Models\Storage;
use App\Models\User;
use App\PhotoStatus;
use App\ScanResultStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\FileExtension;
use League\Flysystem\Filesystem;
use Throwable;

class UploadService
{
    /**
     * 上传图片
     *
     * @param UploadedFile $file
     * @param User|null $user
     * @param array $merge
     * @param array $tags
     * @return Photo
     * @throws ServiceException
     */
    public function uploadImage(UploadedFile $file, User $user = null, array $merge = [], array $tags = []): Photo
    {
        if (! $file->isValid()) {
            throw new ServiceException('文件资源无效');
        }

        /** @var Group $group */
        $group = Context::get('group');

        /** @var Storage $storage */
        $storage = $group->storages()->find(data_get($merge, 'storage_id'));

        if (is_null($storage)) {
            throw new ServiceException('不存在的储存驱动');
        }

        // 获取驱动适配器
        $filesystem = new Filesystem(StorageService::getAdapter($storage->provider, $storage->options));

        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // 处理图片没有正常的拓展名的情况
        if (is_null(FileExtension::tryFrom(Str::lower($extension)))) {
            $extension = $file->extension() ?: 'png';
        }

        $pathname = $this->getFilePathname($file, $storage->options['naming_rule']) . '.' . $extension;
        $stream = fopen($file->getRealPath(), 'r+');
        try {
            // 文件已存在储存中则跳过物理文件上传
            if (!$filesystem->fileExists($pathname)) {
                $filesystem->writeStream($pathname, $stream);
            }

            [$width, $height] = @getimagesize($file->getRealPath()) ?: [0, 0];

            $expiredAt = data_get($merge, 'expired_at');
            // 组如果设置了过期时间则覆盖前端设置的过期时间
            if ($group->options['file_expire_seconds']) {
                $expiredAt = now()->addSeconds((int)$group->options['file_expire_seconds']);
            }

            // 如果是登录用户，补充信息
            $appends = ['is_public' => false];
            $albums = [];
            if (!is_null($user)) {
                $appends = [
                    'user_id' => $user->id,
                    'is_public' => boolval(data_get($merge, 'is_public', false)),
                ];

                $albums = array_filter([data_get($merge, 'album_id')]);
            }

            // 合并数据
            $data = array_filter(array_merge([
                'group_id' => $group->id,
                'storage_id' => $storage->id,
                'mimetype' => $file->getMimeType(),
                'extension' => $extension,
                'name' => str_replace('.' . $extension, '', $filename),
                'md5' => md5_file($file->getRealPath()),
                'sha1' => sha1_file($file->getRealPath()),
                'filename' => $filename,
                'pathname' => $pathname,
                'size' => $file->getSize() / 1024,
                'status' => PhotoStatus::Normal,
                'ip_address' => AppService::getRequestIp(request()),
                'expired_at' => $expiredAt,
                'width' => $width,
                'height' => $height,
            ], $appends));

            $photo = PhotoService::store($data, $albums, $tags);

            // 查找审核、图片处理驱动配置，判断是否需要同步处理
            $types = [DriverType::Scan->value => 0, DriverType::Handle->value => 1];
            $drivers = $photo->storage
                ->drivers()
                ->wherePivotIn('type', array_keys($types))
                ->get()
                ->where('options.is_sync', true)
                // 自定义排序，将审核驱动排在第一位
                ->sortBy(fn (Driver $driver) => $types[$driver->type->value]);

            // 同步处理过的驱动类型
            $processedDrivers = [];

            /** @var Driver $driver */
            foreach ($drivers as $driver) {
                switch ($driver->type) {
                    // 图片审核
                    case DriverType::Scan:
                        /**
                         * @var ScanResultStatus $status
                         * @var array $reasons
                         */
                        [$status, $reasons] = ScanService::syncCheck($photo, $driver->options);
                        if ($status === ScanResultStatus::Violation) {
                            throw new ServiceException("违规内容：".implode(',', $reasons));
                        }
                        break;

                    // 图片处理
                    case DriverType::Handle:
                        PhotoHandleService::format($photo, $driver->options);
                        break;
                    default:
                        throw new ServiceException('系统错误，请稍后再试');
                }

                $processedDrivers[] = $driver->type;
            }

            event(new UploadFinished($photo, $processedDrivers));
        } catch (Throwable $e) {
            // 出错直接删除文件
            if (isset($photo)) {
                // 不能使用 forceDelete，因为删除物理文件的方式是 Photo 监听的 deleted 事件，调用 forceDelete 不会走 deleted 事件
                $photo->delete();
            }

            $message = $e->getPrevious() ? $e->getPrevious()?->getMessage() : $e->getMessage();

            Log::error('上传文件时发生异常', [
                'request' => request()->all(),
                'message' => $message,
                'trace' => $e->getTraceAsString(),
                'previous' => $e->getPrevious() ? $e->getPrevious()->getTraceAsString() : null,
            ]);

            throw new ServiceException($message);
        } finally {
            if (is_resource($stream)) {
                @fclose($stream);
            }
        }

        return $photo;
    }

    /**
     * 获取文件路径命名
     *
     * @param UploadedFile $file
     * @param string $rule
     * @return string
     */
    public function getFilePathname(UploadedFile $file, string $rule): string
    {
        return str_replace([
            '{Y}', '{y}', '{m}', '{d}', '{Ymd}', '{filename}', '{ext}', '{time}', '{uniqid}', '{md5}', '{sha1}', '{uuid}', '{uid}'
        ], [
            date('Y'),
            date('y'),
            date('m'),
            date('d'),
            date(('Ymd')),
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            $file->getClientOriginalExtension(),
            time(),
            uniqid(),
            md5_file($file->getRealPath()),
            sha1_file($file->getRealPath()),
            Str::uuid()->toString(),
            Auth::guard('sanctum')->id() ?: 0,
        ], Str::trim($rule, '/'));
    }
}
