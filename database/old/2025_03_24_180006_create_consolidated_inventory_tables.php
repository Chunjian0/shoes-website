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
     * - 2024_01_17_000003_create_warehouses_table.php
     * - 2024_01_17_000012_create_stock_movements_table.php
     * - 2024_01_17_000020_create_inventory_table.php
     * - 2024_01_17_000020_create_inventory_checks_table.php
     * - 2024_01_19_000003_create_inventory_records_table.php
     * - 2025_01_22_071914_create_stock_transfers_table.php
     * - 2025_01_22_071922_create_stock_transfer_items_table.php
     * - 2025_02_18_092434_create_stocks_table.php
     * - 2025_02_18_093200_add_store_id_to_tables.php (部分库存相关)
     */
    public function up(): void
    {
        // 创建仓库表
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('contact_person', 50)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_store')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建货架表
        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建库位表
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->foreignId('shelf_id')->constrained('shelves')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建库存表
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('current_quantity')->default(0);
            $table->integer('min_quantity')->default(0);
            $table->integer('max_quantity')->nullable();
            $table->timestamps();
            
            $table->unique(['product_id', 'warehouse_id']);
        });

        // 创建库存项目表
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();
            
            $table->unique(['product_id', 'location_id']);
        });
        
        // 创建库存调整表
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->date('adjustment_date');
            $table->string('type');
            $table->string('status');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建库存调整项目表
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 创建库存量表
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('allocated_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->timestamps();
            
            // 产品在每个仓库中只能有一条库存记录
            $table->unique(['product_id', 'warehouse_id']);
        });

        // 创建库存变动表
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('reference_number')->nullable();
            $table->string('type');
            $table->integer('quantity');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->morphs('source');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 创建库存盘点表
        Schema::create('inventory_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // 盘点人员
            $table->date('check_date');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建库存盘点项目表
        Schema::create('inventory_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_check_id')->constrained('inventory_checks')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('system_quantity');
            $table->integer('actual_quantity');
            $table->integer('difference_quantity');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 创建库存记录表
        Schema::create('inventory_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('type'); // 入库、出库、调整等
            $table->morphs('recordable'); // 关联到具体的操作记录（采购、销售、调整等）
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 创建库存转移表
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->date('transfer_date');
            $table->string('status');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 创建库存转移项目表
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventory_records');
        Schema::dropIfExists('inventory_check_items');
        Schema::dropIfExists('inventory_checks');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_items');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('shelves');
        Schema::dropIfExists('warehouses');
    }
}; 