<?php

use App\UserStatus;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('avatar')->default('')->comment('头像');
            $table->string('name')->default('')->comment('姓名');
            $table->string('username')->comment('用户名');
            $table->string('phone', 64)->nullable()->unique()->comment('手机号');
            $table->string('email')->unique()->comment('邮箱');
            $table->string('password')->comment('密码');
            $table->string('location', 64)->default('')->comment('所在地');
            $table->string('url', 255)->default('')->comment('个人网站');
            $table->string('company', 128)->default('')->comment('所在公司');
            $table->string('company_title', 128)->default('')->comment('工作职位');
            $table->string('tagline', 255)->default('')->comment('个性签名');
            $table->string('bio', 255)->default('')->comment('个人简介');
            $table->json('interests')->nullable()->comment('兴趣标签');
            $table->json('socials')->nullable()->comment('社交账号');
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->boolean('is_admin')->default(false)->comment('是否为管理员');
            $table->json('options')->nullable()->comment('配置');
            $table->ipAddress('login_ip')->nullable()->comment('最后登录 IP');
            $table->ipAddress('register_ip')->nullable()->comment('注册 IP');
            $table->string('country_code', 32)->nullable()->comment('国家');
            $table->string('status', 64)->default(UserStatus::Normal)->comment('状态');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('用户表');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->comment('密码重置令牌表');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->comment('会话表');
        });

        Schema::create('oauth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->comment('三方授权驱动ID')->constrained('drivers')->onDelete('cascade');
            $table->foreignId('user_id')->comment('用户ID')->constrained('users')->onDelete('cascade');
            $table->string('openid')->comment('三方授权ID');
            $table->string('avatar', 512)->default('')->comment('三方授权头像');
            $table->string('email')->default('')->comment('三方授权邮箱');
            $table->string('name')->default('')->comment('三方授权名称');
            $table->string('nickname')->default('')->comment('三方授权昵称');
            $table->json('raw')->nullable()->comment('三方授权原始信息');
            $table->timestamps();
            $table->comment('三方授权表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('oauth');
    }
};
