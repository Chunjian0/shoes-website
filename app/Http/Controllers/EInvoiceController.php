<?php

namespace App\Http\Controllers;

use App\Models\EInvoice;
use App\Models\SalesOrder;
use App\Services\EInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EInvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(EInvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * 显示发票列表
     */
    public function index(Request $request)
    {
        $query = EInvoice::query()
            ->where('store_id', session('current_store_id'))
            ->with(['order', 'customer']);

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  })
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
            $query->whereDate('invoice_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->input('date_to'));
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        $invoices = $query->paginate(10);
        
        if ($request->hasAny(['search', 'status', 'date_from', 'date_to'])) {
            $invoices->appends($request->only(['search', 'status', 'date_from', 'date_to']));
        }

        return view('invoices.index', compact('invoices'));
    }

    /**
     * 显示创建发票表单
     */
    public function create()
    {
        // 获取未开具发票的已完成订单
        $orders = SalesOrder::where('store_id', session('current_store_id'))
            ->where('status', 'completed')
            ->whereDoesntHave('eInvoice')
            ->with('customer')
            ->get();

        return view('invoices.create', compact('orders'));
    }

    /**
     * 保存新发票
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:sales_orders,id',
        ]);

        $order = SalesOrder::findOrFail($validated['order_id']);

        // 验证订单是否属于当前商店
        if ($order->store_id != session('current_store_id')) {
            abort(403, '您无权为此订单创建发票');
        }

        // 验证订单状态是否为已完成
        if ($order->status != 'completed') {
            return redirect()->back()->with('error', '只有已完成的订单才能创建发票');
        }

        // 检查订单是否已有发票
        if ($order->eInvoice) {
            return redirect()->route('invoices.show', $order->eInvoice)->with('info', '此订单已有发票');
        }

        // 创建发票
        $invoice = $this->invoiceService->createFromOrder($order);

        return redirect()->route('invoices.show', $invoice)->with('success', '发票创建成功');
    }

    /**
     * 显示发票详情
     */
    public function show(EInvoice $invoice)
    {
        // 验证发票是否属于当前商店
        if ($invoice->store_id != session('current_store_id')) {
            abort(403, '您无权访问此发票');
        }

        // 加载关联数据
        $invoice->load(['order.items', 'customer', 'store', 'user']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * 编辑发票
     */
    public function edit(EInvoice $invoice)
    {
        // 验证发票是否属于当前商店
        if ($invoice->store_id != session('current_store_id')) {
            abort(403, '您无权编辑此发票');
        }

        // 验证发票是否可编辑
        if (!$invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)->with('error', '此发票状态不可编辑');
        }

        // 加载关联数据
        $invoice->load(['order.items', 'customer', 'store']);

        return view('invoices.edit', compact('invoice'));
    }

    /**
     * 更新发票
     */
    public function update(Request $request, EInvoice $invoice)
    {
        // 验证发票是否属于当前商店
        if ($invoice->store_id != session('current_store_id')) {
            abort(403, '您无权编辑此发票');
        }

        // 验证发票是否可编辑
        if (!$invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)->with('error', '此发票状态不可编辑');
        }

        // 验证输入数据
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
        ]);

        // 更新发票
        $invoice->invoice_date = $validated['invoice_date'];
        $invoice->due_date = $validated['due_date'];
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)->with('success', '发票更新成功');
    }

    /**
     * 提交发票到MyInvois API
     */
    public function submit(EInvoice $invoice)
    {
        // 验证发票是否属于当前商店
        if ($invoice->store_id != session('current_store_id')) {
            abort(403, '您无权提交此发票');
        }

        // 验证发票是否可提交
        if (!$invoice->isSubmittable()) {
            return redirect()->route('invoices.show', $invoice)->with('error', '此发票状态不可提交');
        }

        // 提交发票
        $result = $this->invoiceService->submitToApi($invoice);

        if ($result['success']) {
            return redirect()->route('invoices.show', $invoice)->with('success', '发票提交成功');
        } else {
            return redirect()->route('invoices.show', $invoice)->with('error', '发票提交失败: ' . $result['message']);
        }
    }

    /**
     * 检查发票状态
     */
    public function checkStatus(EInvoice $invoice)
    {
        // 验证发票是否属于当前商店
        if ($invoice->store_id != session('current_store_id')) {
            abort(403, '您无权检查此发票');
        }

        // 验证发票是否已提交
        if ($invoice->status != 'submitted') {
            return redirect()->route('invoices.show', $invoice)->with('error', '只有已提交的发票才能检查状态');
        }

        // 检查状态
        $result = $this->invoiceService->checkStatus($invoice);

        if ($result['success']) {
            return redirect()->route('invoices.show', $invoice)->with('success', '发票状态已更新');
        } else {
            return redirect()->route('invoices.show', $invoice)->with('error', '发票状态检查失败: ' . $result['message']);
        }
    }

    /**
     * 下载发票PDF
     */
    public function downloadPdf(EInvoice $invoice)
    {
        // 验证发票是否属于当前商店
        if ($invoice->store_id != session('current_store_id')) {
            abort(403, '您无权下载此发票');
        }

        // 验证发票是否有PDF
        if (!$invoice->pdf_path) {
            // 如果没有PDF，尝试生成
            try {
                $path = $this->invoiceService->generatePdf($invoice);
            } catch (\Exception $e) {
                return redirect()->route('invoices.show', $invoice)->with('error', '生成PDF失败: ' . $e->getMessage());
            }
        } else {
            $path = $invoice->pdf_path;
        }

        // 检查文件是否存在
        if (!Storage::disk('public')->exists($path)) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'PDF文件不存在');
        }

        // 下载文件
        $fullPath = storage_path('app/public/' . $path);
        return response()->download($fullPath, 'invoice_' . $invoice->invoice_number . '.pdf');
    }

    /**
     * 从订单创建发票
     */
    public function createFromOrder(SalesOrder $order)
    {
        // 验证订单是否属于当前商店
        if ($order->store_id != session('current_store_id')) {
            abort(403, '您无权为此订单创建发票');
        }

        // 验证订单状态是否为已完成
        if ($order->status != 'completed') {
            return redirect()->route('orders.show', $order)->with('error', '只有已完成的订单才能创建发票');
        }

        // 检查订单是否已有发票
        if ($order->eInvoice) {
            return redirect()->route('invoices.show', $order->eInvoice)->with('info', '此订单已有发票');
        }

        // 创建发票
        $invoice = $this->invoiceService->createFromOrder($order);

        return redirect()->route('invoices.show', $invoice)->with('success', '发票创建成功');
    }
} 