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
        // Revise purchases surface
        Schema::table('purchases', function (Blueprint $table) {
            // Will purchase_date Change to datetime
            $table->dateTime('purchase_date')->change();
            // Remove expected_delivery_date
            $table->dropColumn('expected_delivery_date');
        });

        // Revise purchase_items surface
        Schema::table('purchase_items', function (Blueprint $table) {
            // Added estimated delivery time and delivery cycle
            $table->dateTime('expected_delivery_at')->nullable()->after('total_amount');
            $table->integer('lead_time')->nullable()->after('expected_delivery_at');
            
            // Add an index
            $table->index('expected_delivery_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // recover purchases surface
        Schema::table('purchases', function (Blueprint $table) {
            // recover purchase_date for date
            $table->date('purchase_date')->change();
            // recover expected_delivery_date
            $table->date('expected_delivery_date')->nullable()->after('purchase_date');
        });

        // recover purchase_items surface
        Schema::table('purchase_items', function (Blueprint $table) {
            // Remove added fields
            $table->dropColumn(['expected_delivery_at', 'lead_time']);
        });
    }
}; 