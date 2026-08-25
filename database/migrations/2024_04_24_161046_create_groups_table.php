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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->string('intro', 2000)->default('')->comment('描述');
            $table->json('options')->nullable()->comment('配置');
            $table->boolean('is_default')->default(false)->comment('是否为默认组');
            $table->boolean('is_guest')->default(false)->comment('是否为游客组');
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
        Schema::dropIfExists('groups');
    }
};
