<?php

namespace App\Http\Controllers\V2;

use App\Exceptions\ServiceException;
use App\Facades\OrderService;
use App\Factories\PaymentDriverFactory;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\OrderStatus;
use App\PaymentProvider;
use App\Support\R;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NotifyController extends Controller
{
    public function __invoke(string $id, string $tradeNo, Request $request): Response
    {
        /** @var Order $order */
        $order = Order::where('out_trade_no', $tradeNo)->first();
        if (is_null($order)) {
            return R::error('订单不存在')->setStatusCode(404);
        }

        // 订单不是未支付状态
        if ($order->status !== OrderStatus::Unpaid) {
            return R::error('订单已支付')->setStatusCode(500);
        }

        try {
            /** @var Driver $payment */
            $payment = Driver::findOrFail($id);

            $pay = PaymentDriverFactory::create(
                platform: PaymentProvider::from($payment->options['provider']),
                config: $payment->options->getArrayCopy(),
            );

            // 验证回调
            if (!$pay->verifyNotify($request)) {
                throw new ServiceException('回调验证失败');
            }

            OrderService::complete($order, ['pay_method' => $payment->options['provider']]);

        } catch (Throwable $e) {
            Log::warning('支付回调处理失败', [
                'headers' => $request->headers->all(),
                'request' => $request->all(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return R::error($e->getMessage())->setStatusCode(500);
        }

        return \response($pay->verifyReturn());
    }
}
