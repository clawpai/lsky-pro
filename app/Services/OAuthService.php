<?php

declare(strict_types=1);

namespace App\Services;

use App\DriverType;
use App\Models\Driver;
use Overtrue\Socialite\Contracts\ProviderInterface;
use Overtrue\Socialite\Contracts\UserInterface;
use Overtrue\Socialite\SocialiteManager;

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
     * @return string
     */
    public function getProviderRedirectUrl(string $id, ?string $redirectUrl = null): string
    {
        return $this->getProviderManager($id)->redirect($redirectUrl);
    }

    /**
     * 获取授权管理器
     *
     * @param string $id 驱动ID
     * @return ProviderInterface
     */
    public function getProviderManager(string $id): ProviderInterface
    {
        $manager = new SocialiteManager($this->getProviders());
        return $manager->create($id);
    }

    /**
     * 获取授权用户信息
     *
     * @param string $id 驱动ID
     * @param string $code 授权 code
     * @return UserInterface
     */
    public function getUser(string $id, string $code): UserInterface
    {
        return $this->getProviderManager($id)->userFromCode($code);
    }
}
