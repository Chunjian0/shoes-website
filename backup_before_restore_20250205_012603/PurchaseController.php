<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Product;
use App\Http\Requests\PurchaseRequest;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Warehouse;
use App\Models\CompanyProfile;
use TCPDF;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseOrderMail;

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
        $purchases = Purchase::with(['supplier', 'warehouse'])
            ->latest()
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show Create Purchase Order Form
     */
    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('status', true)->get();

        return view('purchases.create', compact('suppliers', 'products', 'warehouses'));
    }

    /**
     * Save new purchase order
     */
    public function store(PurchaseRequest $request): RedirectResponse
    {
        try {
            $purchase = $this->purchaseService->createPurchase($request->validated());
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order creation successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Purchase order creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show purchase order details
     */
    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'warehouse', 'items.product']);
        
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show Edit Purchase Order Form
     */
    public function edit(Purchase $purchase): View
    {
        if ($purchase->purchase_status !== 'draft') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Purchase orders that can only be edited for draft status');
        }

        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('status', true)->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'warehouses'));
    }

    /**
     * Update purchase orders
     */
    public function update(PurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        if ($purchase->purchase_status !== 'draft') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Purchase orders that can only be edited for draft status');
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
    public function reject(Purchase $purchase): RedirectResponse
    {
        $this->authorize('reject', $purchase);

        try {
            $this->purchaseService->rejectPurchase($purchase);
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order rejected');
        } catch (\Exception $e) {
            return back()->with('error', 'Purchase order rejection failed: ' . $e->getMessage());
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
     */
    public function confirmReceived(Purchase $purchase): RedirectResponse
    {
        $this->authorize('confirm-received', $purchase);

        try {
            $this->purchaseService->confirmReceived($purchase);
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order has been confirmed and received');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to confirm receipt: ' . $e->getMessage());
        }
    }

    /**
     * Export purchase orderPDF
     */
    public function exportPdf(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        $purchase->load(['supplier', 'warehouse', 'items.product']);
        $company = CompanyProfile::first();

        // create PDF Example
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Setting up document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($company->company_name);
        $pdf->SetTitle('Purchase Order #' . $purchase->purchase_number);

        // Set header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set default monospace fonts
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Set up automatic paging
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set fonts
        $pdf->SetFont('stsongstd-light', '', 10, '', true);

        // Add a page
        $pdf->AddPage();

        // Get the rendered HTML content
        $html = view('purchases.pdf', compact('purchase', 'company'))->render();

        // Output HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        return $pdf->Output("PO-{$purchase->purchase_number}.pdf", 'D');
    }

    /**
     * Send purchase order email to supplier
     */
    public function sendToSupplier(Purchase $purchase): RedirectResponse
    {
        $this->authorize('view', $purchase);

        try {
            $purchase->load(['supplier', 'warehouse', 'items.product']);
            $company = CompanyProfile::first();

            Mail::to($purchase->supplier->email)
                ->send(new PurchaseOrderMail($purchase, $company));

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order has been sent to supplier');
        } catch (\Exception $e) {
            return back()->with('error', 'Send failed: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        if ($purchase->status !== 'draft') {
            return response()->json(['message' => 'Only purchase orders with draft status can be deleted'], 422);
        }

        $purchase->delete();
        return response()->json(null, 204);
    }
} 