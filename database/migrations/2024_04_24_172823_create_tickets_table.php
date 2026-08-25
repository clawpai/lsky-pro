<?php

use App\TicketLevel;
use App\TicketStatus;
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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('用户')->constrained('users')->onDelete('cascade');
            $table->string('issue_no')->unique()->comment('工单编号');
            $table->string('title', 255)->comment('标题');
            /** @see TicketLevel */
            $table->string('level', 32)->default(TicketLevel::Low)->comment('级别');
            /** @see TicketStatus */
            $table->string('status', 32)->default(TicketStatus::InProgress)->comment('状态');
            $table->softDeletes();
            $table->timestamps();
            $table->comment('工单表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
