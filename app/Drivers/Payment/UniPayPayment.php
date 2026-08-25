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
use Yansongda\Pay\Provider\Unipay;

/**
 * 银联支付
 */
class UniPayPayment extends PaymentAbstract
{
    protected Unipay $pay;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->pay = Pay::unipay($this->getYansongdaPayOptions('unipay'));
    }

    public function createOrder(CreateOrderDto $params, PaymentChannel $channel, PaymentMethod $method): array
    {
        $result = $this->pay->{$method->value}(match ($method) {
            // 银联电脑支付、H5 支付、扫码支付
            PaymentMethod::Web, PaymentMethod::H5, PaymentMethod::Scan => [
                'txnTime' => date('YmdHis'),
                'txnAmt' => sprintf('%.2f', $params->amount / 100),
                'orderId' => $params->outTradeNo,
            ],
        });

        // TODO 验证
        return match ($method) {
            PaymentMethod::Web,
            PaymentMethod::H5,
            PaymentMethod::Scan => ['content' => $result->getBody()->getContents()],
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