<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageTemplateApiController extends Controller
{
    /**
     * Get message templates as JSON for API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplates(Request $request)
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
    public function getTemplateVariables(Request $request, $id)
    {
        try {
            $template = MessageTemplate::findOrFail($id);
            
            // 通用变量
            $variables = [
                '{app_name}' => 'Application Name',
                '{app_url}' => 'Application URL',
                '{current_date}' => 'Current Date (Format: YYYY-MM-DD)',
                '{current_time}' => 'Current Time (Format: HH:MM)',
            ];
            
            // 供应商相关变量
            $variables = array_merge($variables, [
                '{supplier_name}' => 'Supplier Name',
                '{supplier_email}' => 'Supplier Email',
                '{supplier_contact}' => 'Supplier Contact Person',
                '{supplier_names}' => 'All Selected Supplier Names (Comma Separated)',
                '{supplier_count}' => 'Number of Selected Suppliers',
            ]);
            
            // 根据模板类型添加特定变量
            switch ($template->name) {
                case 'purchase_order_generated':
                    $variables = array_merge($variables, [
                        '{purchase_count}' => 'Number of Generated Purchase Orders',
                        '{purchase_numbers}' => 'List of Purchase Order Numbers',
                        '{user_name}' => 'User Name',
                        '{user_email}' => 'User Email',
                    ]);
                    break;
                case 'inventory_alert':
                    $variables = array_merge($variables, [
                        '{product_name}' => 'Product Name',
                        '{product_sku}' => 'Product SKU',
                        '{current_stock}' => 'Current Stock',
                        '{min_stock}' => 'Minimum Stock',
                        '{warehouse_name}' => 'Warehouse Name',
                    ]);
                    break;
                case 'payment_overdue':
                    $variables = array_merge($variables, [
                        '{customer_name}' => 'Customer Name',
                        '{invoice_number}' => 'Invoice Number',
                        '{invoice_date}' => 'Invoice Date',
                        '{due_date}' => 'Due Date',
                        '{amount_due}' => 'Amount Due',
                        '{days_overdue}' => 'Days Overdue',
                    ]);
                    break;
                case 'quality_inspection_created':
                    $variables = array_merge($variables, [
                        '{inspection_number}' => 'Inspection Number',
                        '{product_name}' => 'Product Name',
                        '{supplier_name}' => 'Supplier Name',
                        '{created_by}' => 'Created By',
                        '{inspection_date}' => 'Inspection Date',
                    ]);
                    break;
                case 'supplier_order_notification':
                    $variables = array_merge($variables, [
                        '{supplier_name}' => 'Supplier Name',
                        '{purchase_number}' => 'Purchase Order Number',
                        '{order_date}' => 'Order Date',
                        '{delivery_date}' => 'Expected Delivery Date',
                        '{total_amount}' => 'Total Amount',
                        '{contact_person}' => 'Contact Person',
                        '{contact_email}' => 'Contact Email',
                        '{items_list}' => 'Items List',
                    ]);
                    break;
            }
            
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