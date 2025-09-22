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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('name')->default('Default Cart');
            $table->string('type')->default('cart');
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();
            
            $table->index('customer_id');
            $table->index('type');
            $table->unique(['customer_id', 'is_default'], 'customer_default_cart_unique');
            
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
                
            $table->foreign('store_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
}; 