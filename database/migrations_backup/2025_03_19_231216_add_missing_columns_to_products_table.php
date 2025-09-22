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
            // 添加新品字段
            if (!Schema::hasColumn('products', 'is_new_arrival')) {
                $table->boolean('is_new_arrival')->default(false)->after('selling_price');
            }
            
            // 添加折扣日期字段
            if (!Schema::hasColumn('products', 'discount_start_date')) {
                $table->timestamp('discount_start_date')->nullable()->after('discount_percentage');
            }
            
            if (!Schema::hasColumn('products', 'discount_end_date')) {
                $table->timestamp('discount_end_date')->nullable()->after('discount_start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_new_arrival',
                'discount_start_date',
                'discount_end_date'
            ]);
        });
    }
};
