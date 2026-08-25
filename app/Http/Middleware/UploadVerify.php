<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Settings\AppSettings;
use App\Support\R;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 上传验证中间件
 */
class UploadVerify
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        $appSettings = app(AppSettings::class);

        if (!is_null($user)) {
            if ($appSettings->user_email_verify && !$user->email_verified_at) {
                return R::error('请先验证邮箱');
            }

            if ($appSettings->user_phone_verify && !$user->phone_verified_at) {
                return R::error('请先绑定手机号');
            }
        } else {
            // 检测是否支持游客上传
            if (!$appSettings->guest_upload) {
                return R::error('系统暂不支持游客上传');
            }
        }

        return $next($request);
    }
}
