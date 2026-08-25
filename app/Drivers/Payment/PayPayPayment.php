<?php

namespace App\Drivers\Payment;

use App\Contracts\PaymentAbstract;
use App\DTOs\CreateOrderDto;
use App\PaymentChannel;
use App\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Srmklive\PayPal\Services\PayPal;
use Throwable;

/**
 * PayPal
 */
class PayPayPayment extends PaymentAbstract
{
    protected PayPal $pay;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->pay = new PayPal([
            'mode' => $this->getConfig('mode'),
            $this->getConfig('mode') => Arr::only($this->config, ['app_id', 'client_id', 'client_secret']),

            'payment_action' => 'Sale',
            'currency' => $this->getConfig('currency'),
            'notify_url' => $this->getConfig('notify_url'),
            'locale' => 'zh_CN',
            'validate_ssl' => true,
        ]);
    }

    public function createOrder(CreateOrderDto $params, PaymentChannel $channel, PaymentMethod $method): array
    {
        try {
            $this->pay->getAccessToken();

            $result = (array)$this->pay->createOrder(match ($method) {
                // paypal
                // 下单文档：https://developer.paypal.com/docs/api/orders/v2/#orders_create
                PaymentMethod::Web => [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'reference_id' => $params->outTradeNo,
                            'description' => $params->subject,
                            'amount' => [
                                'currency_code' => $this->getConfig('currency', 'USD'),
                                'value' => sprintf('%.2f', $params->amount / 100),
                            ]
                        ]
                    ],
                    'payment_source' => [
                        'paypal' => [
                            'experience_context' => [
                                'user_action' => 'PAY_NOW',
                                'shipping_preference' => 'NO_SHIPPING',
                                'return_url' => $this->getConfig('return_url'),
                                'cancel_url' => $this->getConfig('cancel_url'),
                            ]
                        ]
                    ]
                ],
            });

            $url = collect($result['links'] ?? [])->where('rel', 'payer-action')->first()['href'];

            return compact('url');
        } catch (Throwable $e) {
            return [];
        }
    }

    public function verifyNotify(Request $request): bool
    {
        try {
            $this->pay->getAccessToken();

            // @see https://github.com/srmklive/laravel-paypal/issues/434#issuecomment-1032708943
            $result = $this->pay->verifyWebHook([
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url' => $request->header('PAYPAL-CERT-URL'),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => $this->getConfig('webhook_id'),
                'webhook_event' => $request->all(),
            ]);

            return array_key_exists('verification_status', $result) && 'SUCCESS' === $result['verification_status'];
        } catch (Throwable $e) {
            return false;
        }
    }

    public function verifyReturn(): string
    {
        return '';
    }
}