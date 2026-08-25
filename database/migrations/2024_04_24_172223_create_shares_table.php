<?php

use App\ShareType;
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
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('用户')->constrained('users')->onDelete('cascade');
            $table->string('type', 32)->default(ShareType::Album)->comment('分享类型');
            $table->string('slug')->unique()->comment('url slug');
            $table->text('content')->nullable()->comment('分享内容');
            $table->string('password', 128)->default('')->comment('密码');
            $table->unsignedBigInteger('view_count')->default(0)->comment('浏览量');
            $table->timestamp('expired_at')->nullable()->comment('到期时间');
            $table->timestamps();
            $table->comment('分享表');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
