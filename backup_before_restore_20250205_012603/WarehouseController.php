<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Warehouse::class, 'warehouse');
    }

    /**
     * Show warehouse list
     */
    public function index(): View
    {
        $warehouses = Warehouse::latest()->paginate(10);

        return view('warehouses.index', compact('warehouses'));
    }

    /**
     * Show the Create Repository Form
     */
    public function create(): View
    {
        return view('warehouses.create');
    }

    /**
     * Save a new warehouse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:50'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $warehouse = Warehouse::create($validated);

        return redirect()
            ->route('warehouses.show', $warehouse)
            ->with('success', 'The warehouse was created successfully.');
    }

    /**
     * Show warehouse details
     */
    public function show(Warehouse $warehouse): View
    {
        return view('warehouses.show', compact('warehouse'));
    }

    /**
     * Show edit warehouse form
     */
    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the warehouse
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code,' . $warehouse->id],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:50'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $warehouse->update($validated);

        return redirect()
            ->route('warehouses.show', $warehouse)
            ->with('success', 'Repository update successfully.');
    }

    /**
     * Delete the repository
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Repository deletion successfully.');
    }
}
