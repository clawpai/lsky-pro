<?php

namespace App\Http\Controllers\V2;

use App\Facades\AuthService;
use App\Facades\OAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Models\OAuth;
use App\Support\R;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OAuthController extends Controller
{
    /**
     * 获取授权地址
     */
    public function redirect(string $id, Request $request): Response
    {
        try {
            return R::success(data: [
                'redirect_url' => OAuthService::getProviderRedirectUrl(
                    $id,
                    $request->input('redirect_url'),
                    $request->input('type'),
                ),
            ]);
        } catch (Throwable $e) {
            // 聚合平台未开通某方式或临时不可用时，给前端明确提示，不泄露内部堆栈。
            return R::error($e->getMessage())->setStatusCode(422);
        }
    }

    /**
     * 绑定账号
     */
    public function login(string $id, Request $request): Response
    {
        $oauthUser = OAuthService::getUser($id, (string)$request->input('code'), (string)$request->input('type'));
        $user = AuthService::getUserByOAuthId($oauthUser->getId(), $id);

        if (!is_null($user)) {
            AuthService::login($user, true);

            return R::success(data: AuthService::getLoginResult());
        }

        // 返回 202 表示需要绑定或注册账号
        return R::error('No existing user found. Please proceed with login or registration using the provided token.', [
            // 返回验证token
            'token' => AuthService::getOAuthLoginVerifyToken($oauthUser, [
                'driver_id' => $id,
                'type' => $request->input('type'),
            ]),
        ])->setStatusCode(202);
    }

    /**
     * 绑定列表
     */
    public function binds(QueryRequest $request): Response
    {
        $binds = AuthService::binds($request->validated());

        $binds->getCollection()->transform(function (OAuth $oauth) {
            // driver.type 是 DriverType enum 字段；展示的登录方式来自 oauth 绑定记录本身。
            $driver = $oauth->driver;
            $driver->provider = $driver->options['provider'];
            $driver->mergeCasts(['type' => 'string']);
            $driver->type = $oauth->type;
            $driver->setVisible(['id', 'name', 'intro', 'provider', 'type']);

            return $oauth->setVisible(['id', 'avatar', 'email', 'name', 'nickname', 'driver', 'created_at']);
        });

        return R::success(data: $binds);
    }

    /**
     * 绑定第三方账号
     */
    public function bind(string $id, Request $request): Response
    {
        AuthService::bind($id, (string)$request->input('code'), (string)$request->input('type'));
        return R::success();
    }

    /**
     * 解绑第三方账号
     */
    public function unbind(string $id, Request $request): Response
    {
        AuthService::unbind($id, (string)$request->input('type'));
        return R::success();
    }
}
