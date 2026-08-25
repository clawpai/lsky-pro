<?php

namespace App\Socialite;

use GuzzleHttp\Exception\GuzzleException;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Providers\Base;
use Overtrue\Socialite\User;

/**
 * 聚合 CC 登录 Provider
 *
 * 对接标准聚合登录网关（QQ/微信/微博等），
 * 使用 appid/appkey 认证，协议四步：
 * 1. act=login 获取跳转地址
 * 2. 用户跳转授权
 * 3. 回调带 code
 * 4. act=callback 用 code 换用户信息
 */
class CCLoginProvider extends Base
{
    public const NAME = 'cc';

    /**
     * 网关地址可由后台配置；公开源码仅提供安全示例地址。
     */
    protected string $endpoint = 'https://example.com/connect.php';

    /**
     * 登录方式（可在后台配置，如 qq/wx/sina/baidu 等）
     */
    protected string $loginType = 'qq';

    public function __construct(array $config, ?string $type = null)
    {
        parent::__construct($config);

        if ($this->config->has('endpoint') && ! empty($this->config->get('endpoint'))) {
            $this->endpoint = rtrim((string)$this->config->get('endpoint'), '/');
        }

        if ($this->config->has('type') && ! empty($this->config->get('type'))) {
            $this->loginType = (string)$this->config->get('type');
        }

        // 单次登录指定的 type 优先（多方式驱动：qq/wx/baidu 选一）
        if (! empty($type)) {
            $this->loginType = $type;
        }
    }

    /**
     * 获取拼接了公参的请求地址
     */
    protected function buildEndpointUrl(string $act, array $extra = []): string
    {
        return $this->endpoint . '?' . http_build_query(array_merge([
            'act' => $act,
            'appid' => (string)$this->config->get('appid', ''),
            'appkey' => (string)$this->config->get('appkey', ''),
            'type' => $this->loginType,
        ], $extra));
    }

    /**
     * Step1: 获取跳转登录地址
     */
    protected function getAuthUrl(): string
    {
        try {
            $response = $this->getHttpClient()->get($this->buildEndpointUrl('login', [
                'redirect_uri' => $this->redirectUrl ?? '',
            ]));

            $result = $this->fromJsonBody($response);

            if (! isset($result['code']) || (int)$result['code'] !== 0 || empty($result['url'])) {
                throw new AuthorizeFailedException('聚合登录获取跳转地址失败: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $result);
            }

            return (string)$result['url'];
        } catch (GuzzleException $e) {
            throw new AuthorizeFailedException('聚合登录请求失败: ' . $e->getMessage(), []);
        }
    }

    /**
     * Step4: 用 code 直接换取用户信息（聚合登录无独立 token 换取流程）
     */
    public function userFromCode(string $code): Contracts\UserInterface
    {
        try {
            $response = $this->getHttpClient()->get($this->buildEndpointUrl('callback', [
                'code' => $code,
            ]));

            $result = $this->fromJsonBody($response);

            if (! isset($result['code']) || (int)$result['code'] !== 0) {
                throw new AuthorizeFailedException('聚合登录回调失败: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $result);
            }

            return $this->mapUserToObject($result);
        } catch (GuzzleException $e) {
            throw new AuthorizeFailedException('聚合登录请求失败: ' . $e->getMessage(), []);
        }
    }

    /**
     * Base 抽象方法实现（聚合登录不使用 token 流程）
     */
    protected function getTokenUrl(): string
    {
        return $this->endpoint;
    }

    protected function getUserByToken(string $token): array
    {
        return [];
    }

    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['social_uid'] ?? null,
            Contracts\ABNF_NAME => $user['nickname'] ?? null,
            Contracts\ABNF_NICKNAME => $user['nickname'] ?? null,
            Contracts\ABNF_EMAIL => null,
            Contracts\ABNF_AVATAR => $user['faceimg'] ?? null,
        ]);
    }
}