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
            // 添加模板ID外键
            $table->foreignId('template_id')->nullable()->after('discount_percentage')
                ->constrained('product_templates')->onDelete('set null');
            
            // 添加变体选项（存储为JSON）
            $table->json('variant_options')->nullable()->after('template_id');
            
            // 添加价格调整字段（相对于模板基础价格）
            $table->decimal('price_adjustment', 10, 2)->default(0)->after('variant_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'variant_options', 'price_adjustment']);
        });
    }
};
