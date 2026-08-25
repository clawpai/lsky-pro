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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('用户')->constrained('users')->onDelete('cascade');
            $table->string('name')->default('')->comment('名称');
            $table->string('intro', 2000)->default('')->comment('介绍');
            $table->boolean('is_public')->default(false)->comment('是否公开');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('相册表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
