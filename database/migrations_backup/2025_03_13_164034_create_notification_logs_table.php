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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index()->comment('通知类型');
            $table->string('recipient')->index()->comment('接收人邮箱');
            $table->string('subject')->nullable()->comment('通知主题');
            $table->text('content')->nullable()->comment('通知内容');
            $table->string('status')->default('pending')->comment('发送状态：pending, sent, failed');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('sent_at')->nullable()->comment('发送时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
