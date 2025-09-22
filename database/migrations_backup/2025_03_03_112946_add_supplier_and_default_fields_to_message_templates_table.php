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
        Schema::table('message_templates', function (Blueprint $table) {
            // Add supplier_id field (nullable for global templates)
            $table->foreignId('supplier_id')->nullable()->after('type')
                  ->constrained('suppliers')->nullOnDelete();
                  
            // Add is_default field to mark default templates
            $table->boolean('is_default')->default(false)->after('status');
            
            // Add a compound index for finding templates by name and supplier
            $table->index(['name', 'supplier_id']);
            $table->index(['is_default', 'name', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropIndex(['name', 'supplier_id']);
            $table->dropIndex(['is_default', 'name', 'channel']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'is_default']);
        });
    }
};
