<?php

namespace App\Drivers\Payment;

use App\Contracts\PaymentAbstract;
use App\DTOs\CreateOrderDto;
use App\PaymentChannel;
use App\PaymentMethod;
use App\Services\EPayService;
use Illuminate\Http\Request;

/**
 * EPay
 */
class EPayPayment extends PaymentAbstract
{
    protected EPayService $pay;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->pay = EPayService::make($config);
    }

    public function createOrder(CreateOrderDto $params, PaymentChannel $channel, PaymentMethod $method): array
    {
        // 支付方式映射
        $methods = [
            PaymentMethod::Web->value => 'jump', // 跳转支付
            PaymentMethod::Mp->value => 'jsapi', // 公众号内部支付
            PaymentMethod::Mini->value => 'applet', // 小程序支付
        ];

        $amount = sprintf('%.2f', $params->amount / 100);

        $order = [
            'notify_url' => $this->getConfig('notify_url'),
            'return_url' => $this->getConfig('return_url'),
            'out_trade_no' => $params->outTradeNo,
            'name' => $params->subject,
            'money'	=> $amount,
        ];

        $result = match ($channel) {
            PaymentChannel::Unified => $this->pay->getPayLink(array_merge($order, [
                'type' => $channel->value,
            ])),
            default => $this->pay->apiPay(array_merge($order, [
                'method' => $methods[$method->value] ?? $method->value,
                'type' => $channel->value,
                'clientip' => $params->clientIp,
            ])),
        };

        $payType = data_get($result, 'pay_type');
        $payInfo = data_get($result, 'pay_info');

        if (filter_var($result, FILTER_VALIDATE_URL)) {
            return ['action' => 'jump', 'url' => $result];
        }

        if ($payType === 'jump') {
            return ['action' => $payType, 'url' => $payInfo];
        }

        return ['action' => $payType, 'content' => $payInfo];
    }

    public function verifyNotify(Request $request): bool
    {
        return $this->pay->verify($request->all()) && $request->input('trade_status') === 'TRADE_SUCCESS';
    }

    public function verifyReturn(): string
    {
        return 'success';
    }
}