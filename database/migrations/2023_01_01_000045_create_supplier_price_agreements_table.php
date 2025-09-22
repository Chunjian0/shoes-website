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
        Schema::create('supplier_price_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('min_quantity')->default(1);
            $table->decimal('discount_rate', 5, 2)->nullable();
            $table->enum('discount_type', ['fixed_price', 'discount_rate']);
            $table->text('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('cascade');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_price_agreements');
    }
}; 