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
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('message_template_id')->nullable()->after('status')->comment('关联的消息模板ID');
            $table->foreign('message_template_id')->references('id')->on('message_templates')->onDelete('set null');
            
            // 确保error字段名称正确
            if (Schema::hasColumn('notification_logs', 'error_message') && !Schema::hasColumn('notification_logs', 'error')) {
                $table->renameColumn('error_message', 'error');
            } elseif (!Schema::hasColumn('notification_logs', 'error_message') && !Schema::hasColumn('notification_logs', 'error')) {
                $table->text('error')->nullable()->comment('错误信息');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropForeign(['message_template_id']);
            $table->dropColumn('message_template_id');
            
            if (Schema::hasColumn('notification_logs', 'error') && !Schema::hasColumn('notification_logs', 'error_message')) {
                $table->renameColumn('error', 'error_message');
            }
        });
    }
};
