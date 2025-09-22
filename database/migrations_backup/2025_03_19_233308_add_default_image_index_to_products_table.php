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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'additional_images')) {
                $table->text('additional_images')->nullable();
            }
            
            if (!Schema::hasColumn('products', 'default_image_index')) {
                $table->unsignedInteger('default_image_index')->nullable()->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不执行删除操作
    }
};
