<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\QualityInspection;
use App\Http\Requests\QualityInspectionRequest;
use App\Services\QualityInspectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Enums\PurchaseStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QualityInspectionController extends Controller
{
    public function __construct(
        private readonly QualityInspectionService $qualityInspectionService
    ) {
        $this->authorizeResource(QualityInspection::class, 'quality_inspection');
    }

    /**
     * Display the quality inspection list
     */
    public function index(): View
    {
        $inspections = QualityInspection::with(['purchase', 'inspector'])
            ->latest()
            ->paginate(10);

        return view('quality-inspections.index', compact('inspections'));
    }

    /**
     * Display Create Quality Inspection Form
     */
    public function create(): View
    {
        // Get a received but uninspected purchase order
        $purchases = Purchase::with(['supplierItems.supplier'])
            ->whereIn('purchase_status', [
                PurchaseStatus::RECEIVED->value,
                PurchaseStatus::PARTIALLY_RECEIVED->value
            ])
            ->whereNull('inspection_status')
            ->orderBy('received_at', 'desc')
            ->get();

        return view('quality-inspections.create', compact('purchases'));
    }

    /**
     * Items to obtain purchase orders
     */
    public function getPurchaseItems(Purchase $purchase): JsonResponse
    {
        try {
            Log::info('Start getting purchase order items', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'purchase_status' => $purchase->purchase_status,
                'items_count' => $purchase->items->count()
            ]);

            // Get product table structure information
            $columns = DB::select('SHOW COLUMNS FROM products');
            Log::info('Products Table structure:', ['columns' => $columns]);

        $items = $purchase->items()
                ->with(['product' => function($query) {
                    Log::info('Loading product information', [
                        'query_sql' => $query->toSql(),
                        'query_bindings' => $query->getBindings()
                    ]);
                    // Modify the query and select only fields that are determined to exist.
                    $query->select(['id', 'name', 'sku']);
                }])
                ->get();

            Log::info('Acquisition of purchase order items successfully', [
                'items_count' => $items->count(),
                'first_item_sample' => $items->first() ? [
                    'id' => $items->first()->id,
                    'product_id' => $items->first()->product_id,
                    'product' => $items->first()->product ? [
                        'id' => $items->first()->product->id,
                        'name' => $items->first()->product->name,
                        'sku' => $items->first()->product->sku
                    ] : null,
                    'quantity' => $items->first()->quantity
                ] : null
            ]);

            $mappedItems = $items->map(function ($item) {
                try {
                    if (!$item->product) {
                        Log::warning('The project lacks product information', [
                            'item_id' => $item->id,
                            'product_id' => $item->product_id
                        ]);
                        return null;
                    }

                    $data = [
                    'id' => $item->id,
                        'product_name' => $item->product->name ?? 'Unknown products',
                        'product_sku' => $item->product->sku ?? 'unknownSKU',
                    'quantity' => $item->quantity,
                        'received_quantity' => $item->received_quantity ?? 0,
                    'remaining_quantity' => $item->quantity - ($item->inspected_quantity ?? 0),
                ];

                    Log::debug('Project data mapping', [
                        'item_id' => $item->id,
                        'mapped_data' => $data
                    ]);

                    return $data;
                } catch (\Exception $e) {
                    Log::error('An error occurred while processing a single project', [
                        'item_id' => $item->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                }
            })
            ->filter()
            ->values();

            Log::info('Data mapping is completed', [
                'mapped_items_count' => $mappedItems->count(),
                'sample_item' => $mappedItems->first()
            ]);

            return response()->json($mappedItems);

        } catch (\Exception $e) {
            Log::error('Failed to obtain purchase order item', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'sql_queries' => DB::getQueryLog() // Get all executedSQLQuery
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Failed to obtain purchase order item',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Save new quality inspections
     */
    public function store(QualityInspectionRequest $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            // 详细记录请求内容
            Log::info('Quality inspection creation request received', [
                'request_data' => $request->validated(),
                'request_all' => $request->all(),
                'files' => $request->hasFile('files') ? 'Files attached' : 'No files',
                'temp_id' => $request->temp_id ?? 'No temp_id',
                'items_count' => isset($request->items) ? count($request->items) : 0,
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);
            
            $inspection = $this->qualityInspectionService->createInspection($request->validated());
            
            // 记录创建结果
            Log::info('Quality inspection created successfully', [
                'inspection_id' => $inspection->id,
                'inspection_number' => $inspection->inspection_number,
            ]);
            
            // 关联临时媒体文件（如果存在）
            if ($request->filled('temp_id')) {
                $this->associateMedia($request->temp_id, $inspection);
                Log::info('Media files associated with inspection', [
                    'temp_id' => $request->temp_id,
                    'inspection_id' => $inspection->id,
                ]);
            }
            
            // 根据请求类型返回不同的响应
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Quality inspection record has been created successfully.',
                    'redirect' => route('quality-inspections.show', $inspection)
                ]);
            }
            
            return redirect()
                ->route('quality-inspections.show', $inspection)
                ->with('success', 'Quality inspection record has been created.');
        } catch (\Exception $e) {
            Log::error('Failed to create quality inspection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->validated(),
                'request_all' => $request->all(),
            ]);

            // 根据请求类型返回不同的响应
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create a quality inspection record: ' . $e->getMessage()
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to create a quality inspection record: ' . $e->getMessage());
        }
    }

    /**
     * 关联临时上传的媒体文件到检验记录
     */
    private function associateMedia(string $tempId, QualityInspection $inspection): void
    {
        try {
            $response = app(MediaController::class)->associate(new Request([
                'temp_id' => $tempId,
                'model_type' => 'quality_inspections',
                'model_id' => $inspection->id,
            ]));

            if ($response->getStatusCode() !== 200) {
                Log::warning('Media association failed', [
                    'temp_id' => $tempId,
                    'inspection_id' => $inspection->id,
                    'response' => $response->getData(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Media association exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Show quality inspection details
     */
    public function show(QualityInspection $qualityInspection): View
    {
        $qualityInspection->load(['purchase', 'inspector', 'items.purchaseItem.product', 'media']);

        return view('quality-inspections.show', compact('qualityInspection'));
    }

    /**
     * Display edit quality inspection form
     */
    public function edit(QualityInspection $qualityInspection): View
    {
        $qualityInspection->load(['purchase', 'items.purchaseItem.product', 'media']);
        
        return view('quality-inspections.edit', compact('qualityInspection'));
    }

    /**
     * Update quality inspection
     */
    public function update(QualityInspectionRequest $request, QualityInspection $qualityInspection): RedirectResponse
    {
        try {
            $this->qualityInspectionService->updateInspection($qualityInspection, $request->validated());
            return redirect()
                ->route('quality-inspections.show', $qualityInspection)
                ->with('success', 'Quality inspection records have been updated.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update quality inspection records: ' . $e->getMessage());
        }
    }

    /**
     * Passed the quality inspection
     */
    public function approve(QualityInspection $qualityInspection): RedirectResponse
    {
        try {
            $this->qualityInspectionService->approveInspection($qualityInspection);
            return redirect()
                ->route('quality-inspections.show', $qualityInspection)
                ->with('success', 'Quality inspection has been passed.');
        } catch (\Exception $e) {
            return back()->with('error', 'Review failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject quality inspection
     */
    public function reject(QualityInspection $qualityInspection): RedirectResponse
    {
        try {
            $this->qualityInspectionService->rejectInspection($qualityInspection);
            return redirect()
                ->route('quality-inspections.show', $qualityInspection)
                ->with('success', 'Quality inspection has been rejected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }
} 