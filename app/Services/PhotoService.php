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
            ->firstOrFail();

        /** @var Driver $processDriver */
        $processDriver = $storage->processDrivers?->first();

        if (is_null($processDriver)) {
            throw new ServiceException('No process drivers available.');
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
