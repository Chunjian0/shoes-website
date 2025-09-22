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
        Schema::create('purchase_supplier_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index(['purchase_id', 'supplier_id']);
        });

        // Revise purchase_items Table, add supplier_id Fields
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('supplier_id')->after('product_id')->constrained()->onDelete('restrict');
            $table->index(['purchase_id', 'supplier_id', 'product_id']);
        });

        // Remove purchases The table supplier_id Fields
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // recover purchases Table of supplier_id Fields
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('supplier_id')->after('id')->constrained()->onDelete('restrict');
        });

        // recoverpurchase_itemsThe original structure of the table
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropIndex(['purchase_id', 'supplier_id', 'product_id']);
            $table->dropColumn('supplier_id');
        });

        Schema::dropIfExists('purchase_supplier_items');
    }
}; 