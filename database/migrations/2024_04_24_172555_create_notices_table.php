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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('标题');
            $table->longText('content')->nullable()->comment('内容');
            $table->unsignedBigInteger('view_count')->default(0)->comment('阅读量');
            $table->integer('sort')->default(0)->comment('排序值');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('系统公告表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
