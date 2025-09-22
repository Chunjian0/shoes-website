<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 删除旧的库存表，因为系统现在使用stocks表
     */
    public function up(): void
    {
        // 删除inventory表
        Schema::dropIfExists('inventory');
        
        // 删除inventory_items表（如果存在）
        Schema::dropIfExists('inventory_items');
    }

    /**
     * Reverse the migrations.
     * 本迁移不支持回滚，因为我们已决定不再使用这些表
     */
    public function down(): void
    {
        // 不做任何操作，因为我们已决定不再使用这些表
    }
};
