<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 第三方身份的安全边界：同一驱动 + 登录方式 + openid 只能绑定一个本站用户。
     * 旧记录的空 type 统一为 ''，确保普通单方式驱动同样受唯一约束保护。
     */
    public function up(): void
    {
        DB::table('oauth')->whereNull('type')->update(['type' => '']);
        DB::statement("ALTER TABLE oauth MODIFY type VARCHAR(32) NOT NULL DEFAULT '' COMMENT '三方登录方式（聚合驱动用：qq/wx/baidu 等）'");
        DB::statement('ALTER TABLE oauth ADD UNIQUE oauth_driver_type_openid_unique (driver_id, type, openid)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE oauth DROP INDEX oauth_driver_type_openid_unique');
        DB::statement("ALTER TABLE oauth MODIFY type VARCHAR(32) NULL DEFAULT NULL COMMENT '三方登录方式（聚合驱动用：qq/wx/baidu 等）'");
    }
};
