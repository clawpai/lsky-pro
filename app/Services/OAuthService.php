<?php

declare(strict_types=1);

namespace App\Services;

use App\DriverType;
use App\Models\Driver;
use App\Socialite\CCLoginProvider;
use App\SocialiteProvider;
use Overtrue\Socialite\Contracts\ProviderInterface;
use Overtrue\Socialite\Contracts\UserInterface;
use Overtrue\Socialite\SocialiteManager;
use InvalidArgumentException;

class OAuthService
{
    /**
     * 判断 provider 是否存在
     *
     * @param string $id
     * @return bool
     */
    public function hasProvider(string $id): bool
    {
        return array_key_exists($id, $this->getProviders());
    }

    /**
     * 获取当前可用的驱动
     *
     * @return array
     */
    public function getProviders(): array
    {
        $providers = [];

        $drivers = Driver::where('type', DriverType::Socialite)->get();

        /** @var Driver $driver */
        foreach ($drivers as $driver) {
            $providers[(string)$driver->id] = $driver->options->getArrayCopy();
        }

        return $providers;
    }

    /**
     * 获取授权链接
     *
     * @param string $id 驱动ID
     * @param string|null $redirectUrl 回调地址
     * @param string|null $type 聚合登录方式（qq/wx/baidu 等）
     * @return string
     */
    public function getProviderRedirectUrl(string $id, ?string $redirectUrl = null, ?string $type = null): string
    {
        $this->validateLoginType($id, $type);

        return $this->getProviderManager($id, $type)->redirect($redirectUrl);
    }

    /**
     * 获取授权管理器
     *
     * @param string $id 驱动ID
     * @return ProviderInterface
     */
    public function getProviderManager(string $id, ?string $type = null): ProviderInterface
    {
        $manager = new SocialiteManager($this->getProviders());

        $provider = $this->getProviderName($id);

        // 注册聚合 CC 自定义 Provider（兼容标准聚合登录网关）
        if ($provider === SocialiteProvider::CCLogin->value) {
            $manager->extend(SocialiteProvider::CCLogin->value, fn($config) => new CCLoginProvider($config, $type));
        }

        return $manager->create($id);
    }

    /**
     * 获取驱动对应 provider 标识
     *
     * @param string $id 驱动ID
     * @return string|null
     */
    protected function getProviderName(string $id): ?string
    {
        $driver = Driver::query()->find($id);

        if (is_null($driver)) {
            return null;
        }

        return $driver->options['provider'] ?? null;
    }

    /**
     * 获取授权用户信息
     *
     * @param string $id 驱动ID
     * @param string $code 授权 code
     * @param string|null $type 登录方式（聚合驱动为 qq/wx/baidu 等，用于区分多方式绑定）
     * @return UserInterface
     */
    public function getUser(string $id, string $code, ?string $type = null): UserInterface
    {
        $this->validateLoginType($id, $type);

        return $this->getProviderManager($id, $type)->userFromCode($code);
    }

    /**
     * 校验聚合登录方式必须是后台该驱动已经启用的方式，防止篡改 type 调用未启用平台。
     */
    protected function validateLoginType(string $id, ?string $type): void
    {
        $driver = Driver::query()->find($id);
        if (is_null($driver) || ($driver->options['provider'] ?? null) !== SocialiteProvider::CCLogin->value) {
            return;
        }

        $enabledTypes = preg_split('/[,，\/\s]+/', (string)($driver->options['type'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($type) || ! in_array($type, $enabledTypes, true)) {
            throw new InvalidArgumentException('该登录方式未在后台启用');
        }
    }
}
