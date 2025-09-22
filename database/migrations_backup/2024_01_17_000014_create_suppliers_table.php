<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Supplier number');
            $table->string('name')->comment('Supplier name');
            $table->string('contact_person')->nullable()->comment('Contact');
            $table->string('phone')->nullable()->comment('Contact number');
            $table->string('email')->nullable()->comment('Email');
            $table->string('address')->nullable()->comment('address');
            $table->decimal('credit_limit', 10, 2)->nullable()->default(0)->comment('Credit limit');
            $table->integer('payment_term')->nullable()->default(30)->comment('Accounting period(day)');
            $table->text('remarks')->nullable()->comment('Remark');
            $table->boolean('is_active')->default(true)->comment('Whether to enable');
            $table->timestamps();
            $table->softDeletes();
        });

        // Supplier Contact Form
        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('Contact name');
            $table->string('position')->nullable()->comment('Position');
            $table->string('phone')->nullable()->comment('Contact number');
            $table->string('email')->nullable()->comment('Email');
            $table->boolean('is_primary')->default(false)->comment('Is the main contact person');
            $table->text('remarks')->nullable()->comment('Remark');
            $table->timestamps();
            $table->softDeletes();
        });

        // Supplier Product List
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('supplier_product_code')->nullable()->comment('Supplier product number');
            $table->decimal('purchase_price', 10, 2)->comment('Purchase price');
            $table->integer('min_order_quantity')->default(1)->comment('Minimum order quantity');
            $table->integer('lead_time')->default(7)->comment('Delivery cycle(day)');
            $table->boolean('is_preferred')->default(false)->comment('Is the preferred supplier?');
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('tax rate');
            $table->text('remarks')->nullable()->comment('Remark');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['supplier_id', 'product_id']);
        });

        // Supplier price agreement list
        Schema::create('supplier_price_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable()->comment('Fixed price');
            $table->date('start_date')->comment('Effective Date');
            $table->date('end_date')->nullable()->comment('Expiration date');
            $table->integer('min_quantity')->default(1)->comment('Minimum quantity');
            $table->decimal('discount_rate', 5, 2)->nullable()->comment('Discount rate');
            $table->enum('discount_type', ['fixed_price', 'discount_rate'])->comment('Discount type: fixed price/Discount rate');
            $table->text('terms')->nullable()->comment('Terms of the Agreement');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_price_agreements');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('suppliers');
    }
}; 