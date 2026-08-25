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
        Schema::create('plan_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->comment('计划')->constrained('plans')->onDelete('cascade');
            $table->decimal('capacity', 20)->nullable()->default(0)->comment('容量(kb)');
            $table->comment('计划可用容量表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_capacities');
    }
};
