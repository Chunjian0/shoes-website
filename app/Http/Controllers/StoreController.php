<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class StoreController extends Controller
{
    /**
     * Create a new store
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:50'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Create a store
        $store = Warehouse::create([
            ...$validated,
            'is_store' => true,
            'status' => true,
        ]);

        // Save store information tosession
        session([
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);
        
        // Clear the display modal boxsession
        session()->forget('show_create_store_modal');

        return redirect()
            ->route('dashboard')
            ->with('success', "Shop {$store->name} Created successfully");
    }

    /**
     * Switch the current store
     *
     * @param  int  $storeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch($storeId)
    {
        $store = Warehouse::where('is_store', true)
            ->where('status', true)
            ->findOrFail($storeId);

        // Store store information in session middle
        session(['store_id' => $store->id]);
        session(['store_name' => $store->name]);

        return redirect()->back()->with('success', 'Switched to the store:' . $store->name);
    }

    /**
     * Get current store information
     */
    public function current(): Warehouse
    {
        return Warehouse::where('id', session('store_id'))
            ->where('is_store', true)
            ->where('status', true)
            ->firstOrFail();
    }
}
