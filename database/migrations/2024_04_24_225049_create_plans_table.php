<?php

use App\PlanType;
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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default(PlanType::Vip)->comment('类型');
            $table->string('name')->comment('名称');
            $table->text('intro')->nullable()->comment('简介');
            $table->json('features')->nullable()->comment('特点');
            $table->string('badge', 32)->default('')->comment('徽章内容');
            $table->integer('sort')->default(0)->comment('排序值');
            $table->boolean('is_up')->default(false)->comment('是否上架');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('计划套餐表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
