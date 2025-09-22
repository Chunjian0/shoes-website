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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('Default Cart');
            $table->string('type')->default('cart');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index('customer_id');
            $table->index('type');
        });

        if (Schema::hasTable('warehouses')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unsignedBigInteger('store_id')->nullable()->after('is_default');
                $table->foreign('store_id')->references('id')->on('warehouses')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
}; 