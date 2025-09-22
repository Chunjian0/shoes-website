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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('channel');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('content');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->unique(['name', 'channel']);
            $table->index(['channel', 'type']);
            $table->index('status');
            $table->index(['name', 'supplier_id']);
            $table->index(['is_default', 'name', 'channel']);
            
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
}; 