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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2);
            $table->integer('min_stock')->default(0);
            $table->integer('inventory_count')->default(0);
            $table->text('description')->nullable();
            $table->longText('images')->nullable();
            $table->longText('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('deleted_at')->nullable();
            $table->integer('featured_image_index')->default(0);
            $table->boolean('is_new_arrival')->default(false);
            $table->integer('new_arrival_order')->nullable();
            $table->date('new_until_date')->nullable();
            $table->boolean('show_in_new_arrivals')->default(true);
            $table->integer('new_arrival_image_index')->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->unsignedBigInteger('template_id')->nullable();
            $table->longText('variant_options')->nullable();
            $table->decimal('price_adjustment', 10, 2)->default(0);
            $table->timestamp('discount_start_date')->nullable();
            $table->timestamp('discount_end_date')->nullable();
            $table->integer('min_quantity_for_discount')->default(0);
            $table->integer('max_quantity_for_discount')->nullable();
            $table->boolean('show_in_sale')->default(false);
            $table->boolean('is_sale')->default(false);
            $table->integer('sale_image_index')->default(0);
            $table->text('additional_images')->nullable();
            $table->integer('default_image_index')->unsigned()->nullable()->default(0);
            $table->integer('new_image_index')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->integer('new_order')->nullable();
            $table->integer('featured_order')->nullable();
            $table->integer('sale_order')->nullable();
            $table->timestamp('sale_until_date')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('restrict');
            $table->foreign('template_id')->references('id')->on('product_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}; 