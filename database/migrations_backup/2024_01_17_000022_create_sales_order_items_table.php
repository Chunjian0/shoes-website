<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name')->comment('Product Name');
            $table->string('product_sku')->comment('merchandiseSKU');
            $table->integer('quantity')->comment('quantity');
            $table->decimal('unit_price', 10, 2)->comment('unit price');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Discount amount');
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('tax rate');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('tax');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal');
            $table->json('specifications')->nullable()->comment('Specification information');
            $table->text('remarks')->nullable()->comment('Remark');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
}; 