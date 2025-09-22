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
     * - 2025_03_22_140917_create_homepage_sections_table.php
     * - 2025_03_22_195544_create_homepage_section_items_table.php
     * - 2025_03_22_141117_create_homepage_section_product_table.php
     * - 2025_03_22_141123_create_homepage_section_category_table.php
     * - 2025_03_23_100544_create_homepage_products_table.php
     */
    public function up(): void
    {
        // 创建首页分区表
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // 'hero', 'featured_products', 'category_showcase', 'sale_products', 'banner', 'text', 'custom'
            $table->string('layout')->default('full-width'); // 'full-width', 'contained', 'boxed', 'side-by-side', 'grid-2', 'grid-3', 'grid-4'
            $table->json('content')->nullable(); // 存储各种类型区域的特定内容
            $table->integer('position')->default(0); // 排序位置
            $table->string('background_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('padding')->nullable();
            $table->string('custom_class')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 创建首页分区项目表
        Schema::create('homepage_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('homepage_sections')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('link')->nullable();
            $table->string('button_text')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 创建首页分区-产品关联表
        Schema::create('homepage_section_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('homepage_sections')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();
            
            $table->unique(['section_id', 'product_id']);
        });

        // 创建首页分区-分类关联表
        Schema::create('homepage_section_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('homepage_sections')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
            
            $table->unique(['section_id', 'category_id']);
        });

        // 创建首页产品表（特色、新品、促销等）
        Schema::create('homepage_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('section', ['featured', 'new_arrival', 'sale'])->index();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // 添加唯一索引确保每个产品在每个区域只出现一次
            $table->unique(['product_id', 'section']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_products');
        Schema::dropIfExists('homepage_section_category');
        Schema::dropIfExists('homepage_section_product');
        Schema::dropIfExists('homepage_section_items');
        Schema::dropIfExists('homepage_sections');
    }
}; 