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
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->string('intro', 2000)->default('')->comment('描述');
            $table->string('prefix', 1000)->default('')->comment('储存前缀');
            /** @see \App\StorageProvider */
            $table->string('provider', 64)->comment('储存提供者');
            $table->json('options')->nullable()->comment('储存配置');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('角色组表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storages');
    }
};
