<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageTemplateSeeder extends Seeder
{
    /**
     * Run the message template database seeds.
     */
    public function run(): void
    {
        try {
            // 1. Email Verification Code Template
            MessageTemplate::updateOrCreate(
                ['name' => 'email_verification_code', 'channel' => 'email'],
                [
                    'description' => 'Email template for sending verification codes.',
                    'type' => 'email',
                    'subject' => 'Your Verification Code',
                    'content' => $this->getVerificationCodeTemplate(),
                    'status' => 'active',
                    'is_default' => true,
                ]
            );
            Log::info('Email verification code template created or updated.');
            
            // 2. Order Confirmation Email Template
            MessageTemplate::updateOrCreate(
                ['name' => 'order_confirmation', 'channel' => 'email'],
                [
                    'description' => 'Email template for order confirmation.',
                    'type' => 'email',
                    'subject' => 'Order Confirmation - #{order_number}',
                    'content' => $this->getOrderConfirmationTemplate(),
                    'status' => 'active',
                    'is_default' => true,
                ]
            );
            Log::info('Order confirmation email template created or updated.');
            
            // Add other default templates here if needed...
            
        } catch (\Exception $e) {
            Log::error('Message template seeding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Get the HTML content for the verification code email template (English & Beautified).
     */
    private function getVerificationCodeTemplate(): string
    {
        // Inline CSS for better email client compatibility
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Your Verification Code</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f7; }
        .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .email-header { background-color: #4a6cf7; color: #ffffff; padding: 25px; text-align: center; border-radius: 8px 8px 0 0; }
        .email-header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .email-content { padding: 30px 40px; }
        .email-content p { margin: 0 0 15px 0; font-size: 16px; }
        .verification-code { font-size: 36px; font-weight: bold; color: #4a6cf7; text-align: center; letter-spacing: 6px; background-color: #f5f7fa; padding: 20px; border-radius: 5px; margin: 30px 0; border: 1px dashed #cccccc; }
        .email-footer { border-top: 1px solid #e0e0e0; padding: 20px 40px; font-size: 12px; color: #888888; text-align: center; }
        .email-footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Verification Code</h1>
        </div>
        
        <div class="email-content">
            <p>Dear User,</p>
            
            <p>Your verification code is:</p>
            
            <div class="verification-code">{verification_code}</div>
            
            <p>This code is valid for {expires_in_minutes} minutes. Please complete the verification process soon.</p>
            
            <p>If you did not request this code, please ignore this email.</p>
        </div>
        
        <div class="email-footer">
            <p>This is an automated message, please do not reply directly.</p>
            <p>&copy; {{ date('Y') }} {app_name}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get the HTML content for the order confirmation email template (English & Beautified).
     */
    private function getOrderConfirmationTemplate(): string
    {
        // Inline CSS for better email client compatibility
        // Fully translated to English
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f7; }
        .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .email-header { background-color: #4a6cf7; color: #ffffff; padding: 25px; text-align: center; border-radius: 8px 8px 0 0; }
        .email-header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .email-content { padding: 30px 40px; }
        .email-content p { margin: 0 0 15px 0; font-size: 16px; }
        .order-info { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 25px; border: 1px solid #eeeeee; }
        .order-info p { margin: 8px 0; font-size: 14px; }
        .order-info strong { color: #555555; min-width: 150px; display: inline-block;}
        .items-table { width: 100%; border-collapse: collapse; margin: 25px 0; }
        .items-table th, .items-table td { padding: 12px 15px; border: 1px solid #e0e0e0; text-align: left; font-size: 14px; }
        .items-table th { background-color: #f5f7fa; font-weight: 600; color: #333333; }
        .items-table td.align-right { text-align: right; }
        .items-table td.align-center { text-align: center; }
        .items-table tfoot td { font-weight: bold; background-color: #f5f7fa; }
        .order-summary { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: right; }
        .order-summary p { margin: 8px 0; font-size: 15px; }
        .order-summary strong { color: #333333; min-width: 120px; display: inline-block; text-align: left;}
        .order-summary span { display: inline-block; min-width: 100px; text-align: right; font-weight: bold;}
        .button-container { text-align: center; margin-top: 30px; }
        .button { display: inline-block; background-color: #4a6cf7; color: #ffffff !important; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; font-size: 16px; border: none; cursor: pointer; }
        .email-footer { border-top: 1px solid #e0e0e0; padding: 20px 40px; font-size: 12px; color: #888888; text-align: center; }
        .email-footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Order Confirmation</h1>
        </div>
        
        <div class="email-content">
            <p>Dear {customer_name},</p>
            
            <p>Thank you for your order! Your order has been confirmed. Here are the details:</p>
            
            <div class="order-info">
                <p><strong>Order Number:</strong> {order_number}</p>
                <p><strong>Order Date:</strong> {order_date}</p>
                <p><strong>Order Status:</strong> {order_status}</p>
                <p><strong>Payment Method:</strong> {payment_method}</p>
                <p><strong>Shipping Address:</strong> {shipping_address}</p>
                <p><strong>Estimated Delivery:</strong> {estimated_delivery_date}</p>
            </div>
            
            <h3>Your Order Items:</h3>
            
            <!-- Order Items Table is inserted here by the service -->
            {order_items_table} 
            
            <div class="order-summary">
                <p><strong>Subtotal:</strong> <span>{subtotal}</span></p>
                <p><strong>Shipping Fee:</strong> <span>{shipping_fee}</span></p>
                <p><strong>Tax:</strong> <span>{tax}</span></p>
                <p><strong>Discount:</strong> <span>{discount}</span></p>
                <p style="font-size: 18px; font-weight: bold; margin-top: 15px;"><strong>Total Amount:</strong> <span>{total_amount}</span></p>
            </div>
            
            <div class="button-container">
                <a href="{order_tracking_url}" class="button" style="color: #ffffff;">View Order Details</a>
            </div>
            
            <p style="margin-top: 30px;">If you have any questions, feel free to contact us.</p>
            
            <p>Thank you for shopping with us!</p>
        </div>
            
        <div class="email-footer">
            <p>This is an automated message, please do not reply directly.</p>
            <p>&copy; {app_year} {app_name}. All rights reserved.</p> 
            <!-- Optional: Add company address or contact info here -->
        </div>
    </div>
</body>
</html>
HTML;
    }
} 