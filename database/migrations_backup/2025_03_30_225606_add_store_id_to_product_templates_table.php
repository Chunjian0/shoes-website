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
        Schema::table('product_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('category_id');
            $table->foreign('store_id')->references('id')->on('warehouses')->onDelete('set null');
        });
        
        // 不需要迁移数据，因为product_template_store表已不存在
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
