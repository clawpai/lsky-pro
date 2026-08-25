<?php

declare(strict_types=1);

namespace App\Services;

use App\UpgradeStatus;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Throwable;

class UpgradeService
{
    public const STATUS_CACHE_KEY = 'upgrade-status';
    public const MESSAGE_CACHE_KEY = 'upgrade-message';

    /**
     * 设置更新状态
     * @param UpgradeStatus $status
     * @param DateTimeInterface|DateInterval|int|null $ttl
     * @return bool
     */
    public function setStatus(UpgradeStatus $status, $ttl = 3600): bool
    {
        return Cache::put(self::STATUS_CACHE_KEY, $status, $ttl);
    }

    /**
     * 设置更新进度
     * @param string $message
     * @param DateTimeInterface|DateInterval|int|null $ttl
     * @return bool
     */
    public function setMessage(string $message, $ttl = 3600): bool
    {
        return Cache::put(self::MESSAGE_CACHE_KEY, $message, $ttl);
    }

    /**
     * 补充更新进度
     * @param string $message
     * @param DateTimeInterface|DateInterval|int|null $ttl
     * @return bool
     */
    public function putMessage(string $message, $ttl = 3600): bool
    {
        $msg = $this->getMessage();
        if ($msg) {
            $message = $msg . "\n" . $message;
        }

        return $this->setMessage($message, $ttl);
    }

    /**
     * 获取更新进度字符串
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return Cache::get(self::MESSAGE_CACHE_KEY);
    }

    /**
     * 获取更新进度
     * @return UpgradeStatus|null
     */
    public function getStatus(): ?UpgradeStatus
    {
        return Cache::get(self::STATUS_CACHE_KEY);
    }

    /**
     * 获取当前版本 hashes.json 的 hash 值
     * @return string
     */
    public function getHash(): string
    {
        return File::hash(base_path('hashes.json'));
    }

    /**
     * 清理
     * @return void
     */
    public function clear(): void
    {
        try {
            Cache::delete(self::STATUS_CACHE_KEY);
            Cache::delete(self::MESSAGE_CACHE_KEY);
        } catch (Throwable $e) {

        }
    }
}