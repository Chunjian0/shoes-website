<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\Supplier;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewResponse;
use Illuminate\Http\RedirectResponse;

class MessageTemplateController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->middleware('permission:view notification settings')->only(['index', 'edit', 'create']);
        $this->middleware('permission:manage notification settings')->only(['update', 'preview', 'store']);
        $this->notificationService = $notificationService;
    }

    /**
     * 显示消息模板列表
     */
    public function index(): ViewResponse
    {
        try {
            $emailTemplates = MessageTemplate::where('channel', 'email')
                ->orderBy('name')
                ->get()
                ->groupBy(function($item) {
                    return $item->supplier_id ? 'custom' : 'global';
                });
                
            // 获取供应商列表（用于过滤显示）
            $suppliers = Supplier::where('is_active', true)
                ->orderBy('name')
                ->get();
                
            // 添加日志记录
            Log::info('[MessageTemplateController@index] Fetched email templates:', [
                'grouped_templates' => $emailTemplates->toArray(), // 转换集合为数组以便记录
                'global_count' => $emailTemplates->get('global', collect())->count(),
                'custom_count' => $emailTemplates->get('custom', collect())->count()
            ]);
                
            return view('settings.message-templates', compact('emailTemplates', 'suppliers'));
        } catch (\Exception $e) {
            Log::error('获取消息模板失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('settings.message-templates', [
                'emailTemplates' => collect(),
                'suppliers' => collect(),
                'error' => '获取消息模板列表时发生错误'
            ]);
        }
    }

    /**
     * 显示创建模板页面
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        try {
            // 获取可用的模板类型列表
            $templateTypes = $this->getAvailableTemplateTypes();
            
            // 获取供应商列表
            $suppliers = Supplier::where('is_active', true)
                ->orderBy('name')
                ->get();
                
            // 定义模板类型标签
            $templateTypeLabels = [
                'email' => '电子邮件'
            ];
            
            return view('settings.create-template', compact('templateTypes', 'suppliers', 'templateTypeLabels'));
        } catch (\Exception $e) {
            Log::error('加载创建模板页面失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('settings.message-templates')
                ->with('error', '加载创建模板页面时发生错误');
        }
    }

    /**
     * 创建新模板
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'content' => 'required|string',
                'channel' => 'required|in:email',
                'suppliers' => 'nullable|array',
                'suppliers.*' => 'exists:suppliers,id',
                'type' => 'nullable|string|max:50',
                'is_default' => 'boolean',
                'status' => 'required|in:active,inactive'
            ]);
            
            // 检查具有相同名称和供应商ID的模板是否已存在
            $existingTemplate = MessageTemplate::where('name', $validated['name'])
                ->where('channel', $validated['channel']);
                
            if (isset($validated['supplier_id'])) {
                $existingTemplate->where('supplier_id', $validated['supplier_id']);
            } else {
                $existingTemplate->whereNull('supplier_id');
            }
            
            if ($existingTemplate->exists()) {
                return back()->with('error', '具有相同名称和供应商的模板已存在')->withInput();
            }
            
            // 如果设置为默认模板，确保其他同名模板不是默认的
            if (isset($validated['is_default']) && $validated['is_default']) {
                MessageTemplate::where('name', $validated['name'])
                    ->where('channel', $validated['channel'])
                    ->update(['is_default' => false]);
            }
            
            // 提取供应商数组
            $suppliers = $request->input('suppliers', []);
            
            // 创建新模板
            $template = MessageTemplate::create($validated);
            
            // 如果有选择供应商，则关联供应商
            if (!empty($suppliers)) {
                $template->suppliers()->sync($suppliers);
            }
            
            // 清除相关缓存
            $this->notificationService->clearTemplateCache($template->name);
            
            return redirect()->route('settings.message-templates')
                ->with('success', "消息模板 '{$template->name}' 创建成功！");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('创建消息模板失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return back()->with('error', '创建模板时发生错误')->withInput();
        }
    }

    /**
     * 显示编辑模板页面
     * 
     * @param string $type
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(string $type, int $id)
    {
        try {
            $template = MessageTemplate::findOrFail($id);
            
            // 检查模板类型是否匹配
            if ($template->channel !== $type) {
                return redirect()->route('settings.message-templates')
                    ->with('error', '无效的模板类型');
            }
            
            // 获取模板可用变量
            $template->variables = $this->getTemplateVariables($template);
            
            // 获取供应商列表
            $suppliers = Supplier::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            // 定义模板类型标签
            $templateTypeLabels = [
                'email' => '电子邮件'
            ];
            
            return view('settings.edit-template', compact('template', 'templateTypeLabels', 'suppliers'));
        } catch (\Exception $e) {
            Log::error('获取消息模板详情失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $id
            ]);
            
            return redirect()->route('settings.message-templates')
                ->with('error', '获取模板详情时发生错误');
        }
    }

    /**
     * 更新模板
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $template = MessageTemplate::findOrFail($id);
            
            $validated = $request->validate([
                'description' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'content' => 'required|string',
                'suppliers' => 'nullable|array',
                'suppliers.*' => 'exists:suppliers,id',
                'is_default' => 'boolean',
                'status' => 'required|in:active,inactive',
                'channel' => 'required|in:email'
            ]);
            
            // 如果设置为默认模板，确保其他同名模板不是默认的
            if (isset($validated['is_default']) && $validated['is_default']) {
                MessageTemplate::where('name', $template->name)
                    ->where('channel', $template->channel)
                    ->where('id', '<>', $id)
                    ->update(['is_default' => false]);
            }
            
            // 提取供应商数组
            $suppliers = $request->input('suppliers', []);
            
            // 更新模板
            $template->update($validated);
            
            // 更新供应商关联
            $template->suppliers()->sync($suppliers);
            
            // 清除模板缓存
            $this->notificationService->clearTemplateCache($template->name);
            
            return redirect()->route('settings.message-templates')
                ->with('success', "消息模板 '{$template->name}' 更新成功！");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('更新消息模板失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $id,
                'data' => $request->all()
            ]);
            
            return back()->with('error', '更新模板时发生错误')->withInput();
        }
    }

    /**
     * 预览模板内容
     */
    public function preview(Request $request)
    {
        try {
            // 验证请求数据
            $validated = $request->validate([
                'content' => 'required|string',
                'type' => 'nullable|string',
                'suppliers' => 'nullable|array',
            ]);
            
            // 获取内容和类型
            $content = $validated['content'];
            $type = $validated['type'] ?? null;
            
            // 测试数据
            $testData = $this->getTypeSpecificTestData($type);
            
            // 处理供应商数据（如果有）
            if (!empty($validated['suppliers'])) {
                $suppliers = Supplier::whereIn('id', $validated['suppliers'])->get();
                
                if ($suppliers->count() > 0) {
                    $testData['supplier_name'] = $suppliers->first()->name;
                    $testData['supplier_email'] = $suppliers->first()->email;
                    $testData['supplier_contact'] = $suppliers->first()->contact_person;
                    $testData['supplier_names'] = $suppliers->pluck('name')->implode(', ');
                    $testData['supplier_count'] = $suppliers->count();
                }
            }
            
            // 解析模板内容
            $parsedContent = $this->parseTemplateContent($content, $testData);
            
            // 格式化HTML，添加容器样式
            $formattedHtml = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">' . $parsedContent . '</div>';
            
            return response()->json([
                'success' => true,
                'html' => $formattedHtml
            ]);
        } catch (\Exception $e) {
            Log::error('预览模板失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '预览模板时发生错误: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 获取特定类型的测试数据
     */
    private function getTypeSpecificTestData(string $type): array
    {
        switch ($type) {
            case 'email_verification_code':
                return [
                    'verification_code' => '123456',
                    'email' => 'test@example.com',
                    'expires_in_minutes' => 30,
                ];
            case 'order_confirmation':
                return [
                    'customer_name' => '张三',
                    'order_number' => 'ORD-2025-0045',
                    'order_date' => now()->format('Y年m月d日'),
                    'order_status' => '已付款',
                    'payment_method' => '在线支付',
                    'shipping_address' => '浙江省杭州市西湖区文三路123号',
                    'estimated_delivery_date' => now()->addDays(5)->format('Y年m月d日'),
                    'order_items_table' => '<table class="items-table">
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>数量</th>
                                <th>单价</th>
                                <th>小计</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>高端运动鞋 XYZ型号 (蓝色, 42码)</td>
                                <td>1</td>
                                <td>¥699.00</td>
                                <td>¥699.00</td>
                            </tr>
                            <tr>
                                <td>舒适休闲鞋 ABC型号 (黑色, 41码)</td>
                                <td>1</td>
                                <td>¥459.00</td>
                                <td>¥459.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="3" align="right">总计:</td>
                                <td>¥1,158.00</td>
                            </tr>
                        </tfoot>
                    </table>',
                    'subtotal' => '¥1,158.00',
                    'shipping_fee' => '¥0.00',
                    'tax' => '¥0.00',
                    'discount' => '¥0.00',
                    'total_amount' => '¥1,158.00',
                    'order_tracking_url' => 'http://example.com/orders/ORD-2025-0045',
                ];
            case 'purchase_order_generated':
                return [
                    'purchase_count' => 3,
                    'purchase_numbers' => 'PO-2023-001, PO-2023-002, PO-2023-003',
                    'user_name' => '张三',
                    'user_email' => 'zhangsan@example.com',
                ];
            case 'supplier_order_notification':
                return [
                    'supplier_name' => '优质镜片供应商',
                    'purchase_number' => 'PO-2025-0067',
                    'purchase_date' => now()->format('Y-m-d'),
                    'delivery_date' => now()->addDays(14)->format('Y-m-d'),
                    'purchase_total' => '¥7,250.00',
                    'contact_person' => '李采购',
                    'contact_email' => 'contact@company.com',
                    'supplier_email' => 'quality_supplier@example.com',
                    'supplier_contact' => '陈经理',
                    'items_list' => '<table style="width:100%;border-collapse:collapse;margin-top:10px;margin-bottom:10px">
                        <thead>
                            <tr style="background-color:#f3f4f6">
                                <th style="padding:8px;border:1px solid #e5e7eb;text-align:left">商品</th>
                                <th style="padding:8px;border:1px solid #e5e7eb;text-align:center">数量</th>
                                <th style="padding:8px;border:1px solid #e5e7eb;text-align:right">单价</th>
                                <th style="padding:8px;border:1px solid #e5e7eb;text-align:right">小计</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:8px;border:1px solid #e5e7eb">光学镜片A型号</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:center">50</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:right">¥50.00</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:right">¥2,500.00</td>
                            </tr>
                            <tr>
                                <td style="padding:8px;border:1px solid #e5e7eb">光学镜片B型号</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:center">30</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:right">¥75.00</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:right">¥2,250.00</td>
                            </tr>
                            <tr>
                                <td style="padding:8px;border:1px solid #e5e7eb">特殊镜片</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:center">25</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:right">¥100.00</td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:right">¥2,500.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color:#f9fafb">
                                <td style="padding:8px;border:1px solid #e5e7eb" colspan="3" align="right"><strong>总计:</strong></td>
                                <td style="padding:8px;border:1px solid #e5e7eb;text-align:right"><strong>¥7,250.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>'
                ];
            case 'inventory_alert':
                return [
                    'product_name' => '高端光学镜片XYZ',
                    'product_sku' => 'SKU-12345',
                    'current_stock' => 5,
                    'min_stock' => 10,
                    'warehouse_name' => '主仓库',
                ];
            case 'payment_overdue':
                return [
                    'customer_name' => 'ABC眼镜公司',
                    'invoice_number' => 'INV-2025-0123',
                    'invoice_date' => '2025-02-01',
                    'due_date' => '2025-02-28',
                    'amount_due' => '¥1,250.00',
                    'days_overdue' => 15,
                ];
            case 'quality_inspection_created':
                return [
                    'inspection_number' => 'QI-2025-0045',
                    'product_name' => '高端光学镜片XYZ',
                    'supplier_name' => '优质镜片供应商',
                    'created_by' => '张质检',
                    'inspection_date' => now()->format('Y-m-d'),
                ];
            default:
                return [];
        }
    }

    /**
     * 获取模板对应的变量列表
     */
    private function getTemplateVariables(MessageTemplate $template): array
    {
        // 通用变量
        $variables = [
            '{app_name}' => '应用名称',
            '{app_url}' => '应用URL',
            '{current_date}' => '当前日期（格式：YYYY-MM-DD）',
            '{current_time}' => '当前时间（格式：HH:MM）',
        ];
        
        // 供应商相关变量
        $variables = array_merge($variables, [
            '{supplier_name}' => '供应商名称',
            '{supplier_email}' => '供应商邮箱',
            '{supplier_contact}' => '供应商联系人',
            '{supplier_names}' => '所有选中供应商名称（逗号分隔）',
            '{supplier_count}' => '选中的供应商数量',
        ]);
        
        // 根据模板类型添加特定变量
        switch ($template->name) {
            case 'email_verification_code':
                $variables = array_merge($variables, [
                    '{verification_code}' => '验证码',
                    '{email}' => '用户邮箱',
                    '{expires_in_minutes}' => '有效期（分钟）',
                ]);
                break;
            case 'order_confirmation':
                $variables = array_merge($variables, [
                    '{customer_name}' => '客户名称',
                    '{order_number}' => '订单号',
                    '{order_date}' => '订单日期',
                    '{order_status}' => '订单状态',
                    '{payment_method}' => '支付方式',
                    '{shipping_address}' => '配送地址',
                    '{estimated_delivery_date}' => '预计送达日期',
                    '{order_items_table}' => '订单商品表格HTML',
                    '{subtotal}' => '商品总额',
                    '{shipping_fee}' => '运费',
                    '{tax}' => '税费',
                    '{discount}' => '优惠',
                    '{total_amount}' => '订单总额',
                    '{order_tracking_url}' => '订单跟踪链接',
                ]);
                break;
            case 'purchase_order_generated':
                $variables = array_merge($variables, [
                    '{purchase_count}' => '生成的采购单数量',
                    '{purchase_numbers}' => '采购单号列表',
                    '{user_name}' => '用户名称',
                    '{user_email}' => '用户邮箱',
                ]);
                break;
            case 'inventory_alert':
                $variables = array_merge($variables, [
                    '{product_name}' => '商品名称',
                    '{product_sku}' => '商品编码',
                    '{current_stock}' => '当前库存',
                    '{min_stock}' => '最低库存',
                    '{warehouse_name}' => '仓库名称',
                ]);
                break;
            case 'payment_overdue':
                $variables = array_merge($variables, [
                    '{customer_name}' => '客户名称',
                    '{invoice_number}' => '发票号码',
                    '{invoice_date}' => '发票日期',
                    '{due_date}' => '到期日期',
                    '{amount_due}' => '欠款金额',
                    '{days_overdue}' => '逾期天数',
                ]);
                break;
            case 'quality_inspection_created':
                $variables = array_merge($variables, [
                    '{inspection_number}' => '质检单号',
                    '{product_name}' => '商品名称',
                    '{supplier_name}' => '供应商名称',
                    '{created_by}' => '创建者名称',
                    '{inspection_date}' => '质检日期',
                ]);
                break;
            case 'supplier_order_notification':
                $variables = array_merge($variables, [
                    '{supplier_name}' => '供应商名称',
                    '{purchase_number}' => '采购单号',
                    '{order_date}' => '订单日期',
                    '{delivery_date}' => '预计交货日期',
                    '{total_amount}' => '总金额',
                    '{contact_person}' => '联系人',
                    '{contact_email}' => '联系邮箱',
                    '{items_list}' => '商品列表',
                ]);
                break;
            // 可以继续添加其他模板类型的变量
        }
        
        return $variables;
    }

    /**
     * 解析模板内容
     */
    private function parseTemplateContent(string $content, array $data): string
    {
        try {
            foreach ($data as $key => $value) {
                $placeholder = '{' . $key . '}';
                $content = str_replace($placeholder, $value, $content);
            }
            
            // 解析为Blade模板
            return Blade::render($content, $data);
        } catch (\Exception $e) {
            Log::error('解析模板内容失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $content;
        }
    }
    
    /**
     * 获取可用的模板类型列表
     */
    private function getAvailableTemplateTypes(): array
    {
        return [
            'purchase_order_generated' => 'Auto Purchase Order Generated',
            'inventory_alert' => 'Inventory Alert',
            'payment_overdue' => 'Payment Overdue Reminder',
            'quality_inspection_created' => 'Quality Inspection Created',
            'quality_inspection_updated' => 'Quality Inspection Status Updated',
            'supplier_order_notification' => 'Supplier Order Notification',
            'product_created' => 'Product Created',
            'product_updated' => 'Product Updated',
            'product_deleted' => 'Product Deleted',
            'purchase_created' => 'Purchase Order Created',
            'purchase_status_changed' => 'Purchase Order Status Updated',
            'payment_status_changed' => 'Payment Status Updated',
            'system_alert' => 'System Alert',
            'test_mail' => 'Test Mail',
            'email_verification_code' => 'Email Verification Code',
            'order_confirmation' => 'Order Confirmation',
        ];
    }

    /**
     * Get message templates as JSON for API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplatesApi(Request $request)
    {
        try {
            $type = $request->query('type');
            
            $query = MessageTemplate::where('status', 'active')
                ->where('channel', 'email');
                
            if ($type) {
                $query->where('name', $type);
            }
            
            $templates = $query->orderBy('name')->get([
                'id', 'name', 'subject', 'content', 'is_default', 'type'
            ]);
            
            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get message templates for API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get message templates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get template variables as JSON for API
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplateVariablesApi(Request $request, $id)
    {
        try {
            $template = MessageTemplate::findOrFail($id);
            $variables = $this->getTemplateVariables($template);
            
            return response()->json([
                'success' => true,
                'variables' => $variables
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get template variables for API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get template variables'
            ], 500);
        }
    }
} 