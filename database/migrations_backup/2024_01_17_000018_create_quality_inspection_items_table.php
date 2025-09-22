<?php

declare(strict_types=1);

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
    }
}; 