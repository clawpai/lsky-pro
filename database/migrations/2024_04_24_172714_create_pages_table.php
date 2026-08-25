<?php

use App\PageType;
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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            /** @see PageType */
            $table->string('type', 32)->default(PageType::Internal)->comment('类型');
            $table->string('name')->comment('名称');
            $table->string('icon', 64)->default('')->comment('图标');
            $table->string('title')->default('')->comment('标题');
            $table->longText('content')->nullable()->comment('网页内容');
            $table->text('keywords')->nullable()->comment('网页关键字');
            $table->text('description')->nullable()->comment('网页描述');
            $table->string('slug')->default('')->comment('url slug');
            $table->string('url')->default('')->comment('跳转url');
            $table->unsignedBigInteger('view_count')->default(0)->comment('浏览量');
            $table->integer('sort')->default(0)->comment('排序值');
            $table->boolean('is_show')->default(false)->comment('是否显示');
            $table->timestamps();
            $table->comment('页面表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
