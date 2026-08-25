<?php

namespace App\Drivers\Payment;

use App\Contracts\PaymentAbstract;
use App\DTOs\CreateOrderDto;
use App\PaymentChannel;
use App\PaymentMethod;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Supports\Collection;

/**
 * 支付宝支付
 */
class AlipayPayment extends PaymentAbstract
{
    protected Alipay $pay;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->pay = Pay::alipay($this->getYansongdaPayOptions('alipay'));
    }

    public function createOrder(CreateOrderDto $params, PaymentChannel $channel, PaymentMethod $method): array
    {
        $amount = sprintf('%.2f', $params->amount / 100);

        $order = [
            'out_trade_no' => $params->outTradeNo,
            'total_amount' => $amount,
            'subject' => $params->subject,
        ];

        $result = $this->pay->{$method->value}(match ($method) {
            // 支付宝网页、H5
            PaymentMethod::Web, PaymentMethod::H5 => array_merge($order, ['_method' => 'get']),
            // 支付宝扫码、APP支付
            PaymentMethod::Scan, PaymentMethod::App => $order,
        });

        if ($result instanceof Collection && $result->get('code') !== '10000') {
            throw new InvalidArgumentException($result->get('sub_msg', $result->get('msg')));
        }

        return match ($method) {
            PaymentMethod::App => ['content' => $result->getBody()->getContents()],
            PaymentMethod::Web, PaymentMethod::H5 => ['url' => $result->getHeaderLine('Location')],
            PaymentMethod::Scan => ['qr_code' => $result->get('qr_code')],
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

    public function verifyReturn(): string
    {
        return 'success';
    }
}