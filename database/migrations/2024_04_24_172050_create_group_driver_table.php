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
        Schema::create('group_driver', function (Blueprint $table) {
            /** @see \App\DriverType */
            $table->string('type', 32)->comment('驱动类型');
            $table->foreignId('group_id')->comment('角色组')->constrained('groups')->onDelete('cascade');
            $table->foreignId('driver_id')->comment('驱动')->constrained('drivers')->onDelete('cascade');
            $table->integer('sort')->default(0)->comment('排序值');
            $table->primary(['group_id', 'driver_id']);

            $table->comment('组与驱动中间表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_driver');
    }
};
