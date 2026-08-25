<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\PaymentAbstract;
use App\Drivers\Payment\AlipayPayment;
use App\Drivers\Payment\EPayPayment;
use App\Drivers\Payment\PayPayPayment;
use App\Drivers\Payment\UniPayPayment;
use App\Drivers\Payment\WeChatPayment;
use App\PaymentProvider;
use InvalidArgumentException;

/**
 * 支付工厂类
 */
class PaymentDriverFactory
{
    public static function create(PaymentProvider $platform, array $config): PaymentAbstract
    {
        return match ($platform) {
            PaymentProvider::Alipay => new AlipayPayment($config),
            PaymentProvider::Wechat => new WechatPayment($config),
            PaymentProvider::UniPay => new UniPayPayment($config),
            PaymentProvider::Paypal => new PayPayPayment($config),
            PaymentProvider::EPay => new EPayPayment($config),
            default => throw new InvalidArgumentException("Unsupported payment driver: {$platform->name}"),
        };
    }
}