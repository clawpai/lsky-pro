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
        Schema::create('album_photo', function (Blueprint $table) {
            $table->foreignId('album_id')->comment('相册')->constrained()->onDelete('cascade');
            $table->foreignId('photo_id')->comment('图片')->constrained()->onDelete('cascade');
            $table->integer('sort')->default(0)->comment('排序值');
            $table->primary(['album_id', 'photo_id']);

            $table->comment('相册与图片中间表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('album_photo');
    }
};
