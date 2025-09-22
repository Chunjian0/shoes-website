<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use TCPDF;
use App\Models\CompanyProfile;

class SalesOrderController extends Controller
{
    /**
     * 显示订单列表
     */
    public function index(Request $request)
    {
        $query = SalesOrder::query();
        
        // 添加门店过滤
        $query->where('store_id', session('store_id'));
        
        // 搜索条件
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                  });
            });
        }
        
        // 状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        
        // 支付状态筛选
        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }
        
        // 日期范围筛选
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        // 获取分页数据
        $orders = $query->with(['customer', 'salesperson', 'items'])
                        ->orderByDesc('created_at')
                        ->paginate(10);
        
        return view('orders.index', compact('orders'));
    }
    
    /**
     * 显示订单详情
     */
    public function show(SalesOrder $order)
    {
        // 确保订单属于当前商店
        if ($order->store_id != session('store_id')) {
            abort(404);
        }

        // 加载关联数据
        $order->load(['customer', 'salesperson', 'items.product']);

        return view('orders.show', compact('order'));
    }
    
    /**
     * 更新订单状态
     */
    public function updateStatus(Request $request, SalesOrder $order)
    {
        // 验证订单是否属于当前门店
        if ($order->store_id !== session('store_id')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Order not found']);
            }
            abort(404, 'Order not found');
        }
        
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);
        
        $order->status = $request->status;
        $order->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => ucfirst($order->status),
                'message' => 'Order status updated successfully'
            ]);
        }
        
        return redirect()->route('orders.show', $order)
                         ->with('success', 'Order status has been updated');
    }
    
    /**
     * 更新支付状态
     */
    public function updatePaymentStatus(Request $request, SalesOrder $order)
    {
        // 验证订单是否属于当前门店
        if ($order->store_id !== session('store_id')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Order not found']);
            }
            abort(404, 'Order not found');
        }
        
        $request->validate([
            'payment_status' => 'required|in:unpaid,partially_paid,paid',
            'payment_method' => 'required_if:payment_status,paid,partially_paid',
            'amount' => 'required_if:payment_status,paid,partially_paid|numeric|min:0',
            'payment_date' => 'required_if:payment_status,paid,partially_paid|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            // 更新订单支付状态
            $order->payment_status = $request->payment_status;
            
            // 创建新的支付记录（如果有支付）
            $createPayment = false;
            $refresh = false;
            
            if (in_array($request->payment_status, ['paid', 'partially_paid']) && 
                $request->filled('amount') && $request->amount > 0) {
                
                $createPayment = true;
                $refresh = true;
                
                // 创建支付记录
                $payment = new Payment();
                $payment->sales_order_id = $order->id;
                $payment->amount = $request->amount;
                $payment->payment_method = $request->payment_method;
                $payment->payment_date = $request->payment_date;
                $payment->reference_number = $request->reference_number;
                $payment->notes = $request->notes;
                $payment->created_by = Auth::id();
                $payment->save();
                
                // 如果支付金额等于或超过剩余金额，则标记为已支付
                $totalPaid = $order->payments()->sum('amount') + $request->amount;
                if ($totalPaid >= $order->total) {
                    $order->payment_status = 'paid';
                    $order->paid_at = now();
                }
            }
            
            $order->save();
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'payment_status' => ucfirst(str_replace('_', ' ', $order->payment_status)),
                    'refresh' => $refresh,
                    'message' => 'Payment status updated successfully'
                ]);
            }
            
            return redirect()->route('orders.show', $order)
                             ->with('success', 'Payment status has been updated');
                             
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment status: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->route('orders.show', $order)
                             ->with('error', 'Failed to update payment status: ' . $e->getMessage());
        }
    }
    
    /**
     * 取消订单
     */
    public function cancel(SalesOrder $order)
    {
        // 验证订单是否属于当前门店
        if ($order->store_id !== session('store_id')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Order not found']);
            }
            abort(404, 'Order not found');
        }
        
        // 只有待处理或处理中的订单可以取消
        if (!in_array($order->status, ['pending', 'processing'])) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Only pending or processing orders can be cancelled'
                ]);
            }
            return redirect()->route('orders.show', $order)
                             ->with('error', 'Only pending or processing orders can be cancelled');
        }
        
        DB::beginTransaction();
        
        try {
            // 更新订单状态
            $order->status = 'cancelled';
            $order->save();
            
            // 恢复库存
            foreach ($order->items as $item) {
                $stock = \App\Models\Stock::where('product_id', $item->product_id)
                                         ->where('warehouse_id', $order->store_id)
                                         ->first();
                
                if ($stock) {
                    $stock->quantity += $item->quantity;
                    $stock->save();
                    
                    // 记录库存移动
                    $stockMovement = new \App\Models\StockMovement();
                    $stockMovement->product_id = $item->product_id;
                    $stockMovement->warehouse_id = $order->store_id;
                    $stockMovement->quantity = $item->quantity;
                    $stockMovement->type = 'order_cancelled';
                    $stockMovement->reference_id = $order->id;
                    $stockMovement->reference_type = SalesOrder::class;
                    $stockMovement->notes = "Order cancelled: {$order->order_number}";
                    $stockMovement->save();
                }
            }
            
            DB::commit();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order has been cancelled and inventory has been restored'
                ]);
            }
            
            return redirect()->route('orders.show', $order)
                             ->with('success', 'Order has been cancelled and inventory has been restored');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->route('orders.show', $order)
                             ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
    
    /**
     * 导出订单为PDF
     */
    public function exportPdf(SalesOrder $order)
    {
        // 确保订单属于当前商店
        if ($order->store_id != session('store_id')) {
            abort(404);
        }

        // 加载关联数据
        $order->load(['customer', 'salesperson', 'items.product']);

        // 获取公司信息
        $company = CompanyProfile::first();
        
        // 使用TCPDF生成PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        
        // 设置文档信息
        $pdf->SetCreator(config('app.name'));
        $pdf->SetAuthor(Auth::user()->name);
        $pdf->SetTitle('Order #' . $order->order_number);
        $pdf->SetSubject('Sales Order');
        
        // 移除页眉页脚
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // 设置默认字体
        $pdf->SetFont('helvetica', '', 10);
        
        // 添加页面
        $pdf->AddPage();
        
        // 渲染HTML内容
        $html = View::make('orders.pdf', compact('order', 'company'))->render();
        
        // 输出HTML内容到PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // 关闭并输出PDF文档
        return $pdf->Output("order-{$order->order_number}.pdf", 'D');
    }
} 