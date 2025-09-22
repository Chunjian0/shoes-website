<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('channel'); // email,
            $table->string('type')->nullable(); // 模板类型，如system, order, inventory等
            $table->string('subject')->nullable(); // 邮件主题
            $table->text('content'); // 模板内容
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->unique(['name', 'channel']);
            $table->index(['channel', 'type']);
            $table->index(['status']);
        });
        
        // 插入默认模板
        $this->insertDefaultTemplates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
    
    /**
     * 插入默认的消息模板
     */
    private function insertDefaultTemplates(): void
    {
        $emailTemplates = [
            [
                'name' => 'purchase_order_generated',
                'description' => 'Notification for automatically generated purchase orders.',
                'channel' => 'email',
                'type' => 'purchase',
                'subject' => '[System Notification] Automatic Purchase Order Generation',
                'content' => $this->getPurchaseOrderGeneratedContent(),
                'status' => 'active',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'inventory_alert',
                'description' => 'Notification for low stock levels.',
                'channel' => 'email',
                'type' => 'inventory',
                'subject' => '[Inventory Alert] Low stock for {product_name}',
                'content' => $this->getInventoryAlertContent(),
                'status' => 'active',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'payment_overdue',
                'description' => 'Reminder for overdue payments.',
                'channel' => 'email',
                'type' => 'finance',
                'subject' => '[Payment Reminder] Invoice {invoice_number} is overdue',
                'content' => $this->getPaymentOverdueContent(),
                'status' => 'active',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'quality_inspection_created',
                'description' => 'Notification for new quality inspection orders.',
                'channel' => 'email',
                'type' => 'quality',
                'subject' => '[QA Notification] New Quality Inspection {inspection_number} created',
                'content' => $this->getQualityInspectionContent(),
                'status' => 'active',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'email_verification_code',
                'description' => 'Email template for sending verification codes.',
                'channel' => 'email',
                'type' => 'auth',
                'subject' => 'Your Verification Code',
                'content' => $this->getVerificationCodeTemplate(),
                'status' => 'active',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'order_confirmation',
                'description' => 'Email template for order confirmation.',
                'channel' => 'email',
                'type' => 'order',
                'subject' => 'Order Confirmation - #{order_number}',
                'content' => $this->getOrderConfirmationTemplate(),
                'status' => 'active',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('message_templates')->insert(array_merge($emailTemplates));
    }
    
    /**
     * 获取自动生成采购单通知的邮件内容
     */
    private function getPurchaseOrderGeneratedContent(): string
    {
        $content = <<<HTML
<p>Hello,</p>
<p>The system has automatically generated {purchase_count} purchase order(s) that require your review.</p>
<h3>Purchase Order Details:</h3>
<ul><li>PO Number(s): {purchase_numbers}</li></ul>
<p>Click the button below to view the details:</p>
<p style="text-align: center; margin-top: 25px;">
    <a href="{app_url}/purchases" class="button">View Purchase Orders</a>
</p>
<p>Thank you for using our system!</p>
HTML;
        return $this->getBaseTemplateWrapper('Automatic Purchase Order Generation', $content);
    }
    
    /**
     * 获取库存警告通知的邮件内容
     */
    private function getInventoryAlertContent(): string
    {
        $content = <<<HTML
<p>Hello,</p>
<p>The system has detected that the stock level for the following product is below the warning threshold:</p>
<div class="alert-box alert-danger">
    <h3>{product_name}</h3>
    <ul>
        <li>SKU: {product_sku}</li>
        <li>Current Stock: <strong>{current_stock}</strong></li>
        <li>Minimum Stock: {min_stock}</li>
        <li>Warehouse: {warehouse_name}</li>
    </ul>
</div>
<p>Please take action to ensure sufficient stock levels.</p>
<p style="text-align: center; margin-top: 25px;">
    <a href="{app_url}/inventory" class="button">View Inventory</a>
</p>
<p>Thank you for your attention.</p>
HTML;
        return $this->getBaseTemplateWrapper('Inventory Alert', $content);
    }
    
    /**
     * 获取付款逾期提醒的邮件内容
     */
    private function getPaymentOverdueContent(): string
    {
        $content = <<<HTML
<p>Hello,</p>
<p>This is a reminder that the payment for the following invoice is overdue:</p>
<div class="alert-box alert-warning">
    <h3>Invoice #{invoice_number}</h3>
    <ul>
        <li>Customer: {customer_name}</li>
        <li>Invoice Date: {invoice_date}</li>
        <li>Due Date: {due_date}</li>
        <li>Days Overdue: <strong>{days_overdue} day(s)</strong></li>
        <li>Amount Due: <strong>{amount_due}</strong></li>
    </ul>
</div>
<p>Please contact the customer and resolve this payment issue as soon as possible.</p>
<p style="text-align: center; margin-top: 25px;">
    <a href="{app_url}/finance/invoices" class="button">View Invoices</a>
</p>
<p>If you have any questions, please contact the finance department.</p>
HTML;
        return $this->getBaseTemplateWrapper('Payment Overdue Reminder', $content);
    }
    
    /**
     * 获取质检单创建通知的邮件内容
     */
    private function getQualityInspectionContent(): string
    {
        $content = <<<HTML
<p>Hello,</p>
<p>A new quality inspection order has been created in the system. Details are as follows:</p>
<div class="alert-box alert-info">
    <h3>Inspection Order #{inspection_number}</h3>
    <ul>
        <li>Product: {product_name}</li>
        <li>Supplier: {supplier_name}</li>
        <li>Created By: {created_by}</li>
        <li>Inspection Date: {inspection_date}</li>
    </ul>
</div>
<p>Please review and process this inspection order promptly.</p>
<p style="text-align: center; margin-top: 25px;">
    <a href="{app_url}/quality-inspections" class="button">View Inspection Order</a>
</p>
<p>Thank you for your cooperation.</p>
HTML;
        return $this->getBaseTemplateWrapper('New Quality Inspection Order', $content);
    }
    
    private function getBaseTemplateWrapper(string $title, string $innerContent): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{$title}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f7; }
        .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .email-header { background-color: #4a6cf7; color: #ffffff; padding: 25px; text-align: center; border-radius: 8px 8px 0 0; }
        .email-header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .email-content { padding: 30px 40px; }
        .email-content p { margin: 0 0 15px 0; font-size: 16px; }
        .button { display: inline-block; background-color: #4a6cf7; color: #ffffff !important; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; font-size: 16px; border: none; cursor: pointer; }
        .email-footer { border-top: 1px solid #e0e0e0; padding: 20px 40px; font-size: 12px; color: #888888; text-align: center; }
        .email-footer p { margin: 5px 0; }
        .alert-box { padding: 16px; margin: 16px 0; border-radius: 4px; border-left-width: 4px; border-left-style: solid; }
        .alert-info { background-color: #e0f2fe; border-color: #0ea5e9; color: #0369a1; }
        .alert-warning { background-color: #fef3c7; border-color: #f59e0b; color: #92400e; }
        .alert-danger { background-color: #fee2e2; border-color: #ef4444; color: #991b1b; }
        .alert-box h3 { margin-top: 0; margin-bottom: 10px; }
        .alert-box ul { margin-bottom: 0; padding-left: 20px; }
        .alert-box li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{$title}</h1>
        </div>
        <div class="email-content">
            {$innerContent}
        </div>
        <div class="email-footer">
            <p>This is an automated message, please do not reply directly.</p>
            <p>&copy; {app_year} {app_name}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getVerificationCodeTemplate(): string
    {
        $content = <<<HTML
<p>Dear User,</p>
<p>Your verification code is:</p>
<div style="font-size: 36px; font-weight: bold; color: #4a6cf7; text-align: center; letter-spacing: 6px; background-color: #f5f7fa; padding: 20px; border-radius: 5px; margin: 30px 0; border: 1px dashed #cccccc;">
    {verification_code}
</div>
<p>This code is valid for {expires_in_minutes} minutes. Please complete the verification process soon.</p>
<p>If you did not request this code, please ignore this email.</p>
HTML;
        return $this->getBaseTemplateWrapper('Your Verification Code', $content);
    }

    private function getOrderConfirmationTemplate(): string
    {
        $content = <<<HTML
<p>Dear {customer_name},</p>
<p>Thank you for your order! Your order has been confirmed. Here are the details:</p>
<div style="background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 25px; border: 1px solid #eeeeee;">
    <p style="margin: 8px 0; font-size: 14px;"><strong style="color: #555555; min-width: 150px; display: inline-block;">Order Number:</strong> {order_number}</p>
    <p style="margin: 8px 0; font-size: 14px;"><strong style="color: #555555; min-width: 150px; display: inline-block;">Order Date:</strong> {order_date}</p>
    <p style="margin: 8px 0; font-size: 14px;"><strong style="color: #555555; min-width: 150px; display: inline-block;">Order Status:</strong> {order_status}</p>
    <p style="margin: 8px 0; font-size: 14px;"><strong style="color: #555555; min-width: 150px; display: inline-block;">Payment Method:</strong> {payment_method}</p>
    <p style="margin: 8px 0; font-size: 14px;"><strong style="color: #555555; min-width: 150px; display: inline-block;">Shipping Address:</strong> {shipping_address}</p>
    <p style="margin: 8px 0; font-size: 14px;"><strong style="color: #555555; min-width: 150px; display: inline-block;">Estimated Delivery:</strong> {estimated_delivery_date}</p>
</div>
<h3>Your Order Items:</h3>
{order_items_table}
<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: right;">
    <p style="margin: 8px 0; font-size: 15px;"><strong style="color: #333333; min-width: 120px; display: inline-block; text-align: left;">Subtotal:</strong> <span style="display: inline-block; min-width: 100px; text-align: right; font-weight: bold;">{subtotal}</span></p>
    <p style="margin: 8px 0; font-size: 15px;"><strong style="color: #333333; min-width: 120px; display: inline-block; text-align: left;">Shipping Fee:</strong> <span style="display: inline-block; min-width: 100px; text-align: right; font-weight: bold;">{shipping_fee}</span></p>
    <p style="margin: 8px 0; font-size: 15px;"><strong style="color: #333333; min-width: 120px; display: inline-block; text-align: left;">Tax:</strong> <span style="display: inline-block; min-width: 100px; text-align: right; font-weight: bold;">{tax}</span></p>
    <p style="margin: 8px 0; font-size: 15px;"><strong style="color: #333333; min-width: 120px; display: inline-block; text-align: left;">Discount:</strong> <span style="display: inline-block; min-width: 100px; text-align: right; font-weight: bold;">{discount}</span></p>
    <p style="font-size: 18px; font-weight: bold; margin-top: 15px;"><strong style="color: #333333; min-width: 120px; display: inline-block; text-align: left;">Total Amount:</strong> <span style="display: inline-block; min-width: 100px; text-align: right; font-weight: bold;">{total_amount}</span></p>
</div>
<div style="text-align: center; margin-top: 30px;">
    <a href="{order_tracking_url}" class="button" style="color: #ffffff;">View Order Details</a>
</div>
<p style="margin-top: 30px;">If you have any questions, feel free to contact us.</p>
<p>Thank you for shopping with us!</p>
HTML;
        return $this->getBaseTemplateWrapper('Order Confirmation', $content);
    }
}; 