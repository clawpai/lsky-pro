<?php

namespace App\Drivers\Payment;

use App\Contracts\PaymentAbstract;
use App\DTOs\CreateOrderDto;
use App\PaymentChannel;
use App\PaymentMethod;
use Illuminate\Http\Request;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;

/**
 * 微信支付
 */
class WeChatPayment extends PaymentAbstract
{
    protected Wechat $pay;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->pay = Pay::wechat($this->getYansongdaPayOptions('wechat'));
    }

    public function createOrder(CreateOrderDto $params, PaymentChannel $channel, PaymentMethod $method): array
    {
        $order = [
            'out_trade_no' => $params->outTradeNo,
            'description' => $params->subject,
            'amount' => [
                'total' => $params->amount,
            ],
        ];

        $result = $this->pay->{$method->value}(match ($method) {
            // 微信扫码支付、APP、H5
            PaymentMethod::Scan, PaymentMethod::App => $order,
            PaymentMethod::H5 => array_merge($order, [
                'scene_info' => [
                    'payer_client_ip' => $params->clientIp,
                    'h5_info' => [
                        'type' => 'Wap',
                    ]
                ],
            ]),
        });

        return match ($method) {
            PaymentMethod::H5 => ['url' => $result->get('h5_url')],
            PaymentMethod::Scan => ['content' => $result->get('code_url')],
        };
    }

    public function verifyNotify(Request $request): bool
    {
        try {
            $this->pay->callback($request->all());
        } catch (ContainerException|InvalidParamsException $e) {
            return false;
        }

        return true;
    }

    public function verifyReturn(): array
    {
        return ['code' => 'SUCCESS', 'message' => '成功'];
    }
}