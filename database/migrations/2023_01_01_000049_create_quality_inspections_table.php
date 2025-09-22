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
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('inspector_id');
            $table->string('inspection_number')->unique();
            $table->date('inspection_date');
            $table->enum('status', ['pending', 'passed', 'failed'])->default('pending');
            $table->boolean('is_partial')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('inspection_number');
            $table->index('inspection_date');
            $table->index('status');

            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict');
            $table->foreign('inspector_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
    }
}; 