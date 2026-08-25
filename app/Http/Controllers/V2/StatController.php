<?php

declare(strict_types=1);

namespace App\Http\Controllers\V2;

use App\Exceptions\ServiceException;
use App\Http\Controllers\Controller;
use App\Services\StatService;
use App\Settings\AppSettings;
use App\Support\R;
use Symfony\Component\HttpFoundation\Response;

class StatController extends Controller
{
    public function __construct(
        private readonly StatService $statService,
        private readonly AppSettings $appSettings
    ) {}

    /**
     * 获取系统统计信息
     * @param string|null $key
     * @return Response
     * @throws ServiceException
     */
    public function index(?string $key = null): Response
    {
        // 检查是否启用统计API
        if (!$this->appSettings->enable_stat_api) {
            abort(404, '系统未启用统计接口');
        }

        // 检查访问密钥
        $apiKey = $this->appSettings->enable_stat_api_key;
        if ($apiKey && $key !== $apiKey) {
            abort(404, '密钥不正确');
        }

        try {
            $stats = $this->statService->getFullStats();
            return R::success(data: $stats);
        } catch (\Exception $e) {
            throw new ServiceException('获取统计信息失败: ' . $e->getMessage());
        }
    }
} 