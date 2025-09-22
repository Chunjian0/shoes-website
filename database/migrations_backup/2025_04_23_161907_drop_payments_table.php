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
        // 删除 payments 表（如果存在）
        Schema::dropIfExists('payments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 为了能够回滚，这里可以重新创建 payments 表
        // 你可以从旧的 create_payments_table 迁移文件中复制创建逻辑
        // 如果找不到旧文件或不需要精确回滚，可以留空或只添加一个基础结构
        if (!Schema::hasTable('payments')) {
             Schema::create('payments', function (Blueprint $table) {
                 $table->id();
                 // 添加你之前 payments 表中重要的列定义...
                 // 例如:
                 // $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
                 // $table->decimal('amount', 10, 2);
                 // $table->string('payment_method')->nullable();
                 // $table->string('status')->default('pending');
                 // $table->timestamp('paid_at')->nullable();
                 $table->timestamps();
             });
             // 提醒：上面的列定义只是示例，请根据你之前的结构调整
        }
    }
}; 