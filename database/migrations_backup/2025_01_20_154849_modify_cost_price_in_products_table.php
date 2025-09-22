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
        // First, add the existing one NULL The value is updated to 0
        DB::table('products')->whereNull('cost_price')->update(['cost_price' => 0]);

        // Modify column definitions
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Put it first NULL The value is updated to 0
        DB::table('products')->whereNull('cost_price')->update(['cost_price' => 0]);

        // Recover column definition
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable(false)->default(0)->change();
        });
    }
};
