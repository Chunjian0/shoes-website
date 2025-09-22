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
        // 检查message_templates表是否存在
        if (Schema::hasTable('message_templates')) {
            $this->insertEmailTemplates();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不执行任何操作，因为这会删除用户创建的模板
    }
    
    /**
     * 插入默认的电子邮件模板
     */
    private function insertEmailTemplates(): void
    {
        $templates = [
            // 采购相关模板
            [
                'name' => 'default_purchase_order_generated',
                'description' => '自动生成采购单通知',
                'channel' => 'email',
                'type' => 'purchase',
                'subject' => '[系统通知] 自动采购单生成',
                'content' => $this->getPurchaseOrderGeneratedEmailContent(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'default_supplier_order_notification',
                'description' => '供应商订单通知',
                'channel' => 'email',
                'type' => 'purchase',
                'subject' => '[订单通知] 新采购订单 #{purchase_number}',
                'content' => $this->getSupplierOrderEmailContent(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 库存相关模板
            [
                'name' => 'default_inventory_alert',
                'description' => '库存警告通知',
                'channel' => 'email',
                'type' => 'inventory',
                'subject' => '[库存警告] {product_name} 库存不足',
                'content' => $this->getInventoryAlertEmailContent(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 财务相关模板
            [
                'name' => 'default_payment_overdue',
                'description' => '付款逾期提醒',
                'channel' => 'email',
                'type' => 'finance',
                'subject' => '[付款提醒] 发票 {invoice_number} 付款已逾期',
                'content' => $this->getPaymentOverdueEmailContent(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 质检相关模板
            [
                'name' => 'default_quality_inspection_created',
                'description' => '质检单创建通知',
                'channel' => 'email',
                'type' => 'quality',
                'subject' => '[质检通知] 新的质检单 {inspection_number} 已创建',
                'content' => $this->getQualityInspectionEmailContent(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 系统通知模板
            [
                'name' => 'default_system_notification',
                'description' => '系统通知',
                'channel' => 'email',
                'type' => 'system',
                'subject' => '[系统通知] {subject}',
                'content' => $this->getSystemNotificationEmailContent(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        $this->insertTemplates($templates);
    }

    
    /**
     * 安全地插入模板数据，避免名称冲突
     */
    private function insertTemplates(array $templates): void
    {
        foreach ($templates as $template) {
            // 检查是否已存在相同渠道和名称的模板
            $exists = DB::table('message_templates')
                ->where('name', $template['name'])
                ->where('channel', $template['channel'])
                ->exists();
            
            // 如果不存在，则插入
            if (!$exists) {
                DB::table('message_templates')->insert($template);
            }
        }
    }
    
    /**
     * 获取自动生成采购单通知的邮件内容
     */
    private function getPurchaseOrderGeneratedEmailContent(): string
    {
        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #4f46e5; margin: 0;">采购单自动生成通知</h2>
    </div>
    
    <p>您好!</p>

    <p>系统自动生成了 <strong>{purchase_count}</strong> 个采购单，请及时审核。</p>

    <div style="background-color: #f3f4f6; border-left: 4px solid #4f46e5; padding: 16px; margin: 16px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #374151;">采购单详情:</h3>
        <p style="margin-bottom: 0;">采购单号: <strong>{purchase_numbers}</strong></p>
    </div>

    <p>点击下面的按钮查看详情:</p>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{app_url}/purchases" style="display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            查看采购单
        </a>
    </div>

    <p>如有任何问题，请联系系统管理员。</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;" />
    
    <p style="font-size: 12px; color: #6b7280; text-align: center;">
        此为系统自动发送的邮件，请勿直接回复。<br>
        &copy; {current_date} {app_name}. 版权所有。
    </p>
</div>
HTML;
    }
    
    /**
     * 获取发送给供应商的订单通知的邮件内容
     */
    private function getSupplierOrderEmailContent(): string
    {
        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #4f46e5; margin: 0;">采购订单通知</h2>
    </div>
    
    <p>尊敬的 {supplier_name}:</p>

    <p>我们已向贵公司发出了新的采购订单，详情如下:</p>

    <div style="background-color: #f3f4f6; border-left: 4px solid #4f46e5; padding: 16px; margin: 16px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #374151;">订单详情:</h3>
        <ul style="padding-left: 20px; margin-bottom: 0;">
            <li>采购单号: <strong>{purchase_number}</strong></li>
            <li>订单日期: <strong>{purchase_date}</strong></li>
            <li>预计交货日期: <strong>{delivery_date}</strong></li>
            <li>订单总额: <strong>{purchase_total}</strong></li>
        </ul>
    </div>

    <h3 style="color: #374151;">订购商品:</h3>
    
    {items_list}

    <p>如有任何疑问，请联系:</p>
    <p>
        联系人: {contact_person}<br>
        电子邮件: {contact_email}
    </p>

    <p>请尽快确认此订单。谢谢您的配合！</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;" />
    
    <p style="font-size: 12px; color: #6b7280; text-align: center;">
        &copy; {current_date} {app_name}. 版权所有。
    </p>
</div>
HTML;
    }
    
    /**
     * 获取库存警告通知的邮件内容
     */
    private function getInventoryAlertEmailContent(): string
    {
        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #ef4444; margin: 0;">库存警告通知</h2>
    </div>
    
    <p>您好!</p>

    <p>系统检测到以下商品库存低于预警阈值:</p>

    <div style="background-color: #fee2e2; border-left: 4px solid #ef4444; padding: 16px; margin: 16px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #991b1b;">{product_name}</h3>
        <ul style="padding-left: 20px; margin-bottom: 0;">
            <li>SKU: <strong>{product_sku}</strong></li>
            <li>当前库存: <strong>{current_stock}</strong></li>
            <li>最低库存: <strong>{min_stock}</strong></li>
            <li>仓库: <strong>{warehouse_name}</strong></li>
        </ul>
    </div>

    <p>请及时处理，以确保库存充足。</p>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{app_url}/inventory" style="display: inline-block; background-color: #ef4444; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            查看库存
        </a>
    </div>

    <p>如有任何问题，请联系仓库管理员。</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;" />
    
    <p style="font-size: 12px; color: #6b7280; text-align: center;">
        此为系统自动发送的邮件，请勿直接回复。<br>
        &copy; {current_date} {app_name}. 版权所有。
    </p>
</div>
HTML;
    }
    
    /**
     * 获取付款逾期提醒的邮件内容
     */
    private function getPaymentOverdueEmailContent(): string
    {
        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #f59e0b; margin: 0;">付款逾期提醒</h2>
    </div>
    
    <p>您好!</p>

    <p>我们想提醒您，以下发票的付款已经逾期:</p>

    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 16px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #92400e;">发票 #{invoice_number}</h3>
        <ul style="padding-left: 20px; margin-bottom: 0;">
            <li>客户: <strong>{customer_name}</strong></li>
            <li>发票日期: <strong>{invoice_date}</strong></li>
            <li>到期日期: <strong>{due_date}</strong></li>
            <li>逾期天数: <strong>{days_overdue}天</strong></li>
            <li>欠款金额: <strong>{amount_due}</strong></li>
        </ul>
    </div>

    <p>请尽快联系客户并处理此付款问题。</p>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{app_url}/finance/invoices" style="display: inline-block; background-color: #f59e0b; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            查看发票
        </a>
    </div>

    <p>如有任何问题，请联系财务部门。</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;" />
    
    <p style="font-size: 12px; color: #6b7280; text-align: center;">
        此为系统自动发送的邮件，请勿直接回复。<br>
        &copy; {current_date} {app_name}. 版权所有。
    </p>
</div>
HTML;
    }
    
    /**
     * 获取质检单创建通知的邮件内容
     */
    private function getQualityInspectionEmailContent(): string
    {
        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #0ea5e9; margin: 0;">质检单创建通知</h2>
    </div>
    
    <p>您好!</p>

    <p>系统中已创建新的质检单，详情如下:</p>

    <div style="background-color: #e0f2fe; border-left: 4px solid #0ea5e9; padding: 16px; margin: 16px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #0369a1;">质检单 #{inspection_number}</h3>
        <ul style="padding-left: 20px; margin-bottom: 0;">
            <li>商品: <strong>{product_name}</strong></li>
            <li>供应商: <strong>{supplier_name}</strong></li>
            <li>创建者: <strong>{created_by}</strong></li>
            <li>质检日期: <strong>{inspection_date}</strong></li>
        </ul>
    </div>

    <p>请尽快处理此质检单。</p>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{app_url}/quality-inspections" style="display: inline-block; background-color: #0ea5e9; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            查看质检单
        </a>
    </div>

    <p>如有任何问题，请联系质检部门。</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;" />
    
    <p style="font-size: 12px; color: #6b7280; text-align: center;">
        此为系统自动发送的邮件，请勿直接回复。<br>
        &copy; {current_date} {app_name}. 版权所有。
    </p>
</div>
HTML;
    }
    
    /**
     * 获取系统通知的邮件内容
     */
    private function getSystemNotificationEmailContent(): string
    {
        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #4f46e5; margin: 0;">系统通知</h2>
    </div>
    
    <p>您好!</p>

    <p>{message}</p>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{app_url}" style="display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            访问系统
        </a>
    </div>

    <p>如有任何问题，请联系系统管理员。</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;" />
    
    <p style="font-size: 12px; color: #6b7280; text-align: center;">
        此为系统自动发送的邮件，请勿直接回复。<br>
        &copy; {current_date} {app_name}. 版权所有。
    </p>
</div>
HTML;
    }
};
