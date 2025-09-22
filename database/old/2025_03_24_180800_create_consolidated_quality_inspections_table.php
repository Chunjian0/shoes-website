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
     * - 2025_03_24_000013_create_quality_inspections_table.php
     * - 2025_03_24_000014_create_quality_inspection_items_table.php
     * - 2025_03_24_000014_add_is_partial_to_quality_inspections_table.php
     */
    public function up(): void
    {
        // 创建质量检查表
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->restrictOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->restrictOnDelete();
            $table->string('inspection_number')->unique();
            $table->date('inspection_date');
            $table->enum('status', ['pending', 'passed', 'failed'])->default('pending');
            $table->boolean('is_partial')->default(false)->comment('Is it a partial test');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('inspection_number');
            $table->index('inspection_date');
            $table->index('status');
        });

        // 创建质量检查项目表
        Schema::create('quality_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_inspection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->constrained('purchase_items')->restrictOnDelete();
            $table->decimal('inspected_quantity', 10, 2);
            $table->decimal('passed_quantity', 10, 2);
            $table->decimal('failed_quantity', 10, 2);
            $table->text('defect_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['quality_inspection_id', 'purchase_item_id'], 'qi_items_inspection_item_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspection_items');
        Schema::dropIfExists('quality_inspections');
    }
}; 