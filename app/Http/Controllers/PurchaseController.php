<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Supplier;
use App\Models\Product;
use App\Http\Requests\PurchaseRequest;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Warehouse;
use App\Models\CompanyProfile;
use App\Enums\PurchaseStatus;
use TCPDF;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseOrderMail;
use App\Models\ProductCategory;
use App\Models\PurchaseSupplierItem;

class PurchaseController extends Controller
{
    private PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
        $this->authorizeResource(Purchase::class, 'purchase');
    }

    /**
     * Show purchase order list
     */
    public function index(): View
    {
        $purchases = Purchase::with(['suppliers', 'warehouse'])
            ->latest()
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Get the warehouse address
     */
    public function getWarehouseAddress(Warehouse $warehouse): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'address' => $warehouse->address
            ]
        ]);
    }

    /**
     * Show Create Purchase Order Form
     */
    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::with(['category', 'suppliers' => function($query) {
            $query->select('suppliers.id', 'suppliers.name')
                ->withPivot('purchase_price', 'tax_rate', 'min_order_quantity', 'lead_time', 'supplier_product_code', 'is_preferred', 'remarks');
        }])
        ->where('is_active', true)
        ->get();
        $warehouses = Warehouse::where('status', true)->get();
        $company = CompanyProfile::first();
        $delivery_addresses = [
            ['id' => 'warehouse', 'name' => 'Warehouse address'],
            ['id' => 'company', 'name' => 'Company Address']
        ];
        $categories = ProductCategory::where('is_active', true)->get();

        return view('purchases.create', compact('suppliers', 'products', 'warehouses', 'company', 'delivery_addresses', 'categories'));
    }

    /**
     * Save new purchase order
     */
    public function store(PurchaseRequest $request): RedirectResponse
    {
        try {
            Log::info('Start processing purchase order creation requests', [
                'request_data' => $request->validated(),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            // Record product data
            Log::info('Purchase order product data:', [
                'items_count' => count($request->input('items', [])),
                'items' => $request->input('items')
            ]);

            // Record supplier data
            Log::info('Supplier-related data:', [
                'shipping_fees' => $request->input('supplier_shipping_fee', []),
                'notes' => $request->input('supplier_notes', [])
            ]);

            $purchase = $this->purchaseService->createPurchase($request->validated());
            
            Log::info('Purchase order creation successfully', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number
            ]);

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order creation successfully');
        } catch (\Exception $e) {
            Log::error('Purchase order creation failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->validated()
            ]);

            return back()->withInput()
                ->with('error', 'Purchase order creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show purchase order details
     */
    public function show(Purchase $purchase): View
    {
        $purchase->load(['suppliers', 'warehouse', 'items.product', 'supplierItems']);
        
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show Edit Purchase Order Form
     */
    public function edit(Purchase $purchase): View
    {
        // Check if the order is editable
        if (!$purchase->isEditable()) {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Purchase orders in the current status cannot be edited');
        }

        // Preload associated data
        $purchase->load(['suppliers', 'warehouse', 'items.product', 'supplierItems']);
        
        // Get a list of optional vendors
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
            
        // Get an optional repository list
        $warehouses = Warehouse::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get all items
        $products = Product::with(['category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'warehouses', 'products'));
    }

    /**
     * Update purchase orders
     */
    public function update(PurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        Log::info('Purchase status:', ['status' => $purchase->purchase_status]);
        if ($purchase->purchase_status !== PurchaseStatus::PENDING) {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Only purchase orders with status to be reviewed can be edited');
        }

        try {
            $purchase = $this->purchaseService->updatePurchase($purchase, $request->validated());
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order update successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Purchase order update failed: ' . $e->getMessage());
        }
    }

    /**
     * Review purchase orders
     */
    public function approve(Purchase $purchase): RedirectResponse
    {
        $this->authorize('approve', $purchase);

        try {
            $this->purchaseService->approvePurchase($purchase);
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order has been approved');
        } catch (\Exception $e) {
            return back()->with('error', 'Purchase order review failed: ' . $e->getMessage());
        }
    }

    /**
     * Purchase order rejection
     */
    public function reject(Purchase $purchase): JsonResponse
    {
        $this->authorize('reject', $purchase);

        try {
            $this->purchaseService->rejectPurchase($purchase);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order rejected successfully',
                'redirect' => route('purchases.show', $purchase)
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Purchase rejection failed: ' . $e->getMessage(), [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'System error, please try again later'
            ], 500);
        }
    }

    /**
     * Cancel purchase order
     */
    public function cancel(Purchase $purchase): RedirectResponse
    {
        $this->purchaseService->cancelPurchase($purchase);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Purchase order cancelled.');
    }

    /**
     * confirm the receipt of goods
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmReceived(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('confirm-received', $purchase);

        try {
            Log::info('Received a confirmation request', [
                'purchase_id' => $purchase->id,
                'received_quantities' => $request->input('received_quantities', []),
                'auto_create_inspection' => $request->has('auto_create_inspection')
            ]);

            $this->purchaseService->confirmReceived(
                $purchase, 
                $request->input('received_quantities', []),
                $request->has('auto_create_inspection')
            );
            
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order has been confirmed and received');
        } catch (\Exception $e) {
            Log::error('Failed to confirm receipt', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to confirm receipt: ' . $e->getMessage());
        }
    }

    /**
     * Export purchase orderPDF
     */
    public function exportPdf(Request $request, Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        try {
            $supplierIds = $request->input('supplier_ids', []);
            $purchase->load(['suppliers', 'warehouse', 'items.product', 'supplierItems' => function($query) use ($supplierIds) {
                if (!empty($supplierIds)) {
                    $query->whereIn('supplier_id', $supplierIds);
                }
            }]);
            
            if (!empty($supplierIds)) {
                $purchase->items = $purchase->items->filter(function($item) use ($supplierIds) {
                    return in_array($item->supplier_id, $supplierIds);
                });
            }

        $company = CompanyProfile::first();
            if (!$company) {
                return back()->with('error', 'Company profile not set. Please set up company information first.');
            }

            // If only one supplier was selected,Use the supplier's information directly
            if (count($supplierIds) === 1) {
                $supplierItem = $purchase->supplierItems->first();
                return $this->generateSupplierPdf($purchase, $supplierItem, $company);
            }

            // If multiple vendors are selected or no vendors are selected,Generate completePDF
            return $this->generateFullPdf($purchase, $company);
        } catch (\Exception $e) {
            Log::error('Failed to generate PDF', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->id,
                'supplier_ids' => $supplierIds ?? null
            ]);
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF for a single supplier
     */
    private function generateSupplierPdf(Purchase $purchase, PurchaseSupplierItem $supplierItem, CompanyProfile $company)
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($company->company_name);
        $pdf->SetTitle('Purchase Order #' . $purchase->purchase_number);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();

        $html = view('purchases.pdf', compact('purchase', 'company', 'supplierItem'))->render();
        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = "PO-{$purchase->purchase_number}-{$supplierItem->supplier->name}.pdf";
        return $pdf->Output($filename, 'D');
    }

    /**
     * Generate PDF for all suppliers
     */
    private function generateFullPdf(Purchase $purchase, CompanyProfile $company)
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($company->company_name);
        $pdf->SetTitle('Purchase Order #' . $purchase->purchase_number);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);

        foreach ($purchase->supplierItems as $supplierItem) {
            $pdf->AddPage();
            $html = view('purchases.pdf', compact('purchase', 'company', 'supplierItem'))->render();
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        $filename = "PO-{$purchase->purchase_number}-All_Suppliers.pdf";
        return $pdf->Output($filename, 'D');
    }

    /**
     * Send purchase order email to supplier
     */
    public function sendToSupplier(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('view', $purchase);

        try {
            $supplierIds = $request->input('supplier_ids', []);
            if (empty($supplierIds)) {
                return back()->with('error', 'Please select at least one supplier');
            }

            $purchase->load(['suppliers', 'warehouse', 'items.product', 'supplierItems']);
            $company = CompanyProfile::first();

            foreach ($supplierIds as $supplierId) {
                $supplierItem = $purchase->supplierItems()
                    ->where('supplier_id', $supplierId)
                    ->firstOrFail();

                // Send an email
                Mail::to($supplierItem->supplier->email)
                    ->send(new PurchaseOrderMail($purchase, $supplierItem, $company));

                // Update send status
                $supplierItem->markEmailSent();
            }

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order has been sent to selected suppliers');
        } catch (\Exception $e) {
            Log::error('Failed to send purchase order email:', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->id,
                'supplier_ids' => $supplierIds ?? null
            ]);
            return back()->with('error', 'Send failed: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        if ($purchase->status !== 'pending') {
            return response()->json(['message' => 'Only purchase orders with pending status can be deleted'], 422);
        }

        $purchase->delete();
        return response()->json(null, 204);
    }
} 