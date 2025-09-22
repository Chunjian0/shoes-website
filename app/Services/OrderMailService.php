<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderMailService
{
    /**
     * 通知服务
     *
     * @var NotificationService
     */
    protected $notificationService;
    
    /**
     * 构造函数
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    /**
     * 发送订单确认邮件
     *
     * @param SalesOrder $order 订单实例
     * @return bool 发送成功返回 true，否则返回 false
     */
    public function sendOrderConfirmationMail(SalesOrder $order): bool
    {
        try {
            // 加载订单关联数据（如果尚未加载）
            if (!$order->relationLoaded('customer')) {
                $order->load('customer');
            }
            
            if (!$order->relationLoaded('items')) {
                $order->load(['items', 'items.product']);
            }
            
            $customer = $order->customer;
            
            if (!$customer || !$customer->email) {
                Log::warning('无法发送订单确认邮件：客户邮箱不存在', ['order_id' => $order->id]);
                return false;
            }
            
            // 获取模板
            $template = MessageTemplate::getTemplateFor('order_confirmation', null, 'email');
            
            if (!$template) {
                Log::warning('无法发送订单确认邮件：订单确认邮件模板不存在', ['order_id' => $order->id]);
                return false;
            }
            
            // 准备模板数据
            $templateData = $this->prepareOrderTemplateData($order);
            
            // 使用通知服务发送邮件
            $subject = $this->replacePlaceholders($template->subject, $templateData);
            $content = $this->replacePlaceholders($template->content, $templateData);
            
            $result = $this->notificationService->sendCustomEmail(
                $customer->email,
                $subject, 
                $content,
                [], // 无附件
                false, // 不跳过日志记录
                'order_confirmation' // 邮件类型
            );
            
            if ($result) {
                Log::info('订单确认邮件发送成功', [
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'email' => $customer->email
                ]);
            } else {
                Log::error('订单确认邮件发送失败', [
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'email' => $customer->email
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('发送订单确认邮件时发生错误', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * 准备订单相关的模板数据
     *
     * @param SalesOrder $order
     * @return array
     */
    protected function prepareOrderTemplateData(SalesOrder $order): array
    {
        // 获取基本数据
        $customer = $order->customer;
        $items = $order->items;
        
        // Log the value of shipping_cost before formatting
        Log::debug('OrderMailService::prepareOrderTemplateData - Checking shipping_cost value', [
            'order_id' => $order->id,
            'shipping_cost_from_order_object' => $order->shipping_cost,
            'order_object_attributes' => $order->getAttributes() // Log all attributes for context
        ]);
        
        // 计算汇总数据
        $subtotal = 0;
        $itemsTable = $this->generateOrderItemsTable($order->items, $subtotal);
        
        // 应用货币格式
        $formattedSubtotal = $this->formatCurrency($subtotal);
        $formattedShippingFee = $this->formatCurrency($order->shipping_fee ?? 0);
        $formattedTax = $this->formatCurrency($order->tax_amount ?? 0);
        $formattedDiscount = $this->formatCurrency($order->discount_amount ?? 0);
        $formattedTotalAmount = $this->formatCurrency($order->total_amount ?? $subtotal);
        
        // 日期格式化
        $orderDate = $order->order_date ? Carbon::parse($order->order_date)->format('Y年m月d日') : Carbon::now()->format('Y年m月d日');
        $estimatedDeliveryDate = $order->estimated_arrival_date 
            ? Carbon::parse($order->estimated_arrival_date)->format('Y年m月d日')
            : Carbon::now()->addDays(7)->format('Y年m月d日'); // 默认一周后
        
        // 状态映射
        $statusMap = [
            'pending' => '待付款',
            'paid' => '已付款',
            'processing' => '处理中',
            'shipped' => '已发货',
            'delivered' => '已送达',
            'cancelled' => '已取消',
            'refunded' => '已退款',
        ];
        
        $orderStatus = $statusMap[$order->status] ?? $order->status;
        
        // 创建追踪链接 (修正路由名称)
        $orderTrackingUrl = route('orders.show', $order->order_number, false);
        
        // 准备模板变量数据
        $data = [
            'app_name' => config('app.name', 'Your Store'),
            'app_url' => config('app.url'),
            'current_date' => Carbon::now()->format('Y年m月d日'),
            'current_time' => Carbon::now()->format('H:i'),
            
            'customer_name' => $customer->name ?? $customer->email,
            'order_number' => $order->order_number,
            'order_date' => $orderDate,
            'order_status' => $orderStatus,
            'payment_method' => $order->payment_method ?? '在线支付',
            'shipping_address' => $this->formatAddress($order),
            'estimated_delivery_date' => $estimatedDeliveryDate,
            
            'order_items_table' => $itemsTable,
            'subtotal' => $formattedSubtotal,
            'tax' => $formattedTax,
            'discount' => $formattedDiscount,
            'total_amount' => $formattedTotalAmount,
            
            'order_tracking_url' => $orderTrackingUrl,
            'app_year' => date('Y'),
        ];

        // Log the formatted shipping fee *before* adding to the final array
        $formattedShippingFeeValue = $this->formatCurrency($order->shipping_cost ?? 0);
        Log::debug('OrderMailService::prepareOrderTemplateData - Formatted shipping_fee value', [
            'order_id' => $order->id,
            'input_value' => $order->shipping_cost ?? 0,
            'formatted_output' => $formattedShippingFeeValue
        ]);
        $data['shipping_fee'] = $formattedShippingFeeValue; // Add it back to the array

        return $data; // Return the modified array
    }
    
    /**
     * 生成订单商品表格 HTML
     *
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param float &$subtotal 商品总额（引用传递，会被更新）
     * @return string
     */
    protected function generateOrderItemsTable($items, &$subtotal): string
    {
        $html = '<table class="items-table" style="width:100%; border-collapse:collapse; margin:20px 0;">
            <thead>
                <tr>
                    <th style="padding:12px 15px; border:1px solid #e0e0e0; text-align:left; background-color:#f5f7fa;">Item</th>
                    <th style="padding:12px 15px; border:1px solid #e0e0e0; text-align:center; background-color:#f5f7fa;">Quantity</th>
                    <th style="padding:12px 15px; border:1px solid #e0e0e0; text-align:right; background-color:#f5f7fa;">Unit Price</th>
                    <th style="padding:12px 15px; border:1px solid #e0e0e0; text-align:right; background-color:#f5f7fa;">Subtotal</th>
                </tr>
            </thead>
            <tbody>';
        
        $subtotal = 0; // Reset subtotal calculation here
        
        foreach ($items as $item) {
            // Ensure price is treated as a float using the correct attribute
            $unitPrice = (float)($item->unit_price ?? 0);
            $quantity = (int)($item->quantity ?? 0);
            $itemSubtotal = $quantity * $unitPrice;
            $subtotal += $itemSubtotal;
            
            // Format specifications
            $specifications = $item->specifications ? (object)$item->specifications : null;
            $specText = '';
            if ($specifications) {
                $specParts = [];
                foreach ($specifications as $key => $value) {
                    // Simple key: value format for specs
                    $specParts[] = htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . ': ' . htmlspecialchars((string)$value);
                }
                if (!empty($specParts)) {
                     $specText = ' <small style="font-size: 0.85em; color: #555;">(' . implode(', ', $specParts) . ')</small>';
                }
            }
            
            $productName = htmlspecialchars($item->product->name ?? $item->name ?? 'Unknown Product');
            
            $html .= '<tr>
                <td style="padding:12px 15px; border:1px solid #e0e0e0;">' . $productName . $specText . '</td>
                <td style="padding:12px 15px; border:1px solid #e0e0e0; text-align:center;">' . $quantity . '</td>
                <td style="padding:12px 15px; border:1px solid #e0e0e0; text-align:right;">' . $this->formatCurrency($unitPrice) . '</td>
                <td style="padding:12px 15px; border:1px solid #e0e0e0; text-align:right;">' . $this->formatCurrency($itemSubtotal) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="padding:12px 15px; border:1px solid #e0e0e0; text-align:right; font-weight:bold; background-color:#f5f7fa;">Subtotal:</td>
                    <td style="padding:12px 15px; border:1px solid #e0e0e0; text-align:right; font-weight:bold; background-color:#f5f7fa;">' . $this->formatCurrency($subtotal) . '</td>
                </tr>
            </tfoot>
        </table>';
        
        return $html;
    }
    
    /**
     * 格式化地址
     *
     * @param SalesOrder $order
     * @return string
     */
    protected function formatAddress(SalesOrder $order): string
    {
        $address = [];
        
        // 优先使用订单中的地址
        if ($order->shipping_address) {
            return $order->shipping_address;
        }
        
        // 尝试从其他属性组装地址
        $addressParts = [];
        
        if ($order->shipping_province) {
            $addressParts[] = $order->shipping_province;
        }
        
        if ($order->shipping_city) {
            $addressParts[] = $order->shipping_city;
        }
        
        if ($order->shipping_district) {
            $addressParts[] = $order->shipping_district;
        }
        
        if ($order->shipping_street) {
            $addressParts[] = $order->shipping_street;
        }
        
        if (!empty($addressParts)) {
            return implode(' ', $addressParts);
        }
        
        // 回退到客户默认地址
        if ($order->customer && $order->customer->address) {
            return $order->customer->address;
        }
        
        return '未提供配送地址';
    }
    
    /**
     * 替换模板中的占位符
     *
     * @param string $content
     * @param array $data
     * @return string
     */
    protected function replacePlaceholders(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $content = str_replace($placeholder, $value, $content);
        }
        
        return $content;
    }

    // 确保 formatCurrency 方法存在且工作正常
    private function formatCurrency(float $amount, string $currency = 'MYR', string $locale = 'en_MY'): string
    {
        // 备用方案：不使用 NumberFormatter
        // 注意：这不会处理复杂的区域格式，但能满足基本的 RM 显示
        if ($currency === 'MYR') {
            return 'RM ' . number_format($amount, 2, '.', ','); // 保留两位小数，用.做小数点，用,做千位分隔符
        } else {
            // 其他货币的简单处理
            return $currency . ' ' . number_format($amount, 2, '.', ',');
        }
    }
} 