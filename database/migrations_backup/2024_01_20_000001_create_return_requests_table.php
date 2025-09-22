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
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('store_id')->constrained('warehouses')->where('is_store', true);
            $table->foreignId('user_id')->constrained(); // 创建退货申请的用户
            $table->text('reason');
            $table->string('status'); // pending, approved, rejected
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->foreignId('processed_by')->nullable()->constrained('users'); // 处理退货申请的用户
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
}; 