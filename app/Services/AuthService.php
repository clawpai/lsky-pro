<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\OAuthService;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Overtrue\Socialite\Contracts\UserInterface;
use Psr\SimpleCache\InvalidArgumentException;

class AuthService
{
    /**
     * 直接登录用户
     *
     * @param User $user
     * @param bool $remember
     * @return void
     */
    public function login(User $user, bool $remember = false): void
    {
        Auth::login($user, $remember);
    }

    /**
     * 通过第三方授权ID获取用户
     *
     * @param string $openid 第三方授权ID
     * @return null|User
     */
    public function getUserByOAuthId(string $openid, ?string $driverId = null): ?User
    {
        return User::whereHas('oauth', function (Builder $builder) use ($openid, $driverId) {
            $builder->where('openid', $openid);

            if (! is_null($driverId)) {
                $builder->where('driver_id', $driverId);
            }
        })->first();
    }

    /**
     * 获取格式化后的第三方用户信息
     * @param UserInterface $user
     * @return array
     */
    public function getOAuthUserFormatData(UserInterface $user): array
    {
        return array_filter([
            'openid' => $user->getId(),
            'avatar' => $user->getAvatar(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'nickname' => $user->getNickname(),
            'raw' => $user->getRaw() ?: [],
        ]);
    }

    /**
     * 登录 result
     *
     * @return array
     */
    public function getLoginResult(): array
    {
        /** @var User $user */
        $user = auth()->user();

        return [
            'name' => $user->name,
            'token' => $user->createToken($user->email ?: $user->phone)->plainTextToken,
        ];
    }

    /**
     * 获取 oauth 授权登录 token
     *
     * @param UserInterface $user
     * @param array $appends
     * @return string
     * @throws InvalidArgumentException
     */
    public function getOAuthLoginVerifyToken(UserInterface $user, array $appends = []): string
    {
        $token = md5($user->getId() . time());
        Cache::set($token, array_filter([
            ...$this->getOAuthUserFormatData($user),
            ...$appends,
        ]), now()->addDays());

        return $token;
    }

    /**
     * 获取 oauth 授权登录 token 内容
     *
     * @param string $token
     * @return null|array
     */
    public function getOAuthLoginVerifyTokenContent(string $token): ?array
    {
        $data = Cache::get($token);

        if ($data) {
            return Cache::pull($token);
        }

        return null;
    }

    /**
     * 当前用户绑定的第三方列表
     */
    public function binds(array $queries = []): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->oauth()->with('driver')->has('driver')->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 绑定第三方账号
     *
     * @param string $id oauth provider id
     * @param string $code code
     * @param string|null $type 登录方式（聚合驱动为 qq/wx/baidu 等，可空兼容旧逻辑）
     * @return bool
     */
    public function bind(string $id, string $code, ?string $type = null): bool
    {
        /** @var User $user */
        $user = Auth::user();
        $oauthUser = OAuthService::getUser($id, $code, $type);

        // 同一驱动 + 登录方式 + 第三方唯一 ID 才视为同一绑定；昵称、头像等变化只更新资料，不能产生重复绑定。
        $attributes = [
            'driver_id' => $id,
            'openid' => $oauthUser->getId(),
        ];
        if (! is_null($type) && $type !== '') {
            $attributes['type'] = $type;
        }

        $oauth = $user->oauth()->firstOrNew($attributes);
        $oauth->fill($this->getOAuthUserFormatData($oauthUser));

        return $oauth->save();
    }

    /**
     * 解绑第三方账号
     *
     * @param string $id oauth provider id
     * @param string|null $type 登录方式（聚合驱动多方式绑定需指定，防止误删其他方式）
     * @return bool
     */
    public function unbind(string $id, ?string $type = null): bool
    {
        /** @var User $user */
        $user = Auth::user();
        $query = $user->oauth()->where('driver_id', $id);

        // 聚合驱动多方式：按 type 精确解绑；其余按驱动解绑
        if (! is_null($type) && $type !== '') {
            $query->where('type', $type);
        }

        return (bool)$query->delete();
    }
}
