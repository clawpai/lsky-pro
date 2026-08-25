<?php

use App\CouponType;
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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            /** @see CouponType */
            $table->string('type', 32)->default(CouponType::Direct)->comment('折扣类型');
            $table->string('name', 32)->default('')->comment('名称');
            $table->string('code')->unique()->comment('券码');
            $table->decimal('value')->default(0)->comment('金额或折扣率');
            $table->unsignedInteger('usage_limit')->default(1)->comment('可使用次数');
            $table->timestamp('expired_at')->nullable()->comment('到期时间');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('优惠券表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
