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
            $table->unsignedBigInteger('product_template_id');
            $table->unsignedBigInteger('product_id');
            $table->string('parameter_group');
            $table->timestamps();
            
            $table->unique(['product_template_id', 'parameter_group'], 'pt_param_group_unique');
            
            $table->foreign('product_template_id')
                ->references('id')
                ->on('product_templates')
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
        Schema::dropIfExists('product_template_product');
    }
}; 