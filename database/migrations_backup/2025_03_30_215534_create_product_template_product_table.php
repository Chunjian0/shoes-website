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
        Schema::create('product_template_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('parameter_group');  // 存储格式为 "group=value"
            $table->timestamps();
            
            // 确保每个参数组只能链接到一个产品
            $table->unique(['product_template_id', 'parameter_group'], 'pt_param_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_template_product');
    }
};
