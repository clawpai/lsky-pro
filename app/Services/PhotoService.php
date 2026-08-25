<?php

declare(strict_types=1);

namespace App\Services;

use App\DriverType;
use App\Exceptions\ServiceException;
use App\Facades\ScanService;
use App\Models\Driver;
use App\Models\Photo;
use App\Models\Storage;
use App\Models\Tag;
use App\PhotoStatus;
use App\ScanResultStatus;
use App\ViolationStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Laravel\Facades\Image;
use League\Flysystem\FilesystemException;
use League\Glide\Filesystem\FileNotFoundException;
use Throwable;

class PhotoService
{
    /**
     * 通过储存前缀发送图片响应
     *
     * @param string $prefix 储存前缀
     * @param string $path 文件路径
     * @return mixed
     * @throws ServiceException
     */
    public function sendImageResponse(string $prefix, string $path): mixed
    {
        // 通过前缀获取储存
        // TODO 优化性能
        /** @var Storage $storage */
        $storage = Storage::query()
            ->has('processDrivers')
            ->with('processDrivers')
            ->where('prefix', $prefix)
            ->first();

        // 无云处理驱动的储存（如本地/WebDAV 策略）：走智能回源，按 pathname 查照片记录，
        // 从照片所属储存读取（支持任意存储间迁移后原链接不变），并写入本地缓存供 nginx 直出
        if (is_null($storage)) {
            return $this->sendImageFromPathname($prefix, $path);
        }

        /** @var Driver $processDriver */
        $processDriver = $storage->processDrivers?->first();

        if (is_null($processDriver)) {
            return $this->sendImageFromPathname($prefix, $path);
        }

        $server = \App\Facades\StorageService::getProcessServerFactory($storage, $processDriver->options->getArrayCopy());
        $params = request()->only(array_merge(data_get($processDriver->options, 'supported_params', []), [
            'p', // 预设配置
        ]));

        // 预设配置
        $presets = collect(data_get($processDriver->options, 'presets', []));

        // 获取预设配置
        $getPresets = function($isDefault) use ($presets) {
            return $presets->where('is_default', $isDefault)
                ->mapWithKeys(fn($preset) => [$preset['name'] => $preset['params']])
                ->toArray();
        };

        $server->setDefaults(Arr::collapse($getPresets(true)));
        $server->setPresets($getPresets(false));

        try {
            return $server->getImageResponse($path, $params);
        } catch (FileNotFoundException $e) {
            abort(404, 'Image not found.');
        } catch (\League\Glide\Filesystem\FilesystemException $e) {
            abort(500, $e->getMessage());
        }
    }

