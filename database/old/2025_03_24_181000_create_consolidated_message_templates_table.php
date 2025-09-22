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
     * - 2024_03_15_000001_create_message_templates_table.php
     * - 2025_03_24_000023_add_supplier_and_default_fields_to_message_templates_table.php
     * - 2025_03_24_000024_create_message_template_supplier_table.php
     */
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('channel'); // email
            $table->string('type')->nullable(); // 模板类型，如system, order, inventory等
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('subject')->nullable(); // 邮件主题
            $table->text('content'); // 模板内容
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->unique(['name', 'channel']);
            $table->index(['channel', 'type']);
            $table->index(['status']);
            $table->index(['name', 'supplier_id']);
            $table->index(['is_default', 'name', 'channel']);
        });
        
        Schema::create('message_template_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // 添加唯一索引确保不会有重复的关联
            $table->unique(['message_template_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_template_supplier');
        Schema::dropIfExists('message_templates');
    }
}; 