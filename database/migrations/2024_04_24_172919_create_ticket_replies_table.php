<?php

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
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->comment('工单')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->comment('用户')->constrained('users')->onDelete('cascade');
            $table->longText('content')->comment('内容');
            $table->boolean('is_notify')->default(true)->comment('是否需要接收通知');
            $table->timestamp('read_at')->nullable()->comment('已读时间');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('工单回复记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};