    /**
     * 智能回源：按 pathname 查找照片记录，从照片所属储存读取文件流式输出，
     * 并写入本地缓存（storage/app/uploads，即 public/i 符号链接目标）供 nginx 静态直出，
     * 保证任意存储间迁移后原链接不变（本地图仍走 nginx 直出，PHP 零参与）。
     *
     * @param string $prefix 访问前缀（可能是真实前缀，也可能是 pathname 第一段）
     * @param string $path 剩余路径
     * @return mixed
     */
    protected function sendImageFromPathname(string $prefix, string $path): mixed
    {
        // 兼容两种情况：pathname 含 path（WebDAV 无前缀年份路径）/ pathname 为完整相对路径（本地 /i/ 前缀）
        $pathnames = collect([$path, "{$prefix}/{$path}"])
            ->filter(fn(string $p): bool => $p !== '' && $p !== '/')
            ->unique()
            ->values()
            ->all();

        /** @var Photo|null $photo */
        $photo = Photo::query()
            ->with('storage')
            ->whereIn('pathname', $pathnames)
            ->orderByDesc('id')
            ->first();

        if (is_null($photo) || is_null($photo->storage)) {
            abort(404, 'Image not found.');
        }

        // 本地缓存路径：storage/app/uploads/{pathname}（= public/i 符号链接目标，nginx 可直接静态直出）
        $cachePath = storage_path('app/uploads/' . $photo->pathname);

        try {
            $stream = $photo->filesystem()->readStream($photo->pathname);
        } catch (Throwable $e) {
            abort(404, 'Image not found.');
        }

        if (! is_resource($stream)) {
            abort(404, 'Image not found.');
        }

        // 写入本地缓存（失败不影响本次输出，仅跳过缓存）
        $cached = false;
        try {
            $directory = dirname($cachePath);
            if (! is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }
            $cacheStream = fopen($cachePath, 'wb');
            if ($cacheStream !== false) {
                stream_copy_to_stream($stream, $cacheStream);
                fclose($cacheStream);
                @chmod($cachePath, 0644);
                $cached = true;
            }
        } catch (Throwable $e) {
            // 缓存写失败忽略，走流式输出
        }

        $headers = [
            'Cache-Control' => 'public, max-age=2592000',
            'Content-Type' => $photo->mimetype ?: 'application/octet-stream',
        ];

        // 已写缓存：直接输出缓存文件（响应式 file，支持 Range）
        if ($cached && is_file($cachePath)) {
            return response()->file($cachePath, $headers);
        }

        // 缓存失败：流式输出
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, $headers);
    }

    /**
     * 生成图片缩略图
     *
     * @param Photo $photo
     * @param int $max 最大尺寸
     * @param int $quality 质量 0-100
     * @return void
     * @throws ServiceException
     */
    public function generateThumbnail(Photo $photo, int $max = 800, int $quality = 90): void
    {
        $stream = $pointer = false;
        try {
            $stream = $photo->filesystem()->readStream($photo->pathname);
            $manager = Image::read($stream);

            $width = $w = $manager->width();
            $height = $h = $manager->height();

            if ($w > $max && $h > $max) {
                $scale = min($max / $w, $max / $h);
                $width = (int)($w * $scale);
                $height = (int)($h * $scale);
            }

            $pointer = $manager->scale($width, $height)->encode(new AutoEncoder(quality: $quality))->toFilePointer();
            $photo->thumbnailFilesystem()->writeStream($photo->thumbnail_pathname, $pointer);
        } catch (FilesystemException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode());
        } finally {
            if (is_resource($stream)) {
                @fclose($stream);
            }
            if (is_resource($pointer)) {
                @fclose($pointer);
            }

            unset($stream, $pointer);
        }
    }

    /**
     * 扫描图片
     *
     * @param Photo $photo
     * @return void
     * @throws ServiceException
     * @throws Throwable
     */
    public function scan(Photo $photo): void
    {
        $photo->loadMissing('storage');

        /** @var Driver $scanDriver */
        $scanDriver = $photo->storage->scanDrivers()->wherePivot('type', DriverType::Scan)->first();

        /**
         * @var ScanResultStatus $status
         * @var array $reasons
         */
        [$status, $reasons] = ScanService::syncCheck($photo, $scanDriver->options);

        // 疑似违规，需要人工审核
        if (ScanResultStatus::Suspected === $status) {
            $photo->status = PhotoStatus::Pending;
        }

        // 确认违规
        if (ScanResultStatus::Violation === $status) {
            $photo->status = PhotoStatus::Violation;

            // 如果设置了违规图片转移目录，转移该图片
            $dir = Str::trim((string)data_get($scanDriver->options, 'violation_store_dir', ''), '/');
            if ($dir) {
                $filesystem = $photo->filesystem();

                $name = Str::random();
                $pathname = "{$dir}/{$name}.{$photo->extension}";
                if (! $filesystem->directoryExists($dir)) {
                    $filesystem->createDirectory($dir);
                }

                // 删除缩略图
                $photo->thumbnailFilesystem()->delete($photo->thumbnail_pathname);
                $filesystem->move($photo->pathname, $pathname);

                // 修改原数据的文件路径
                $photo->pathname = $pathname;
            }
        }

        if ($photo->isDirty('status')) {
            // 创建违规记录
            DB::transaction(function () use ($photo, $reasons) {
                $photo->save();
                $photo->violations()->create([
                    'user_id' => $photo->user_id,
                    'reason' => implode(', ', $reasons),
                    'status' => ViolationStatus::Unhandled,
                ]);
            });
        }
    }

    /**
     * 储存图片信息
     *
     * @param array $data
     * @param array $albums
     * @param array<string> $tags
     * @return Photo
     * @throws Throwable
     */
    public function store(array $data, array $albums = [], array $tags = []): Photo
    {
        return DB::transaction(function () use ($data, $albums, $tags) {
            /** @var Photo $photo */
            $photo = Photo::firstOrCreate(Arr::only($data, ['user_id', 'storage_id', 'md5', 'sha1', 'pathname']), $data);

            // 储存到相册
            if (count($albums) > 0) {
                $photo->albums()->syncWithoutDetaching($albums);
            }

            // 储存标签
            if ($tags) {
                $attach = [];
                foreach ($tags as $name) {
                    $attach[Tag::firstOrCreate(compact('name'))->id] = ['user_id' => $photo->user_id];
                }
                $photo->tags()->syncWithoutDetaching($attach);
            }

            return $photo;
        });
    }

    /**
     * 恢复违规图片
     *
     * @param Photo $photo
     * @return bool
     * @throws ServiceException
     */
    public function restoreViolationPhoto(Photo $photo): bool
    {
        if ($photo->status !== PhotoStatus::Violation) {
            throw new ServiceException('只能恢复违规状态的图片');
        }

        // 更改图片状态为正常
        $photo->status = PhotoStatus::Normal;
        $photo->save();

        // 标记所有相关的违规记录为已处理
        $photo->violations()->where('status', ViolationStatus::Unhandled)->update([
            'status' => ViolationStatus::Handled,
            'handled_at' => now(),
        ]);

        return true;
    }

    /**
     * 更新图片状态
     *
     * @param Photo $photo
     * @param PhotoStatus $status
     * @return bool
     * @throws ServiceException
     */
    public function updatePhotoStatus(Photo $photo, PhotoStatus $status): bool
    {
        if ($photo->status === $status) {
            return true;
        }

        $oldStatus = $photo->status;
        $photo->status = $status;
        $photo->save();

        // 如果从违规状态恢复为正常状态，标记违规记录为已处理
        if ($oldStatus === PhotoStatus::Violation && $status === PhotoStatus::Normal) {
            $photo->violations()->where('status', ViolationStatus::Unhandled)->update([
                'status' => ViolationStatus::Handled,
                'handled_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * 根据图片IDs删除图片
     *
     * @param array $ids 图片ID数组
     * @return int 删除成功的数量
     */
    public function destroy(array $ids): int
    {
        $photos = Photo::whereIn('id', $ids)->get();

        return DB::transaction(function () use ($photos) {
            $count = 0;

            /** @var Photo $photo */
            foreach ($photos as $photo) {
                // 删除所有关于该图片的分享
                $photo->shares()->detach();

                if ($photo->delete()) {
                    $count++;
                }
            }

            return $count;
        });
    }

    /**
     * 删除指定用户的所有图片
     *
     * @param int $userId 用户ID
     * @return int 删除成功的数量
     */
    public function destroyUserAllPhotos(int $userId): int
    {
        $photoIds = Photo::where('user_id', $userId)->pluck('id')->toArray();
        
        if (empty($photoIds)) {
            return 0;
        }

        return $this->destroy($photoIds);
    }
}
