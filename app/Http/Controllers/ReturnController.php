<?php

namespace App\Http\Controllers;

use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnController extends Controller
{
    /**
     * 显示退货申请表单
     */
    public function create(SalesOrder $order)
    {
        // 验证订单是否属于当前商店
        if ($order->store_id != session('current_store_id')) {
            abort(403, '您无权访问此订单');
        }

        // 验证订单状态是否为已完成
        if ($order->status != 'completed') {
            return redirect()->route('orders.show', $order)->with('error', '只有已完成的订单才能申请退货');
        }

        return view('returns.create', compact('order'));
    }

    /**
     * 保存退货申请
     */
    public function store(Request $request, SalesOrder $order)
    {
        // 验证订单是否属于当前商店
        if ($order->store_id != session('current_store_id')) {
            abort(403, '您无权访问此订单');
        }

        // 验证订单状态是否为已完成
        if ($order->status != 'completed') {
            return redirect()->route('orders.show', $order)->with('error', '只有已完成的订单才能申请退货');
        }

        // 验证输入数据
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.reason' => 'required|string|max:255',
        ]);

        // 检查是否有退货项目
        $hasReturnItems = false;
        foreach ($request->items as $itemId => $itemData) {
            if ($itemData['quantity'] > 0) {
                $hasReturnItems = true;
                break;
            }
        }

        if (!$hasReturnItems) {
            return redirect()->back()->with('error', '请至少选择一个退货项目')->withInput();
        }

        // 开始事务
        DB::beginTransaction();

        try {
            // 创建退货申请
            $returnRequest = new ReturnRequest([
                'return_number' => 'RET-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'sales_order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'store_id' => $order->store_id,
                'user_id' => Auth::id(),
                'reason' => $validated['reason'],
                'status' => 'pending',
                'total_amount' => $validated['total_amount'],
            ]);

            $returnRequest->save();

            // 创建退货项目
            foreach ($request->items as $itemId => $itemData) {
                if ($itemData['quantity'] > 0) {
                    // 获取原始订单项目
                    $orderItem = SalesOrderItem::findOrFail($itemId);

                    // 验证退货数量不超过原始订单数量
                    if ($itemData['quantity'] > $orderItem->quantity) {
                        throw new \Exception("退货数量不能超过原始订单数量");
                    }

                    // 创建退货项目
                    $returnItem = new ReturnItem([
                        'return_request_id' => $returnRequest->id,
                        'sales_order_item_id' => $orderItem->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'total' => $itemData['price'] * $itemData['quantity'],
                        'reason' => $itemData['reason'],
                    ]);

                    $returnItem->save();
                }
            }

            DB::commit();

            return redirect()->route('returns.show', $returnRequest)->with('success', '退货申请已提交，等待审核');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '退货申请提交失败: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 显示退货申请详情
     */
    public function show(ReturnRequest $return)
    {
        // 验证退货申请是否属于当前商店
        if ($return->store_id != session('current_store_id')) {
            abort(403, '您无权访问此退货申请');
        }

        // 加载关联数据
        $return->load(['order', 'customer', 'items.product', 'user', 'processor']);

        return view('returns.show', compact('return'));
    }

    /**
     * 显示退货申请列表
     */
    public function index(Request $request)
    {
        $query = ReturnRequest::query()
            ->where('store_id', session('current_store_id'))
            ->with(['order', 'customer']);

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // 状态筛选
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 日期范围筛选
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        $returns = $query->paginate(10);
        
        if ($request->hasAny(['search', 'status', 'date_from', 'date_to'])) {
            $returns->appends($request->only(['search', 'status', 'date_from', 'date_to']));
        }

        return view('returns.index', compact('returns'));
    }

    /**
     * 处理退货申请（批准或拒绝）
     */
    public function process(Request $request, ReturnRequest $return)
    {
        // 验证退货申请是否属于当前商店
        if ($return->store_id != session('current_store_id')) {
            abort(403, '您无权访问此退货申请');
        }

        // 验证退货申请状态是否为待处理
        if ($return->status != 'pending') {
            return redirect()->route('returns.show', $return)->with('error', '此退货申请已处理');
        }

        // 验证输入数据
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        // 开始事务
        DB::beginTransaction();

        try {
            // 更新退货申请状态
            $return->status = $validated['status'];
            $return->processed_by = Auth::id();
            $return->processed_at = now();
            $return->notes = $validated['notes'];
            $return->save();

            // 如果批准退货，更新库存和创建退款
            if ($validated['status'] == 'approved') {
                // 处理每个退货项目
                foreach ($return->items as $item) {
                    // 更新库存
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $return->store_id)
                        ->first();

                    if ($stock) {
                        $stock->quantity += $item->quantity;
                        $stock->save();
                    } else {
                        // 如果没有库存记录，创建一个新的
                        Stock::create([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $return->store_id,
                            'quantity' => $item->quantity,
                        ]);
                    }

                    // 记录库存移动
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->recordStockMovement(
                            $return->store_id,
                            $item->quantity,
                            'return',
                            "退货申请 #{$return->return_number}"
                        );
                    }
                }

                // 创建退款记录
                $payment = new Payment([
                    'sales_order_id' => $return->order->id,
                    'amount' => -$return->total_amount, // 负数表示退款
                    'payment_method' => $return->order->payment_method ?? 'refund',
                    'payment_date' => now(),
                    'status' => 'completed',
                    'reference_number' => 'REF-' . $return->return_number,
                    'notes' => "退货申请 #{$return->return_number} 的退款",
                ]);

                $payment->save();

                // 更新订单支付状态
                $this->updateOrderPaymentStatus($return->order);
            }

            DB::commit();

            $statusText = $validated['status'] == 'approved' ? '已批准' : '已拒绝';
            return redirect()->route('returns.show', $return)->with('success', "退货申请{$statusText}");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '处理退货申请失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新订单支付状态
     */
    private function updateOrderPaymentStatus(SalesOrder $order)
    {
        // 计算订单总金额和已支付金额
        $totalAmount = $order->total_amount;
        $paidAmount = $order->payments()->where('status', 'completed')->sum('amount');

        // 更新支付状态
        if ($paidAmount <= 0) {
            $order->payment_status = 'unpaid';
        } elseif ($paidAmount < $totalAmount) {
            $order->payment_status = 'partially_paid';
        } else {
            $order->payment_status = 'paid';
        }

        $order->save();
    }
} 