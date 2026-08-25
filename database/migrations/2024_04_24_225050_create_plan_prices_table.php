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
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->comment('计划')->constrained('plans')->onDelete('cascade');
            $table->string('name')->comment('名称');
            $table->integer('duration')->default(0)->comment('时长(分钟)');
            $table->integer('price')->default(0)->comment('价格(分)');
            $table->timestamps();
            $table->comment('计划套餐阶梯价格表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
