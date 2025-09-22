<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    /**
     * Show customer list
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        // Search criteria
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ic_number', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Member level filter
        if ($memberLevel = $request->input('member_level')) {
            $query->where('member_level', $memberLevel);
        }

        // Recent visits filter
        if ($lastVisit = $request->input('last_visit')) {
            $query->where('last_visit_at', '>=', now()->subDays($lastVisit));
        }

        // Get paginated data
        $customers = $query->with(['prescriptions', 'salesOrders'])
                         ->orderByDesc('last_visit_at')
                         ->paginate(10);

        return view('customers.index', [
            'customers' => $customers,
            'search' => $search,
            'memberLevel' => $memberLevel,
            'lastVisit' => $lastVisit
        ]);
    }

    /**
     * Show Create Customer Form
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Save new customers
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ic_number' => 'required|string|max:20|unique:customers',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'birthday' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'member_level' => 'required|string|in:normal,silver,gold,platinum',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
                        ->with('success', 'Customer creation is successful!');
    }

    /**
     * Show customer details
     */
    public function show(Customer $customer): View
    {
        $customer->load(['prescriptions', 'salesOrders']);
        return view('customers.show', compact('customer'));
    }

    /**
     * Show Edit Customer Form
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer information
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ic_number' => 'required|string|max:20|unique:customers,ic_number,'.$customer->id,
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'birthday' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'member_level' => 'required|string|in:normal,silver,gold,platinum',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
                        ->with('success', 'Customer information updated successfully!');
    }

    /**
     * Delete a customer
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();
        return redirect()->route('customers.index')
                        ->with('success', 'Customer deletion successfully!');
    }
} 