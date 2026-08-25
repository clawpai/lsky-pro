<?php

use App\PhotoStatus;
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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('用户')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->comment('角色组')->constrained('groups')->onDelete('set null');
            $table->foreignId('storage_id')->nullable()->comment('储存')->constrained('storages')->onDelete('cascade');
            $table->string('name')->comment('文件别名');
            $table->string('intro', 2000)->default('')->comment('介绍');
            $table->string('filename')->comment('文件原始名称');
            $table->string('pathname')->comment('文件路径名称');
            $table->string('mimetype', 64)->default('')->comment('媒体类型');
            $table->string('extension', 32)->default('')->comment('文件后缀');
            $table->string('md5', 32)->default('')->comment('文件MD5');
            $table->string('sha1')->default('')->comment('文件SHA1');
            $table->json('exif')->nullable()->comment('EXIF 信息');
            $table->decimal('size', 20)->default(0)->comment('大小(kb)');
            $table->unsignedInteger('width')->default(0)->comment('宽度');
            $table->unsignedInteger('height')->default(0)->comment('高度');
            $table->boolean('is_public')->default(false)->comment('是否公开');
            $table->string('status', 64)->default(PhotoStatus::Normal)->comment('状态');
            $table->ipAddress('ip_address')->nullable()->comment('上传IP');
            $table->timestamp('expired_at')->nullable()->comment('到期时间');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('图片表');

            $table->index('user_id');
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
