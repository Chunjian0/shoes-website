<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory Table
        Schema::create('inventory_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_number')->unique(); // Inventory order numbers
            $table->date('check_date'); // Check out dates
            $table->foreignId('user_id')->constrained(); // Inventory personnel
            $table->string('status')->default('pending'); // state:pending, completed, cancelled
            $table->text('notes')->nullable(); // Remark
            $table->timestamps();
            $table->softDeletes();
        });

        // Inventory inventory details
        Schema::create('inventory_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_check_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('system_count'); // System inventory quantity
            $table->integer('actual_count'); // Actual inventory quantity
            $table->integer('difference'); // Number of differences
            $table->text('notes')->nullable(); // Remark
            $table->timestamps();
        });

        // Inventory Loss Statement
        Schema::create('inventory_losses', function (Blueprint $table) {
            $table->id();
            $table->string('loss_number')->unique(); // Loss order number
            $table->date('loss_date'); // Loss date
            $table->foreignId('user_id')->constrained(); // Report damage
            $table->string('status')->default('pending'); // state:pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Approver
            $table->timestamp('approved_at')->nullable(); // Approval time
            $table->text('reason'); // Reason for reporting damage
            $table->text('notes')->nullable(); // Remark
            $table->timestamps();
            $table->softDeletes();
        });

        // Stock loss report details
        Schema::create('inventory_loss_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_loss_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity'); // Losses reported
            $table->decimal('cost_price', 10, 2); // Cost price
            $table->decimal('total_amount', 10, 2); // Report loss amount
            $table->text('notes')->nullable(); // Remark
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_loss_items');
        Schema::dropIfExists('inventory_losses');
        Schema::dropIfExists('inventory_check_items');
        Schema::dropIfExists('inventory_checks');
    }
}; 