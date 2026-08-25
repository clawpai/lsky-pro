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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            /** @see \App\DriverType */
            $table->string('type')->comment('驱动类型');
            $table->string('name')->comment('名称');
            $table->string('intro', 2000)->default('')->comment('简介');
            $table->json('options')->nullable()->comment('配置');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('驱动表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
