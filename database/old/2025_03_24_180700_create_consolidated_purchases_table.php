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
     * - 2025_03_24_000007_create_purchases_table.php
     * - 2025_03_10_000002_add_auto_generated_to_purchases_table.php
     * - 2025_03_24_000021_modify_purchase_dates.php
     * - 2025_03_24_000012_create_purchase_items_table.php
     * - 2025_03_24_000022_add_received_quantity_to_purchase_items_table.php
     * - 2025_03_24_000015_create_purchase_supplier_items_table.php
     */
    public function up(): void
    {
        // 创建采购表
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->dateTime('purchase_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('adjustment_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('purchase_status')->default('draft');
            $table->string('payment_status')->default('unpaid');
            $table->string('inspection_status')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->boolean('auto_generated')->default(false)->comment('是否由系统自动生成');
            $table->string('generated_by')->nullable()->comment('生成者（系统或用户ID）');
            $table->timestamps();
            $table->softDeletes();

            // 添加索引
            $table->index('purchase_number');
            $table->index('purchase_date');
            $table->index('payment_status');
            $table->index('purchase_status');
            $table->index('inspection_status');
        });

        // 创建采购供应商项目表
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

            // 添加索引
            $table->index(['purchase_id', 'supplier_id']);
        });

        // 创建采购项目表
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->integer('received_quantity')->default(0);
            $table->dateTime('expected_delivery_at')->nullable();
            $table->integer('lead_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // 添加索引
            $table->index(['purchase_id', 'product_id']);
            $table->index(['purchase_id', 'supplier_id', 'product_id']);
            $table->index('expected_delivery_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchase_supplier_items');
        Schema::dropIfExists('purchases');
    }
}; 