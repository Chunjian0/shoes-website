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
        Schema::create('product_template_store', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_template_id');
            $table->unsignedBigInteger('store_id');
            $table->timestamps();
            
            // 添加外键约束
            $table->foreign('product_template_id')
                  ->references('id')
                  ->on('product_templates')
                  ->onDelete('cascade');
                  
            $table->foreign('store_id')
                  ->references('id')
                  ->on('warehouses')
                  ->onDelete('cascade');
                  
            // 添加唯一约束，防止重复关联
            $table->unique(['product_template_id', 'store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_template_store');
    }
};
