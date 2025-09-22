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
            // 移除不必要的字段
            $table->dropColumn('base_price');
            $table->dropColumn('brand');
            $table->dropColumn('model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            // 恢复已移除的字段
            $table->string('brand')->nullable()->after('description');
            $table->string('model')->nullable()->after('brand');
            $table->decimal('base_price', 10, 2)->nullable()->after('model');
        });
    }
};
