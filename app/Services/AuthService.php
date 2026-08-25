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
    public function getUserByOAuthId(string $openid): ?User
    {
        return User::whereHas('oauth', function (Builder $builder) use ($openid) {
            $builder->where('openid', $openid);
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
     * @return bool
     */
    public function bind(string $id, string $code): bool
    {
        /** @var User $user */
        $user = Auth::user();
        $oauthUser = OAuthService::getUser($id, $code);
        return (bool)$user->oauth()->firstOrCreate([
            ...['driver_id' => $id],
            ...$this->getOAuthUserFormatData($oauthUser),
        ]);
    }

    /**
     * 解绑第三方账号
     *
     * @param string $id oauth provider id
     * @return bool
     */
    public function unbind(string $id): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return (bool)$user->oauth()->where('driver_id', $id)->delete();
    }
}
