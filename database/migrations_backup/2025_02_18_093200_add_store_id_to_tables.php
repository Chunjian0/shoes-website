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
        // Add store_id to customers table
        if (!Schema::hasColumn('customers', 'store_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->constrained('warehouses')
                    ->where('is_store', true)
                    ->nullOnDelete();
            });
        }

        // Add store_id to sales table
        if (!Schema::hasColumn('sales', 'store_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->constrained('warehouses')
                    ->where('is_store', true)
                    ->nullOnDelete();
            });
        }

        //Add store_id to the prescriptions table
        if (!Schema::hasColumn('prescriptions', 'store_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->constrained('warehouses')
                    ->where('is_store', true)
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Store ID
        if (Schema::hasColumn('customers', 'store_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }

        if (Schema::hasColumn('sales', 'store_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }

        if (Schema::hasColumn('prescriptions', 'store_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }
    }
};
