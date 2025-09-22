<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationLog;
use Carbon\Carbon;

class NotificationLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Commented out to prevent generating test data
        /*
        $notificationTypes = [
            'product_created' => 'New Product Created',
            'product_updated' => 'Product Updated',
            'product_deleted' => 'Product Deleted',
            'low_stock' => 'Low Stock Alert',
            'order_placed' => 'New Order Placed',
            'order_shipped' => 'Order Shipped',
            'order_delivered' => 'Order Delivered',
            'payment_received' => 'Payment Received',
            'payment_failed' => 'Payment Failed',
            'customer_registered' => 'New Customer Registered',
        ];

        $statuses = ['sent', 'failed', 'pending'];
        $recipients = [
            'admin@example.com',
            'manager@example.com',
            'staff@example.com',
            'support@example.com',
            'sales@example.com',
        ];

        // Create 50 sample notification logs
        for ($i = 0; $i < 50; $i++) {
            $type = array_rand($notificationTypes);
            $status = $statuses[array_rand($statuses)];
            $recipient = $recipients[array_rand($recipients)];
            $subject = $notificationTypes[$type];
            
            // Create random date within the last 30 days
            $date = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            $content = $this->generateContent($type, $subject);
            $errorMessage = ($status === 'failed') ? 'Failed to send email: SMTP connection error' : null;
            
            NotificationLog::create([
                'type' => $type,
                'recipient' => $recipient,
                'subject' => $subject,
                'content' => $content,
                'status' => $status,
                'error_message' => $errorMessage,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
        */
    }

    /**
     * Generate sample content for notification
     *
     * @param string $type
     * @param string $subject
     * @return string
     */
    private function generateContent($type, $subject)
    {
        $contents = [
            'product_created' => '<p>Hello,</p><p>A new product has been added to the system.</p><p><strong>Product Name:</strong> Sample Product</p><p><strong>SKU:</strong> SKU-12345</p><p><strong>Price:</strong> $99.99</p><p>Please review the product details.</p><p>Thank you,<br>System Administrator</p>',
            
            'product_updated' => '<p>Hello,</p><p>A product has been updated in the system.</p><p><strong>Product Name:</strong> Sample Product</p><p><strong>SKU:</strong> SKU-12345</p><p><strong>Updated Fields:</strong> Price, Description</p><p>Please review the changes.</p><p>Thank you,<br>System Administrator</p>',
            
            'product_deleted' => '<p>Hello,</p><p>A product has been deleted from the system.</p><p><strong>Product Name:</strong> Sample Product</p><p><strong>SKU:</strong> SKU-12345</p><p>If this was done in error, please restore from the archive.</p><p>Thank you,<br>System Administrator</p>',
            
            'low_stock' => '<p>Hello,</p><p>This is an alert for low stock levels.</p><p><strong>Product Name:</strong> Sample Product</p><p><strong>SKU:</strong> SKU-12345</p><p><strong>Current Stock:</strong> 5 units</p><p><strong>Reorder Level:</strong> 10 units</p><p>Please restock this item soon.</p><p>Thank you,<br>System Administrator</p>',
            
            'order_placed' => '<p>Hello,</p><p>A new order has been placed.</p><p><strong>Order Number:</strong> ORD-67890</p><p><strong>Customer:</strong> John Doe</p><p><strong>Total Amount:</strong> $199.99</p><p>Please process this order at your earliest convenience.</p><p>Thank you,<br>System Administrator</p>',
            
            'order_shipped' => '<p>Hello,</p><p>An order has been shipped.</p><p><strong>Order Number:</strong> ORD-67890</p><p><strong>Customer:</strong> John Doe</p><p><strong>Shipping Method:</strong> Express Delivery</p><p><strong>Tracking Number:</strong> TRK-123456789</p><p>Thank you,<br>System Administrator</p>',
            
            'order_delivered' => '<p>Hello,</p><p>An order has been delivered.</p><p><strong>Order Number:</strong> ORD-67890</p><p><strong>Customer:</strong> John Doe</p><p><strong>Delivery Date:</strong> ' . Carbon::now()->subDays(rand(1, 5))->format('Y-m-d') . '</p><p>Thank you,<br>System Administrator</p>',
            
            'payment_received' => '<p>Hello,</p><p>Payment has been received for an order.</p><p><strong>Order Number:</strong> ORD-67890</p><p><strong>Customer:</strong> John Doe</p><p><strong>Amount:</strong> $199.99</p><p><strong>Payment Method:</strong> Credit Card</p><p>Thank you,<br>System Administrator</p>',
            
            'payment_failed' => '<p>Hello,</p><p>A payment has failed for an order.</p><p><strong>Order Number:</strong> ORD-67890</p><p><strong>Customer:</strong> John Doe</p><p><strong>Amount:</strong> $199.99</p><p><strong>Payment Method:</strong> Credit Card</p><p><strong>Error:</strong> Insufficient funds</p><p>Please contact the customer to arrange alternative payment.</p><p>Thank you,<br>System Administrator</p>',
            
            'customer_registered' => '<p>Hello,</p><p>A new customer has registered on the system.</p><p><strong>Name:</strong> John Doe</p><p><strong>Email:</strong> john.doe@example.com</p><p><strong>Registration Date:</strong> ' . Carbon::now()->subDays(rand(0, 7))->format('Y-m-d') . '</p><p>Thank you,<br>System Administrator</p>',
        ];
        
        return $contents[$type] ?? '<p>Hello,</p><p>' . $subject . '</p><p>This is a system notification.</p><p>Thank you,<br>System Administrator</p>';
    }
} 