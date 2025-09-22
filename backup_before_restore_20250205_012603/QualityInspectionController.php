<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\QualityInspection;
use App\Http\Requests\QualityInspectionRequest;
use App\Services\QualityInspectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
        $purchases = Purchase::whereIn('purchase_status', ['received', 'partially_received'])
            ->whereNull('inspection_status')
            ->orderBy('received_at', 'desc')
            ->get();

        return view('quality-inspections.create', compact('purchases'));
    }

    /**
     * Save new quality inspections
     */
    public function store(QualityInspectionRequest $request): RedirectResponse
    {
        $inspection = $this->qualityInspectionService->createInspection($request->validated());

        return redirect()
            ->route('quality-inspections.show', $inspection)
            ->with('success', 'Quality inspection record has been created. ');
    }

    /**
     * Show quality inspection details
     */
    public function show(QualityInspection $qualityInspection): View
    {
        $qualityInspection->load(['purchase', 'inspector', 'items.purchaseItem.product']);

        return view('quality-inspections.show', compact('qualityInspection'));
    }

    /**
     * Display edit quality inspection form
     */
    public function edit(QualityInspection $qualityInspection): View
    {
        $qualityInspection->load(['items.purchaseItem.product']);

        return view('quality-inspections.edit', compact('qualityInspection'));
    }

    /**
     * Update quality inspection
     */
    public function update(QualityInspectionRequest $request, QualityInspection $qualityInspection): RedirectResponse
    {
        $this->qualityInspectionService->updateInspection($qualityInspection, $request->validated());

        return redirect()
            ->route('quality-inspections.show', $qualityInspection)
            ->with('success', 'Quality inspection records have been updated.');
    }

    /**
     * Passed the quality inspection
     */
    public function approve(QualityInspection $qualityInspection): RedirectResponse
    {
        $this->authorize('approve', $qualityInspection);

        $this->qualityInspectionService->approveInspection($qualityInspection);

        return back()->with('success', 'Quality inspection has been passed.');
    }

    /**
     * Reject quality inspection
     */
    public function reject(QualityInspection $qualityInspection): RedirectResponse
    {
        $this->authorize('reject', $qualityInspection);

        $this->qualityInspectionService->rejectInspection($qualityInspection);

        return back()->with('success', 'Quality inspection has been rejected.');
    }
} 