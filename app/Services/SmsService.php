<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\VerifyCodeService;
use App\SmsProvider;
use Arr;
use Illuminate\Support\Facades\Log;
use libphonenumber\PhoneNumberUtil;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\PhoneNumber;
use Overtrue\EasySms\Strategies\OrderStrategy;
use Throwable;

class SmsService
{
    protected array $providers = [];
    protected ?EasySms $easySms = null;

    /**
     * 获取实例
     *
     * @param array $providers
     * @return $this
     */
    public function instance(array $providers): self
    {
        $this->providers = $providers;

        $gateways = [];

        foreach ($providers as $provider) {
            $gateways[$provider['provider']] = Arr::except($provider, ['provider', 'templates']);
        }

        // 创建独立的EasySms实例，避免修改全局配置
        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                    ...Arr::pluck($providers, 'provider'),
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => storage_path('logs/sms.log'),
                ],
                ...$gateways
            ],
        ];

        $this->easySms = new EasySms($config);

        return $this;
    }

    /**
     * 发送验证码短信短信
     *
     * @param string $event
     * @param string $phone
     * @param int|string $countryCode
     * @return bool
     */
    public function sendCode(string $event, string $phone, int|string $countryCode = 'cn'): bool
    {
        if (!$this->easySms) {
            Log::error('短信服务未初始化');
            return false;
        }

        try {
            $code = VerifyCodeService::generateCode($this->getCodeKey($event, $phone));

            $this->easySms->send($phone, [
                'content' => function (Gateway $gateway) use ($code, $event) {
                    $content = $this->getProviderTemplate($gateway->getName(), $event)['content'] ?? '';
                    // 将全角符号替换为半角
                    $content = str_replace(['｛', '｝'], ['{', '}'], $content);
                    return str_replace('{code}', $code, $content);
                },
                'template' => function (Gateway $gateway) use ($event) {
                    return $this->getProviderTemplate($gateway->getName(), $event)['template'] ?? '';
                },
                'data' => fn(Gateway $gateway) => match (SmsProvider::from($gateway->getName())) {
                    SmsProvider::Aliyun,
                    SmsProvider::AliyunRest,
                    SmsProvider::Qiniu,
                    SmsProvider::UCloud,
                    SmsProvider::Zzyun,
                    SmsProvider::Volcengine => compact('code'),
                    SmsProvider::AliyunIntl => new PhoneNumber($phone, PhoneNumberUtil::getInstance()->getCountryCodeForRegion($countryCode)),
                    SmsProvider::Chuanglanv1 => "phone\":\"{$phone},{$code}",
                    SmsProvider::QCloud,
                    SmsProvider::Huawei,
                    SmsProvider::Moduyun,
                    SmsProvider::Maap,
                    SmsProvider::Tinree => [$code],
                    SmsProvider::Yunxin => ['code' => $code, 'action' => 'sendCode'],
                    SmsProvider::Yunzhixun => ['params' => $code],
                    SmsProvider::Rongheyun => ['valid_code' => $code],
                    default => [],
                }
            ]);

        } catch (NoGatewayAvailableException $e) {
            Log::warning("短信发送失败", [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'results' => $e->getResults(),
                'exceptions' => $e->getExceptions(),
            ]);
            /** @var GatewayErrorException $exception */
            foreach ($e->getExceptions() as $index => $exception) {
                $raw = $exception->raw ?? [];
                Log::error("错误详情{$index}：", compact('raw', 'exception'));
            }
            return false;
        } catch (Throwable $e) {
            Log::warning("短信发送异常", [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'trade' => $e->getTraceAsString(),
            ]);
            return false;
        }

        return true;
    }

    public function getCodeKey(string $event, string $phone): string
    {
        return "sms_code:{$event}:{$phone}";
    }

    /**
     * 获取指定短信服务商模板配置
     *
     * @param string $provider
     * @param string $event
     * @return array
     */
    public function getProviderTemplate(string $provider, string $event): array
    {
        return Arr::first($this->getProviderTemplates($provider), function ($value) use ($event) {
            return $event === $value['event'];
        }) ?: [];
    }

    /**
     * 获取指定短信服务商模板配置
     *
     * @param string $provider
     * @return array
     */
    public function getProviderTemplates(string $provider): array
    {
        return $this->getProviderConfig($provider)['templates'] ?? [];
    }

    /**
     * 获取指定短信服务商配置
     *
     * @param string $provider
     * @return array
     */
    public function getProviderConfig(string $provider): array
    {
        return Arr::first($this->providers, function ($value) use ($provider) {
            return $provider === $value['provider'];
        });
    }

    /**
     * 验证短信验证码
     *
     * @param string $event
     * @param string $phone
     * @param string|null $code
     * @return bool
     */
    public function verifyCode(string $event, string $phone, ?string $code): bool
    {
        return VerifyCodeService::verifyCode($this->getCodeKey($event, $phone), $code);
    }
}
