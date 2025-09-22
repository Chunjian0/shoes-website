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
            // 仅在列不存在时添加
            if (!Schema::hasColumn('products', 'featured_image_index')) {
                // 特色图片索引
                $table->integer('featured_image_index')->default(0)->comment('首页展示时使用的图片索引');
            }
            
            if (!Schema::hasColumn('products', 'is_new_arrival')) {
                // 新品展示相关
                $table->boolean('is_new_arrival')->default(false)->comment('是否手动设为新品');
            }
            
            if (!Schema::hasColumn('products', 'show_in_new_arrivals')) {
                $table->boolean('show_in_new_arrivals')->default(true)->comment('是否在新品区域显示');
            }
            
            if (!Schema::hasColumn('products', 'discount_percentage')) {
                // 折扣相关
                $table->decimal('discount_percentage', 5, 2)->default(0)->comment('折扣百分比，如10表示打9折');
            }
            
            if (!Schema::hasColumn('products', 'discount_start_date')) {
                $table->timestamp('discount_start_date')->nullable()->comment('折扣开始日期');
            }
            
            if (!Schema::hasColumn('products', 'discount_end_date')) {
                $table->timestamp('discount_end_date')->nullable()->comment('折扣结束日期');
            }
            
            if (!Schema::hasColumn('products', 'min_quantity_for_discount')) {
                $table->integer('min_quantity_for_discount')->default(0)->comment('享受折扣的最小数量');
            }
            
            if (!Schema::hasColumn('products', 'max_quantity_for_discount')) {
                $table->integer('max_quantity_for_discount')->nullable()->comment('享受折扣的最大数量');
            }
            
            if (!Schema::hasColumn('products', 'is_sale')) {
                // 促销产品展示相关
                $table->boolean('is_sale')->default(false)->comment('是否在促销区域显示');
            }
            
            if (!Schema::hasColumn('products', 'sale_image_index')) {
                $table->integer('sale_image_index')->default(0)->comment('促销区域展示时使用的图片索引');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不执行删除操作
        // 因为我们使用了条件检查，所以down方法不需要具体操作
    }
};
