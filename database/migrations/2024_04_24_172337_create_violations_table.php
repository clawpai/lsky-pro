<?php

use App\ViolationStatus;
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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('用户')->constrained('users')->onDelete('set null');
            $table->foreignId('photo_id')->nullable()->comment('图片')->constrained('photos')->onDelete('set null');
            $table->string('reason')->default('')->default('违规原因');
            $table->string('status', 32)->default(ViolationStatus::Unhandled)->comment('状态');
            $table->timestamp('handled_at')->nullable()->comment('处理时间');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('图片违规记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
