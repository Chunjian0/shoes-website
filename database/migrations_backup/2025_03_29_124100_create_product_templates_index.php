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
        Schema::table('product_templates', function (Blueprint $table) {
            // 添加索引以提高查询性能
            $table->index('name');
            $table->index('category_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            // 删除索引
            $table->dropIndex(['name']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_active']);
        });
    }
};
