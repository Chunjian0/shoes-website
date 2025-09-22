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
        Schema::table('sales_orders', function (Blueprint $table) {
            // Add new unified fields
            $table->string('contact_name')->nullable()->after('user_id'); // 或者选择一个更合适的位置
            $table->string('contact_phone')->nullable()->after('contact_name');
            $table->text('address_details')->nullable()->after('contact_phone');

            // Add other previously planned fields if they weren't already added
            $table->timestamp('order_date')->nullable()->after('order_number');
            $table->string('shipping_method')->nullable()->after('payment_method');
            $table->decimal('shipping_cost', 10, 2)->default(0.00)->after('discount_amount');
            $table->string('shipping_status')->default('pending')->after('status'); // Track shipment
            $table->date('estimated_arrival_date')->nullable()->after('shipping_status'); // Add estimated arrival date

            // Remove old separate fields (ensure they actually exist before trying to drop)
            if (Schema::hasColumn('sales_orders', 'shipping_contact_name')) {
                $table->dropColumn('shipping_contact_name');
            }
            if (Schema::hasColumn('sales_orders', 'shipping_contact_phone')) {
                $table->dropColumn('shipping_contact_phone');
            }
            if (Schema::hasColumn('sales_orders', 'shipping_address_details')) {
                $table->dropColumn('shipping_address_details');
            }
            if (Schema::hasColumn('sales_orders', 'billing_contact_name')) {
                $table->dropColumn('billing_contact_name');
            }
             if (Schema::hasColumn('sales_orders', 'billing_contact_phone')) {
                $table->dropColumn('billing_contact_phone');
            }
            if (Schema::hasColumn('sales_orders', 'billing_address_details')) {
                $table->dropColumn('billing_address_details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
             // Add back the old fields (reverse of dropping) - Only if they existed before
            if (!Schema::hasColumn('sales_orders', 'shipping_contact_name')) {
                $table->string('shipping_contact_name')->nullable(); // Adjust position if needed
            }
            if (!Schema::hasColumn('sales_orders', 'shipping_contact_phone')) {
                 $table->string('shipping_contact_phone')->nullable();
            }
             if (!Schema::hasColumn('sales_orders', 'shipping_address_details')) {
                $table->text('shipping_address_details')->nullable();
            }
             if (!Schema::hasColumn('sales_orders', 'billing_contact_name')) {
                $table->string('billing_contact_name')->nullable();
            }
            if (!Schema::hasColumn('sales_orders', 'billing_contact_phone')) {
                $table->string('billing_contact_phone')->nullable();
            }
            if (!Schema::hasColumn('sales_orders', 'billing_address_details')) {
                $table->text('billing_address_details')->nullable();
            }

            // Drop the new unified fields and others added in this migration
            $table->dropColumn([
                'contact_name',
                'contact_phone',
                'address_details',
                'order_date',
                'shipping_method',
                'shipping_cost',
                'shipping_status',
                'estimated_arrival_date',
            ]);
        });
    }
};