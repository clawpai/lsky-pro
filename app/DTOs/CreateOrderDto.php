<?php

namespace App\DTOs;

use App\Facades\AppService;

class CreateOrderDto
{
    public function __construct(
        public string  $outTradeNo, // 支付订单号
        public string  $subject, // 订单主题
        public int     $amount, // 金额(分)
        public ?string $returnUrl = null, // 同步回调地址
        public ?string $notifyUrl = null, // 异步回调地址
        public ?string $cancelUrl = null, // 取消订单地址
        public ?string $clientIp = null, // 下单用户 ip
    )
    {
        $this->clientIp = $clientIp ?? AppService::getRequestIp(request());
    }
}