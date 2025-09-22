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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->json('specifications')->nullable();
            $table->timestamps();
            
            // A product can only be in a cart once (without considering specifications)
            // We can't include JSON in a unique constraint
            $table->unique(['cart_id', 'product_id'], 'cart_product');
        });

        // Add foreign key constraints if related tables exist
        if (Schema::hasTable('carts')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
}; 