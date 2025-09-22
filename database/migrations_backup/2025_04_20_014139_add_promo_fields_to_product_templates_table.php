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
            $table->string('promo_page_url')->nullable()->after('is_sale');
            $table->json('template_gallery')->nullable()->after('promo_page_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            $table->dropColumn('promo_page_url');
            $table->dropColumn('template_gallery');
        });
    }
};
