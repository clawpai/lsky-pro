<?php

use App\UserGroupFrom;
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
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('用户')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_id')->comment('角色组')->constrained('groups')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->comment('来源订单')->constrained('orders')->onDelete('set null');
            /** @see UserGroupFrom */
            $table->string('from', 32)->default(UserGroupFrom::System)->comment('来源');
            $table->timestamp('expired_at')->nullable()->comment('到期时间');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('用户角色组表');

            $table->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_groups');
    }
};
