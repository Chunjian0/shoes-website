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
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('is_new_arrival')->default(false)->after('is_featured');
            $table->boolean('is_sale')->default(false)->after('is_new_arrival');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_new_arrival', 'is_sale']);
        });
    }
};
