<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Album;
use App\Models\Order;
use App\Models\Photo;
use App\Models\Share;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Support\Carbon;

class StatService
{
    /**
     * 图片数量
     * @return int
     */
    public function getPhotoCount(): int
    {
        return Photo::count();
    }

    /**
     * 相册数量
     * @return int
     */
    public function getAlbumCount(): int
    {
        return Album::count();
    }

    /**
     * 用户数量
     * @return int
     */
    public function getUserCount(): int
    {
        return User::count();
    }

    /**
     * 分享数量
     * @return int
     */
    public function getShareCount(): int
    {
        return Share::count();
    }

    /**
     * 今日图片上传数量
     * @return int
     */
    public function getTodayPhotoCount(): int
    {
        return Photo::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * 今日相册创建数量
     * @return int
     */
    public function getTodayAlbumCount(): int
    {
        return Album::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * 今日用户注册数量
     * @return int
     */
    public function getTodayUserCount(): int
    {
        return User::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * 今日分享数量
     * @return int
     */
    public function getTodayShareCount(): int
    {
        return Share::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * 总订单金额
     * @return float
     */
    public function getTotalOrderAmount(): float
    {
        $amount = Order::where('status', OrderStatus::Paid)->sum('amount');
        return $amount ? $amount / 100 : 0;
    }

    /**
     * 今日订单额
     * @return float
     */
    public function getTodayOrderAmount(): float
    {
        $amount = Order::where('status', OrderStatus::Paid)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        return $amount ? $amount / 100 : 0;
    }

    /**
     * 昨日订单额
     * @return float
     */
    public function getYesterdayOrderAmount(): float
    {
        $amount = Order::where('status', OrderStatus::Paid)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('amount');

        return $amount ? $amount / 100 : 0;
    }

    /**
     * 本周订单额
     * @return float
     */
    public function getWeekOrderAmount(): float
    {
        $amount = Order::where('status', OrderStatus::Paid)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('amount');

        return $amount ? $amount / 100 : 0;
    }

    /**
     * 本月订单额
     * @return float
     */
    public function getMonthOrderAmount(): float
    {
        $amount = Order::where('status', OrderStatus::Paid)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('amount');

        return $amount ? $amount / 100 : 0;
    }

    /**
     * 获取系统信息
     * @return array
     */
    public function getSystemInfo(): array
    {
        return [
            'os' => PHP_OS_FAMILY,
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_upload_filesize' => ini_get('upload_max_filesize'),
            'max_post_size' => ini_get('post_max_size'),
            'software_version' => config('app.version', '1.0.0'),
        ];
    }

    /**
     * 获取完整统计数据
     * @return array
     */
    public function getFullStats(): array
    {
        return [
            'basic' => [
                'photo_count' => $this->getPhotoCount(),
                'album_count' => $this->getAlbumCount(),
                'user_count' => $this->getUserCount(),
                'share_count' => $this->getShareCount(),
                'today_photo_count' => $this->getTodayPhotoCount(),
                'today_album_count' => $this->getTodayAlbumCount(),
                'today_user_count' => $this->getTodayUserCount(),
                'today_share_count' => $this->getTodayShareCount(),
            ],
            'orders' => [
                'total_amount' => $this->getTotalOrderAmount(),
                'today_amount' => $this->getTodayOrderAmount(),
                'yesterday_amount' => $this->getYesterdayOrderAmount(),
                'week_amount' => $this->getWeekOrderAmount(),
                'month_amount' => $this->getMonthOrderAmount(),
            ],
            'system' => $this->getSystemInfo(),
        ];
    }
}