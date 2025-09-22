<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
        Schema::dropIfExists('notification_receivers');
        Schema::dropIfExists('notification_settings');
    }
}; 