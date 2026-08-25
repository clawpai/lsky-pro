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
        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->nullable()->comment('标签')->constrained('tags')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->comment('用户')->constrained('users')->onDelete('cascade');
            $table->morphs('taggable');
            $table->comment('标签表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
