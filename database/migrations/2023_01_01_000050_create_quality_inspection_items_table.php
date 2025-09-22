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
        Schema::create('quality_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quality_inspection_id');
            $table->unsignedBigInteger('purchase_item_id');
            $table->decimal('inspected_quantity', 10, 2);
            $table->decimal('passed_quantity', 10, 2);
            $table->decimal('failed_quantity', 10, 2);
            $table->text('defect_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['quality_inspection_id', 'purchase_item_id'], 'qi_items_inspection_item_index');

            $table->foreign('quality_inspection_id')
                  ->references('id')
                  ->on('quality_inspections')
                  ->onDelete('cascade');

            $table->foreign('purchase_item_id')
                  ->references('id')
                  ->on('purchase_items')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspection_items');
    }
}; 