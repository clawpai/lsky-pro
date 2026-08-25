<?php

declare(strict_types=1);

namespace App\Services;

use App\PaymentChannel;
use App\PaymentMethod;
use App\PaymentProvider;

class PaymentService
{
    /**
     * 获取指定支付平台支持的支付通道
     *
     * @param PaymentProvider $provider
     * @return array
     */
    public function getPlatformChannels(PaymentProvider $provider): array
    {
        return match ($provider) {
            PaymentProvider::Alipay => [PaymentChannel::Alipay],
            PaymentProvider::Wechat => [PaymentChannel::Wechat],
            PaymentProvider::Paypal => [PaymentChannel::Paypal],
            PaymentProvider::UniPay => [PaymentChannel::UniPay],
            PaymentProvider::EPay => [
                PaymentChannel::Alipay,
                PaymentChannel::WXPay,
                PaymentChannel::QQPay,
                PaymentChannel::Paypal,
                PaymentChannel::Bank,
                PaymentChannel::JDPay,
            ],
        };
    }

    /**
     * 获取指定支付平台支持的支付方法
     *
     * @param PaymentProvider $provider
     * @return array
     */
    public function getPlatformMethods(PaymentProvider $provider): array
    {
        return match ($provider) {
            PaymentProvider::Alipay => [
                PaymentMethod::Web,
                PaymentMethod::H5,
                PaymentMethod::App,
                PaymentMethod::Mini,
                PaymentMethod::Pos,
                PaymentMethod::Scan,
            ],
            PaymentProvider::Wechat => [
                PaymentMethod::Mp,
                PaymentMethod::H5,
                PaymentMethod::App,
                PaymentMethod::Mini,
                PaymentMethod::Pos,
                PaymentMethod::Scan,
            ],
            PaymentProvider::Paypal => [
                PaymentMethod::Web,
            ],
            PaymentProvider::UniPay => [
                PaymentMethod::Web,
                PaymentMethod::H5,
                PaymentMethod::Scan,
                PaymentMethod::Pos,
            ],
            PaymentProvider::EPay => PaymentMethod::cases(),
        };
    }
}