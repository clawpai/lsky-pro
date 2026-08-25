<?php

namespace App\Jobs;

use App\Facades\OrderService;
use App\Models\Order;
use App\OrderStatus;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 延时取消未支付订单
 */
class CancelOrderJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public Order $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->withoutRelations();

        // 延时1小时
        $this->delay = 3600;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var Order $order */
        $order = Order::find($this->order->id);
        if (! is_null($order) && $order->status === OrderStatus::Unpaid) {
            OrderService::cancel($order);
        }
    }

    public function uniqueId(): string
    {
        return md5('cancel-order:' . $this->order->trade_no);
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("延时取消未支付订单执行失败，{$exception?->getMessage()}", [
            'photo' => $this->order,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
