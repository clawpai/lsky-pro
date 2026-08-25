<?php

namespace App\Http\Middleware;

use App\Models\Group;
use App\Models\User;
use App\Models\UserGroup;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * 服务初始化中间件
 * 此中间件必须放在第一，优先级最高，因为部分中间件需要用到上下文
 */
class Initialize
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取当前用户使用的组，没有登录则获取游客组
        /** @var User|null $user */
        $user = Auth::guard('sanctum')->user();

        if (! is_null($user)) {
            $group = UserGroup::valid()->where('user_id', $user->id)->first()?->group;
        } else {
            $group = Group::where('is_guest', true)->first();
        }

        Context::add('group', $group);

        return $next($request);
    }
}
