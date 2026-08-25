<?php

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Facades\PhotoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 本地储存图片输出控制器
 */
class SendImageController extends Controller
{
    public function __invoke(string $path)
    {
        try {
            return PhotoService::sendImageResponse(Str::before($path, '/'), Str::after($path, '/'));
        } catch (ServiceException $e) {
            Log::warning('云处理输出图片失败', [
                'message' => $e->getMessage(),
            ]);
            abort(500, '不支持的文件格式');
        }
    }
}
