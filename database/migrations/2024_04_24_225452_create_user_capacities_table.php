<?php

use App\UserCapacityFrom;
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
        Schema::create('user_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('用户')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->comment('来源订单')->constrained('orders')->onDelete('set null');
            $table->decimal('capacity', 20)->nullable()->default(0)->comment('容量(kb)');
            /** @see UserCapacityFrom */
            $table->string('from', 32)->default(UserCapacityFrom::System)->comment('来源');
            $table->timestamp('expired_at')->nullable()->comment('到期时间');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('用户容量表');

            $table->index('expired_at');
            $table->index('capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_capacities');
    }
};
