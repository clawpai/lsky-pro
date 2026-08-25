<?php

namespace App\Http\Middleware;

use App\ApiPermission;
use App\Support\R;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取当前令牌
        $token = $request->user()->currentAccessToken();
        
        // 如果不是通过令牌认证，则跳过检查
        if (!$token instanceof PersonalAccessToken) {
            return $next($request);
        }
        
        // 获取令牌能力
        $abilities = $token->abilities ?? ['*'];
        
        // 如果有通配符，则允许访问
        if (in_array('*', $abilities)) {
            return $next($request);
        }
        
        // 确保基础权限总是被赋予
        if (!in_array(ApiPermission::BASIC->value, $abilities)) {
            $abilities[] = ApiPermission::BASIC->value;
        }
        
        // 获取当前路由需要的权限
        $permission = $this->getRequiredPermission($request);
        
        // 如果未定义权限，则默认允许访问
        if (is_null($permission)) {
            return $next($request);
        }
        
        // 检查是否有权限
        if (!in_array($permission->value, $abilities)) {
            return R::error('您的令牌没有权限访问此API')->setStatusCode(403);
        }
        
        return $next($request);
    }
    
    /**
     * 获取当前请求所需的权限
     */
    private function getRequiredPermission(Request $request): ?ApiPermission
    {
        $path = $request->path();
        $method = $request->method();
        $routeMap = ApiPermission::getRoutePermissionMap();
        
        // 直接匹配
        if (isset($routeMap[$path][$method])) {
            return $routeMap[$path][$method];
        }
        
        // 通配符匹配
        foreach ($routeMap as $route => $methodMap) {
            if (Str::endsWith($route, '*')) {
                $prefix = Str::beforeLast($route, '*');
                if (Str::startsWith($path, $prefix) && isset($methodMap[$method])) {
                    return $methodMap[$method];
                }
            }
        }
        
        return null;
    }
}
