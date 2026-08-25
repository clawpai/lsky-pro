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
        Schema::create('plan_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->comment('计划')->constrained('plans')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->comment('角色组')->constrained('groups')->onDelete('set null');
            $table->comment('计划可用组表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_groups');
    }
};
