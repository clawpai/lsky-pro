<?php

use App\FeedbackType;
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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            /** @see FeedbackType */
            $table->string('type', 32)->default(FeedbackType::General)->comment('类型');
            $table->string('title', 64)->comment('标题');
            $table->string('name', 64)->comment('姓名');
            $table->string('email', 128)->comment('email');
            $table->longText('content')->comment('内容');
            $table->ipAddress('ip_address')->nullable()->comment('IP 地址');
            $table->timestamps();
            $table->comment('意见与反馈表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
