<?php

use App\OrderStatus;
use App\OrderType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->nullable()->comment('计划')->constrained('plans')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->comment('用户')->constrained('users')->onDelete('set null');
            $table->foreignId('coupon_id')->nullable()->comment('优惠券')->constrained('coupons')->onDelete('set null');
            $table->string('trade_no')->unique()->comment('系统订单号');
            $table->string('out_trade_no')->unique()->comment('支付订单号');
            /** @see OrderType */
            $table->string('type', 32)->default(OrderType::Plan)->comment('类型');
            $table->unsignedInteger('amount')->default(0)->comment('实际付款金额(分)');
            $table->unsignedInteger('deduct_amount')->default(0)->comment('抵扣金额(分)');
            $table->json('snapshot')->nullable()->comment('产品快照');
            $table->json('product')->nullable()->comment('购买产品数据');
            $table->string('pay_method')->default('')->comment('支付方式');
            $table->string('status', 32)->default(OrderStatus::Unpaid)->comment('状态');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->timestamp('canceled_at')->nullable()->comment('取消时间');
            $table->timestamps();
            $table->comment('订单表');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
