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
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('name');
                $table->string('sku')->unique();
                $table->string('barcode')->nullable();
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->decimal('cost_price', 8, 2)->default(0);
                $table->decimal('selling_price', 8, 2)->default(0);
                $table->integer('min_stock')->default(5);
                $table->text('description')->nullable();
                $table->json('images')->nullable();
                $table->json('parameters')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('state')->default('active');
                
                // 首页管理相关字段
                $table->boolean('is_featured')->default(false);
                $table->integer('featured_order')->nullable();
                $table->integer('featured_image_index')->default(0);
                
                $table->boolean('is_new_arrival')->default(false);
                $table->boolean('show_in_new_arrivals')->default(false);
                $table->integer('new_arrival_image_index')->default(0);
                
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->timestamp('discount_start_date')->nullable();
                $table->timestamp('discount_end_date')->nullable();
                $table->integer('min_quantity_for_discount')->nullable();
                $table->integer('max_quantity_for_discount')->nullable();
                
                $table->boolean('show_in_sale')->default(false);
                $table->integer('sale_image_index')->default(0);
                
                $table->json('additional_images')->nullable();
                $table->integer('default_image_index')->default(0);
                
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('set null');
            });
        } else {
            // 如果表已存在，检查并添加必要字段
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('state');
                }
                
                if (!Schema::hasColumn('products', 'featured_order')) {
                    $table->integer('featured_order')->nullable()->after('is_featured');
                }
                
                if (!Schema::hasColumn('products', 'featured_image_index')) {
                    $table->integer('featured_image_index')->default(0)->after('featured_order');
                }
                
                if (!Schema::hasColumn('products', 'is_new_arrival')) {
                    $table->boolean('is_new_arrival')->default(false)->after('featured_image_index');
                }
                
                if (!Schema::hasColumn('products', 'show_in_new_arrivals')) {
                    $table->boolean('show_in_new_arrivals')->default(false)->after('is_new_arrival');
                }
                
                if (!Schema::hasColumn('products', 'new_arrival_image_index')) {
                    $table->integer('new_arrival_image_index')->default(0)->after('show_in_new_arrivals');
                }
                
                if (!Schema::hasColumn('products', 'discount_percentage')) {
                    $table->decimal('discount_percentage', 5, 2)->default(0)->after('new_arrival_image_index');
                }
                
                if (!Schema::hasColumn('products', 'discount_start_date')) {
                    $table->timestamp('discount_start_date')->nullable()->after('discount_percentage');
                }
                
                if (!Schema::hasColumn('products', 'discount_end_date')) {
                    $table->timestamp('discount_end_date')->nullable()->after('discount_start_date');
                }
                
                if (!Schema::hasColumn('products', 'min_quantity_for_discount')) {
                    $table->integer('min_quantity_for_discount')->nullable()->after('discount_end_date');
                }
                
                if (!Schema::hasColumn('products', 'max_quantity_for_discount')) {
                    $table->integer('max_quantity_for_discount')->nullable()->after('min_quantity_for_discount');
                }
                
                if (!Schema::hasColumn('products', 'show_in_sale')) {
                    $table->boolean('show_in_sale')->default(false)->after('max_quantity_for_discount');
                }
                
                if (!Schema::hasColumn('products', 'sale_image_index')) {
                    $table->integer('sale_image_index')->default(0)->after('show_in_sale');
                }
                
                if (!Schema::hasColumn('products', 'additional_images')) {
                    $table->json('additional_images')->nullable()->after('sale_image_index');
                }
                
                if (!Schema::hasColumn('products', 'default_image_index')) {
                    $table->integer('default_image_index')->default(0)->after('additional_images');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不进行任何操作，避免删除重要数据
    }
};
