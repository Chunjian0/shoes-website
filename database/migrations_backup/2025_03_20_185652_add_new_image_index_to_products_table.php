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
        Schema::table('products', function (Blueprint $table) {
            // 添加新品图片索引
            if (!Schema::hasColumn('products', 'new_image_index')) {
                $table->integer('new_image_index')->default(0)->comment('新品页面展示时使用的图片索引');
            }

            if (!Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->comment('是否为特色产品');
            }

            if (!Schema::hasColumn('products', 'is_new')) {
                $table->boolean('is_new')->default(false)->comment('是否为新品');
            }

            // 添加新品顺序字段
            if (!Schema::hasColumn('products', 'new_order')) {
                $table->integer('new_order')->nullable()->comment('新品展示顺序');
            }
            
            // 确保Featured Order字段存在
            if (!Schema::hasColumn('products', 'featured_order')) {
                $table->integer('featured_order')->nullable()->comment('特色产品展示顺序');
            }
            
            // 确保Sale Order字段存在
            if (!Schema::hasColumn('products', 'sale_order')) {
                $table->integer('sale_order')->nullable()->comment('促销产品展示顺序');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'new_image_index')) {
                $table->dropColumn('new_image_index');
            }
            
            if (Schema::hasColumn('products', 'new_order')) {
                $table->dropColumn('new_order');
            }
            
            // 不删除其他可能已存在的字段
        });
    }
};
