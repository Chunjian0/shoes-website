<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Consolidated from the following migrations:
     * - 2025_03_21_232258_create_product_analytics_tables.php
     */
    public function up(): void
    {
        // 产品展示统计表 - 按日期和区域汇总
        Schema::create('product_display_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('section_type')->nullable();
            $table->string('section_id')->nullable();
            $table->date('date');
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();
            
            // 复合索引
            $table->unique(['product_id', 'section_type', 'date']);
            $table->index(['date', 'section_type']);
        });
        
        // 产品展示明细表
        Schema::create('product_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('section_type')->nullable();
            $table->integer('position')->nullable();
            $table->string('device_type')->nullable();
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // 索引
            $table->index(['product_id', 'viewed_at']);
            $table->index(['viewed_at']);
            $table->index(['section_type']);
        });
        
        // 产品点击明细表
        Schema::create('product_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('section_type')->nullable();
            $table->integer('position')->nullable();
            $table->string('device_type')->nullable();
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();
            
            // 索引
            $table->index(['product_id', 'clicked_at']);
            $table->index(['clicked_at']);
            $table->index(['section_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_clicks');
        Schema::dropIfExists('product_impressions');
        Schema::dropIfExists('product_display_stats');
    }
};