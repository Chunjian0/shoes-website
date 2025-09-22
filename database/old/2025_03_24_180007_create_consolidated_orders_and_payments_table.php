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
     * - 2024_01_17_000021_create_sales_orders_table.php
     * - 2024_01_17_000022_create_sales_order_items_table.php
     * - 2024_01_17_000023_create_payments_table.php
     * - 2024_01_17_000024_create_invoices_table.php
     * - 2024_01_20_000001_create_return_requests_table.php
     * - 2024_01_20_000002_create_return_items_table.php
     * - 2025_03_16_191120_add_store_id_to_sales_orders_table.php
     */
    public function up(): void
    {
        // 创建订单表
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('status');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('shipping_method')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city', 50)->nullable();
            $table->string('shipping_state', 50)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 50)->nullable();
            $table->string('shipping_phone', 20)->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city', 50)->nullable();
            $table->string('billing_state', 50)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_country', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建订单项目表
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
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

        // 创建支付表
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建发票表
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建退货请求表
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('requested_action')->default('refund');
            $table->decimal('total_amount', 10, 2);
            $table->date('request_date');
            $table->date('processed_date')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建退货项目表
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained('return_requests')->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->constrained('sales_order_items')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->string('condition');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
}; 