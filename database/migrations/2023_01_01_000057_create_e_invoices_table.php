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
        Schema::create('e_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('user_id');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('draft');
            $table->string('submission_id')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('pdf_path')->nullable();
            $table->longText('xml_data')->nullable();
            $table->longText('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('submission_count')->default(0);
            $table->timestamp('last_submitted_at')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->string('currency')->default('MYR');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->foreign('store_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_invoices');
    }
}; 