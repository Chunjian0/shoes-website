<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->comment('merchandiseID');
            $table->integer('quantity')->comment('quantity');
            $table->string('type')->comment('type:in-Into the warehouse,out-Out of the warehouse');
            $table->string('source_type')->nullable()->comment('Source Type:purchase-purchase,sale-Sale,loss-Report losses,check-inventory');
            $table->unsignedBigInteger('source_id')->nullable()->comment('sourceID');
            $table->string('batch_number')->nullable()->comment('Batch number');
            $table->date('expiry_date')->nullable()->comment('Validity period');
            $table->string('location')->nullable()->comment('Library location');
            $table->string('status')->default('available')->comment('state:available-Available,locked-locking');
            $table->json('additional_data')->nullable()->comment('Additional data');
            $table->timestamps();
            
            $table->index(['source_type', 'source_id']);
            $table->index(['product_id', 'batch_number']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_records');
    }
}; 