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
        Schema::create('group_storage', function (Blueprint $table) {
            $table->foreignId('group_id')->comment('角色组')->constrained('groups')->onDelete('cascade');
            $table->foreignId('storage_id')->comment('储存')->constrained('storages')->onDelete('cascade');
            $table->integer('sort')->default(0)->comment('排序值');
            $table->primary(['group_id', 'storage_id']);

            $table->comment('组与储存中间表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_storage');
    }
};
