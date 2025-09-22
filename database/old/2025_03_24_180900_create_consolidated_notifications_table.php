<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Consolidated from the following migrations:
     * - 2025_03_13_164034_create_notification_logs_table.php
     * - 2025_03_15_215234_add_user_id_to_notification_logs_table.php
     * - 2023_10_15_000000_create_notification_settings_tables.php
     */
    public function up(): void
    {
        // 创建通知设置表
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->unique()->comment('Notification type (e.g. new_product, homepage_updated)');
            $table->timestamps();
        });

        Schema::create('notification_receivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_setting_id')->constrained()->onDelete('cascade');
            $table->string('email')->comment('Email address of the notification receiver');
            $table->timestamps();
            
            // 一个邮箱在一个通知类型中只能出现一次
            $table->unique(['notification_setting_id', 'email']);
        });
        
        // 创建通知日志表
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index()->comment('通知类型');
            $table->string('recipient')->index()->comment('接收人邮箱');
            $table->unsignedBigInteger('user_id')->nullable()->comment('关联的用户ID');
            $table->string('subject')->nullable()->comment('通知主题');
            $table->text('content')->nullable()->comment('通知内容');
            $table->string('status')->default('pending')->comment('发送状态：pending, sent, failed');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('sent_at')->nullable()->comment('发送时间');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
        
        
        
        // 插入默认的通知类型
        DB::table('notification_settings')->insert([
            ['type' => 'settings', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'featured_products', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'new_arrivals', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'sale_products', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'product_created', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_receivers');
        Schema::dropIfExists('notification_settings');
    }
}; 