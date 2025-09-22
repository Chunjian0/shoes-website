<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Add notification recipient settings
        Setting::create([
            'key' => 'notification_receivers',
            'value' => json_encode([
                'product_created' => [], // New product creation notification recipient
                'product_updated' => [], // Product update notification recipient
                'product_deleted' => [], // Product Deletion Notification Recipient
                'quality_inspection_created' => [], // Quality inspection creation notification recipient
                'quality_inspection_updated' => [], // Quality inspection update notification recipient
                'purchase_created' => [], // Purchase order creation notification recipient
                'purchase_status_changed' => [], // Notify the recipient of the purchase order status change
                'inventory_alert' => [], // Inventory warning notification recipient
                'payment_status_changed' => [], // Notify the recipient of the payment status change
                'system_alert' => [], // System warning notification recipient
            ]),
            'type' => 'json',
            'group' => 'notification',
            'label' => 'Notify the recipient',
            'description' => 'Settings of recipients of various notifications in the system',
            'is_public' => false
        ]);


        // Add email notification settings
        Setting::create([
            'key' => 'email_notification_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'group' => 'notification',
            'label' => 'Enable email notifications',
            'description' => 'Whether to enable email notification function',
            'is_public' => false
        ]);
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'notification_receivers',
            'email_notification_enabled'
        ])->delete();
    }
}; 