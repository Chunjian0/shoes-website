<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('sales_order_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('store_id')->constrained('warehouses');
            $table->foreignId('user_id')->constrained();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('draft');
            $table->string('submission_id')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('xml_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('submission_count')->default(0);
            $table->timestamp('last_submitted_at')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->string('currency')->default('MYR');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_invoices');
    }
}; 