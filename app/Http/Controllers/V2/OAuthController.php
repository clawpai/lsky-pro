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
                    $this->normalizeLoginType($request->input('type')),
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
        $type = $this->normalizeLoginType($request->input('type'));
        $oauthUser = OAuthService::getUser($id, (string)$request->input('code'), $type);
        $user = AuthService::getUserByOAuthId($oauthUser->getId(), $id, $type);

        if (!is_null($user)) {
            AuthService::login($user, true);

            return R::success(data: AuthService::getLoginResult());
        }

        // 返回 202 表示需要绑定或注册账号
        return R::error('No existing user found. Please proceed with login or registration using the provided token.', [
            // 返回验证token
            'token' => AuthService::getOAuthLoginVerifyToken($oauthUser, [
                'driver_id' => $id,
                'type' => $type,
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
            // 每一条绑定都构造独立展示数据，不能修改共享的 Eloquent driver 关系对象。
            $driver = [
                'id' => $oauth->driver->id,
                'name' => $oauth->driver->name,
                'intro' => $oauth->driver->intro,
                'provider' => $oauth->driver->options['provider'],
                'type' => $oauth->type,
            ];

            return [
                'id' => $oauth->id,
                'avatar' => $oauth->avatar,
                'email' => $oauth->email,
                'name' => $oauth->name,
                'nickname' => $oauth->nickname,
                'driver' => $driver,
                'created_at' => $oauth->created_at,
            ];
        });

        return R::success(data: $binds);
    }

    /**
     * 绑定第三方账号
     */
    public function bind(string $id, Request $request): Response
    {
        AuthService::bind($id, (string)$request->input('code'), $this->normalizeLoginType($request->input('type')));
        return R::success();
    }

    /**
     * 解绑第三方账号
     */
    public function unbind(string $id, Request $request): Response
    {
        AuthService::unbind(
            $id,
            $this->normalizeLoginType($request->input('type')),
            $request->integer('oauth_id') ?: null,
        );
        return R::success();
    }

    /**
     * 聚合网关会把回调 URL 自身的 type 再附加一次，形成 wx,wx。
     * 只折叠完全相同的重复值；混合类型一律拒绝，避免绕过启用方式校验。
     */
    private function normalizeLoginType(mixed $value): ?string
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        $types = array_values(array_filter(array_map('trim', explode(',', (string) $value))));
        if (empty($types)) {
            return null;
        }
        if (count(array_unique($types)) !== 1) {
            throw new \InvalidArgumentException('登录方式参数无效');
        }

        return $types[0];
    }
}
