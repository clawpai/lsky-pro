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
        Schema::create('shareables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_id')->comment('分享')->constrained('shares')->onDelete('cascade');
            $table->morphs('shareable');
            $table->comment('分享内容表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shareables');
    }
};
