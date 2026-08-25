<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * oauth 表增加 type 列：支持同一聚合驱动（聚合CC）绑定多个登录方式（qq/wx/baidu）
     * 纯增量：兼容原有记录（type 为 null）
     */
    public function up(): void
    {
        Schema::table('oauth', function (Blueprint $table) {
            $table->string('type', 32)->nullable()->after('openid')->comment('三方登录方式（聚合驱动用：qq/wx/baidu 等）');
        });
    }

    public function down(): void
    {
        Schema::table('oauth', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};