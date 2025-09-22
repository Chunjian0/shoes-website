<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->comment('Salesperson ID');
            $table->foreignId('store_id')->constrained('warehouses')->comment('Store ID');
            $table->foreignId('prescription_id')->nullable()->constrained();
            
            // Amount related
            $table->decimal('subtotal', 10, 2)->comment('Subtotal');
            $table->decimal('tax_amount', 10, 2)->comment('tax');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Discount amount');
            $table->decimal('total_amount', 10, 2)->comment('lump sum');
            
            // Status Related
            $table->string('status')->default('pending')->comment('Order Status');
            $table->string('payment_status')->default('unpaid')->comment('Payment Status');
            $table->timestamp('paid_at')->nullable()->comment('Payment time');
            $table->string('payment_method')->nullable()->comment('Payment method');
            
            // Additional Information
            $table->text('remarks')->nullable()->comment('Remark');
            $table->json('additional_data')->nullable()->comment('Additional data');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
}; 