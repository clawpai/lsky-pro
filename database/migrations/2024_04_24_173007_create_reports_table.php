<?php

use App\ReportStatus;
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_user_id')->nullable()->comment('被举报用户')->constrained('users')->onDelete('set null');
            $table->string('reportable_type', 32);
            $table->unsignedBigInteger('reportable_id');
            $table->string('content', 255)->nullable()->comment('原因');
            $table->string('status', 32)->default(ReportStatus::Unhandled)->comment('状态');
            $table->timestamp('handled_at')->nullable()->comment('处理时间');
            $table->ipAddress('ip_address')->nullable()->comment('IP 地址');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('举报记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
