<?php

namespace App\Http\Middleware;

use App\Facades\AppService;
use App\Models\Group;
use App\Models\Photo;
use App\Models\User;
use App\Support\R;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * 上传频率限制中间件
 */
class UploadFrequencyLimit
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Group $group */
        $group = Context::get('group');

        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        $ipAddress = AppService::getRequestIp($request);

        // 根据组限制用户上传频率，未登录则使用ip，否则根据用户查询
        $scopes = ['minute' => '分钟', 'hour' => '小时', 'day' => '天', 'week' => '周', 'month' => '个月'];
        foreach ($scopes as $scope => $name) {
            $limit = (int)$group->options['limit_per_' . $scope];

            // 为 0 则不限制
            if (0 === $limit) continue;

            $count = Photo::$scope()->when(!is_null($user), function (Builder $builder) use ($user) {
                $builder->where('user_id', $user->id);
            }, function (Builder $builder) use ($ipAddress) {
                $builder->where('ip_address', $ipAddress);
            })->count();

            if ($count >= $limit) {
                return R::error("一{$name}内你只能上传 {$limit} 张图片")->setStatusCode(429);
            }
        }

        return $next($request);
    }
}
