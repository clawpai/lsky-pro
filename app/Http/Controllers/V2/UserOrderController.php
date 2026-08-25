<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserOrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPaymentRequest;
use App\Http\Requests\QueryRequest;
use App\Http\Resources\UserOrderResource;
use App\Support\R;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class UserOrderController extends Controller
{
    /**
     * 订单列表
     */
    public function index(QueryRequest $request): Response
    {
        $orders = UserOrderService::paginate($request->validated());

        return R::success(data: UserOrderResource::collection($orders)->response()->getData());
    }

    /**
     * 预览订单
     */
    public function preview(Request $request): Response
    {
        $data = UserOrderService::preview((int)$request->post('price_id'), $request->post('coupon_code'));

        return R::success(data: Arr::only($data, ['amount', 'deduct_amount']));
    }

    /**
     * 创建订单
     */
    public function store(Request $request): Response
    {
        $order = UserOrderService::store((int)$request->post('price_id'), $request->post('coupon_code'));

        return R::success(data: array_merge($order->only('trade_no'), [
            'is_paid' => ! is_null($order->paid_at), // 是否直接支付成功了
        ]))->setStatusCode(201);
    }

    /**
     * 订单详情
     */
    public function show(string $tradeNo): Response
    {
        $order = UserOrderService::show($tradeNo);

        return R::success(data: UserOrderResource::make($order));
    }

    /**
     * 取消订单
     */
    public function cancel(string $tradeNo): Response
    {
        UserOrderService::cancel($tradeNo);

        return R::success()->setStatusCode(204);
    }

    /**
     * 删除订单
     */
    public function destroy(string $tradeNo): Response
    {
        UserOrderService::destroy($tradeNo);

        return R::success()->setStatusCode(204);
    }

    /**
     * 支付订单
     */
    public function pay(string $tradeNo, OrderPaymentRequest $request): Response
    {
        $result = UserOrderService::pay($request);

        return R::success(data: $result)->setStatusCode(201);
    }
}
