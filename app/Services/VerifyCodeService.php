<?php

declare(strict_types=1);

namespace App\Services;

use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

class VerifyCodeService
{
    /**
     * 生成验证码
     *
     * @param string $key
     * @param DateTimeInterface|DateInterval|int|null $ttl
     * @return string
     */
    public function generateCode(string $key, $ttl = 900): string
    {
        $code = (string)mt_rand(100000, 999999);
        Cache::put($key, $code, $ttl);

        return $code;
    }

    /**
     * 验证验证码
     *
     * @param string $key
     * @param string|null $code
     * @return bool
     */
    public function verifyCode(string $key, ?string $code): bool
    {
        return Cache::get($key) == $code;
    }
}