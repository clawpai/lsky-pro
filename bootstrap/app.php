<?php

use App\Exceptions\ApplicationNotInstalledException;
use App\Exceptions\ServiceException;
use App\Support\R;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
//        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
        ]);
        $middleware->statefulApi();
        // 屏蔽 csrf 验证
        $middleware->validateCsrfTokens([
            'api/*',
            'api/*/notify/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 捕获接口异常
        // 程序未安装
        $exceptions->render(function (ApplicationNotInstalledException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error($e->getMessage());
            }
            return false;
        });

        // 服务(业务)异常
        $exceptions->render(function (ServiceException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error($e->getMessage());
            }
            return false;
        });

        // 认证异常
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error($e->getMessage())->setStatusCode(401);
            }
            return false;
        });

        // 验证器鉴权异常
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error($e->getMessage())->setStatusCode(403);
            }
            return false;
        });

        // oauth 认证失败
        $exceptions->render(function (AuthorizeFailedException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error($e->getMessage())->setStatusCode(500);
            }
            return false;
        });

        // 验证错误
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error($e->getMessage(), ['errors' => $e->errors()])->setStatusCode(422);
            }
            return false;
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error($e->getMessage())->setStatusCode(429);
            }
            return false;
        });

        // 请求 content-length 超出 post_max_size 配置异常
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson() && $request->is('api/*')) {
                return R::error('上传文件大小超出 post_max_size 限制值，请检查 PHP 配置。')->setStatusCode(413);
            }
            return false;
        });
    })->create();
