<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\CreateOrderDto;
use App\PaymentChannel;
use App\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * 支付驱动抽象类
 */
abstract class PaymentAbstract
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 创建支付订单
     * @param CreateOrderDto $params 支付参数（如金额、订单号等）
     * @param PaymentChannel $channel 支付通道
     * @param PaymentMethod $method 支付类型
     * @return array 返回支付相关数据（如二维码链接、支付链接等）
     */
    abstract public function createOrder(CreateOrderDto $params, PaymentChannel $channel, PaymentMethod $method): array;

    /**
     * 处理支付通知
     *
     * @param Request $request
     * @return bool 返回通知处理是否成功
     */
    abstract public function verifyNotify(Request $request): bool;

    /**
     * 返回支付验证结果
     *
     * @return bool
     */
    abstract public function verifyReturn(): mixed;

    /**
     * @param string|array|int|null $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig($key, $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * 获取 YansongdaPay 支付配置
     *
     * @link https://pay.yansongda.cn
     * @param string $provider e.g: alipay
     * @return array
     */
    protected function getYansongdaPayOptions(string $provider): array
    {
        $defaultConfigs = $this->config;

        // 补充文件路径
        foreach ($defaultConfigs as $key => &$value) {
            if (in_array($key, [
                'app_public_cert_path',
                'alipay_public_cert_path',
                'alipay_root_cert_path',
                'mch_secret_cert',
                'mch_public_cert_path',
                'unipay_public_cert_path',
            ])) {
                $value = Storage::path($value);
            }
        }

        // 补充微信的支付公钥ID及证书路径
        if ($provider === 'wechat') {
            $defaultConfigs['wechat_public_cert_path'] = [
                $defaultConfigs['wechat_public_cert_id'] => Storage::path($defaultConfigs['wechat_public_cert_path']),
            ];
        }

        return [
            $provider => ['default' => $defaultConfigs],
            'logger' => [
                'enable' => true,
                'file' => storage_path('logs/payment.log'),
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'daily', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
        ];
    }
}