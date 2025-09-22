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
            if (!Schema::hasColumn('products', 'featured_order')) {
                $table->integer('featured_order')->nullable()->default(null)->after('is_featured');
            }
            
            if (!Schema::hasColumn('products', 'new_arrival_order')) {
                $table->integer('new_arrival_order')->nullable()->default(null)->after('is_new_arrival');
            }
            
            if (!Schema::hasColumn('products', 'sale_order')) {
                $table->integer('sale_order')->nullable()->default(null)->after('is_sale');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'featured_order')) {
                $table->dropColumn('featured_order');
            }
            
            if (Schema::hasColumn('products', 'new_arrival_order')) {
                $table->dropColumn('new_arrival_order');
            }
            
            if (Schema::hasColumn('products', 'sale_order')) {
                $table->dropColumn('sale_order');
            }
        });
    }
};
