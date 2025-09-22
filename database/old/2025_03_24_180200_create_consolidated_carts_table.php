<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Consolidated from the following migrations:
     * - 2023_10_15_000001_create_carts_table.php
     * - 2023_10_15_000002_create_cart_items_table.php
     * - 2025_03_16_205653_add_customer_id_to_carts_table.php
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();
            
            // A cart belongs to either a user or a session
            $table->index(['user_id', 'session_id']);
        });

        // Add foreign key constraint if warehouses table exists
        if (Schema::hasTable('warehouses')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->foreign('store_id')->references('id')->on('warehouses')->onDelete('cascade');
            });
        }

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
        Schema::dropIfExists('carts');
    }
}; 