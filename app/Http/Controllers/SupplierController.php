<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\SupplierRequest;
use App\Http\Requests\SupplierProductRequest;
use App\Http\Requests\SupplierAgreementRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\SupplierPriceAgreement;
use App\Models\PriceAgreement;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view suppliers')->only(['index', 'show']);
        $this->middleware('permission:create suppliers')->only(['create', 'store']);
        $this->middleware('permission:edit suppliers')->only(['edit', 'update']);
        $this->middleware('permission:delete suppliers')->only('destroy');
    }

    /**
     * Supplier list
     */
    public function index(): View
    {
        $suppliers = Supplier::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(request('status') !== null, function ($query) {
                $query->where('is_active', request('status'));
            })
            ->with(['contacts'])
            ->latest()
            ->paginate(10)
            ->appends(request()->query());

        return view('suppliers.index', compact('suppliers'));
    }

    // Create a supplier page
    public function create(): View
    {
        return view('suppliers.create');
    }

    // Save supplier
    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validated());

        if ($request->has('contacts')) {
            foreach ($request->input('contacts') as $contact) {
                $supplier->contacts()->create($contact);
            }
        }

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier\'s successful creation');
    }

    // Supplier details
    public function show(Supplier $supplier): View
    {
        Log::info('访问供应商详情页面', [
            'supplier_id' => $supplier->id,
            'user_id' => auth()->id(),
            'auth_check' => auth()->check(),
            'user_email' => auth()->user() ? auth()->user()->email : null,
            'roles' => auth()->user() ? auth()->user()->roles->pluck('name') : [],
            'session_id' => session()->getId(),
            'session_status' => session()->isStarted() ? 'started' : 'not_started',
            'request_ip' => request()->ip(),
            'request_url' => request()->fullUrl(),
            'user_agent' => request()->header('User-Agent')
        ]);
        
        try {
            // Pre -load all related related data
            $supplier->load([
                'contacts',
                'products.category',
                'priceAgreements.product.category'
            ]);

            // Get all the products (for selecting lists)
            $allProducts = Product::with(['category'])
                ->orderBy('name')
                ->get();

            // Get all categories (for screening)
            $categories = \App\Models\ProductCategory::orderBy('name')->get();

            return view('suppliers.show', compact('supplier', 'allProducts', 'categories'));
        } catch (\Exception $e) {
            Log::error('供应商详情页面错误', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    // Editers page
    public function edit(Supplier $supplier): View
    {
        $supplier->load('contacts');
        return view('suppliers.edit', compact('supplier'));
    }

    // Update supplier
    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        // Update contact
        $supplier->contacts()->delete();
        if ($request->has('contacts')) {
            foreach ($request->input('contacts') as $contact) {
                $supplier->contacts()->create($contact);
            }
        }

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier update successfully');
    }

    // Delete supplier
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier delete successfully');
    }

    /**
     * Obtain the product list of suppliers
     */
    public function products(Supplier $supplier): JsonResponse
    {
        try {
            Log::info('Start getting supplier products', [
                'supplier_id' => $supplier->id,
                'user_id' => auth()->id()
            ]);

            $products = $supplier->products()
                ->with(['category', 'priceAgreements' => function($query) {
                    $query->where(function($q) {
                        $q->where('start_date', '<=', now())
                            ->where(function($q) {
                                $q->where('end_date', '>=', now())
                                    ->orWhereNull('end_date');
                            });
                    });
                }])
                ->get()
                ->map(function($product) {
                    // Get the current valid price agreement
                    $activeAgreement = $product->priceAgreements->first();
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'category' => $product->category,
                        'cost_price' => $product->pivot->purchase_price,
                        'selling_price' => $product->selling_price,
                        'tax_rate' => $product->pivot->tax_rate,
                        'supplier_product_code' => $product->pivot->supplier_product_code,
                        'min_order_quantity' => $product->pivot->min_order_quantity,
                        'lead_time' => $product->pivot->lead_time,
                        'remarks' => $product->pivot->remarks,
                        'price_agreement' => $activeAgreement ? [
                            'price' => $activeAgreement->price,
                            'min_quantity' => $activeAgreement->min_quantity,
                            'discount_rate' => $activeAgreement->discount_rate,
                            'start_date' => $activeAgreement->start_date->format('Y-m-d'),
                            'end_date' => $activeAgreement->end_date ? $activeAgreement->end_date->format('Y-m-d') : null
                        ] : null
                    ];
                });

            Log::info('Successfully acquired supplier products', [
                'supplier_id' => $supplier->id,
                'product_count' => $products->count()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to obtain supplier product', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => request()->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get the supplier product list:' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add supplier products
     */
    public function storeProduct(SupplierProductRequest $request, Supplier $supplier)
    {
        try {
            Log::info('Storing product for supplier', [
                'supplier_id' => $supplier->id,
                'request_data' => $request->all()
            ]);

            // Check whether the product exists
            $product = Product::with('category')->find($request->product_id);
            if (!$product) {
                Log::error('Product not found', ['product_id' => $request->product_id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Products do not exist'
                ], 404);
            }

            // Check whether the product has been associated to the supplier
            $exists = $supplier->products()->where('products.id', $request->product_id)->exists();
            if ($exists) {
                Log::warning('Product already associated with supplier', [
                    'supplier_id' => $supplier->id,
                    'product_id' => $request->product_id
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'This product has been added to the supplier'
                ], 409);
            }

            Log::info('Attaching product to supplier', [
                'supplier_id' => $supplier->id,
                'product_id' => $product->id,
                'pivot_data' => [
                    'supplier_product_code' => $request->supplier_product_code,
                    'purchase_price' => $request->purchase_price,
                    'tax_rate' => $request->tax_rate,
                    'min_order_quantity' => $request->min_order_quantity,
                    'lead_time' => $request->lead_time,
                    'remarks' => $request->remarks
                ]
            ]);

            // Add product correlation
            $supplier->products()->attach($product->id, [
                'supplier_product_code' => $request->supplier_product_code,
                'purchase_price' => $request->purchase_price,
                'tax_rate' => $request->tax_rate,
                'min_order_quantity' => $request->min_order_quantity,
                'lead_time' => $request->lead_time,
                'remarks' => $request->remarks
            ]);

            // Update product cost price
            $product->updateCostPrice();

            Log::info('Product attached successfully');

            // Re -obtain product information with related data
            $attachedProduct = $supplier->products()
                ->with('category')
                ->find($product->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully add product',
                'data' => [
                    'id' => $attachedProduct->id,
                    'name' => $attachedProduct->name,
                    'sku' => $attachedProduct->sku,
                    'barcode' => $attachedProduct->barcode,
                    'category' => $attachedProduct->category,
                    'cost_price' => $attachedProduct->pivot->purchase_price,
                    'tax_rate' => $attachedProduct->pivot->tax_rate,
                    'supplier_product_code' => $attachedProduct->pivot->supplier_product_code,
                    'min_order_quantity' => $attachedProduct->pivot->min_order_quantity,
                    'lead_time' => $attachedProduct->pivot->lead_time,
                    'remarks' => $attachedProduct->pivot->remarks,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing product for supplier', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Add product failure: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    /**
     * Update supplier products
     */
    public function updateProduct(SupplierProductRequest $request, Supplier $supplier, $productId): JsonResponse
    {
        try {
            Log::info('Updating supplier product', [
                'supplier_id' => $supplier->id,
                'product_id' => $productId,
                'request_data' => $request->validated()
            ]);

            // Update intermediate table data
            $supplier->products()->updateExistingPivot($productId, [
                'supplier_product_code' => $request->supplier_product_code,
                'purchase_price' => $request->purchase_price,
                'tax_rate' => $request->tax_rate,
                'min_order_quantity' => $request->min_order_quantity ?: 1,
                'lead_time' => $request->lead_time ?: 0,
                'remarks' => $request->remarks
            ]);

            // Update product cost price
            $product = Product::find($productId);
            $product->updateCostPrice();

            // Get the updated data
            $updatedProduct = $supplier->products()
                ->with('category')
                ->find($productId);

            return response()->json([
                'status' => 'success',
                'message' => 'Successful product update',
                'data' => [
                    'id' => $updatedProduct->id,
                    'name' => $updatedProduct->name,
                    'sku' => $updatedProduct->sku,
                    'barcode' => $updatedProduct->barcode,
                    'category' => $updatedProduct->category,
                    'cost_price' => $updatedProduct->pivot->purchase_price,
                    'tax_rate' => $updatedProduct->pivot->tax_rate,
                    'supplier_product_code' => $updatedProduct->pivot->supplier_product_code,
                    'min_order_quantity' => $updatedProduct->pivot->min_order_quantity,
                    'lead_time' => $updatedProduct->pivot->lead_time,
                    'remarks' => $updatedProduct->pivot->remarks,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating supplier product', [
                'supplier_id' => $supplier->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Update product failure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete supplier products
     */
    public function destroyProduct(Supplier $supplier, $productId): JsonResponse
    {
        try {
            Log::info('Detaching product from supplier', [
                'supplier_id' => $supplier->id,
                'product_id' => $productId
            ]);

            // Delete only,Do not delete the product itself
            $supplier->products()->detach($productId);

            // Update product cost price
            $product = Product::find($productId);
            $product->updateCostPrice();

            Log::info('Product detached successfully');

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier product removal success'
            ]);
        } catch (\Exception $e) {
            Log::error('Error detaching product from supplier', [
                'supplier_id' => $supplier->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Removal of supplier products failed'
            ], 500);
        }
    }

    // Supplier price agreement management page
    public function priceAgreements(Supplier $supplier): View
    {
        $supplier->load(['priceAgreements.product', 'products']);
        return view('suppliers.price-agreements', compact('supplier'));
    }

    // Add price protocol
    public function addPriceAgreement(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'min_quantity' => 'nullable|integer|min:1',
            'discount_rate' => 'nullable|numeric|between:0,100',
            'terms' => 'nullable|string',
        ]);

        $supplier->priceAgreements()->create($validated);

        return redirect()
            ->route('suppliers.price-agreements', $supplier)
            ->with('success', 'Price agreement is successfully added');
    }

    // Update price protocol
    public function updatePriceAgreement(Request $request, Supplier $supplier, $agreementId): RedirectResponse
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'min_quantity' => 'nullable|integer|min:1',
            'discount_rate' => 'nullable|numeric|between:0,100',
            'terms' => 'nullable|string',
        ]);

        $supplier->priceAgreements()->findOrFail($agreementId)->update($validated);

        return redirect()
            ->route('suppliers.price-agreements', $supplier)
            ->with('success', 'Price protocol update is successful');
    }

    // Delete price protocol
    public function deletePriceAgreement(Supplier $supplier, $agreementId): RedirectResponse
    {
        $supplier->priceAgreements()->findOrFail($agreementId)->delete();

        return redirect()
            ->route('suppliers.price-agreements', $supplier)
            ->with('success', 'Price protocol delete successfully');
    }

    /**
     * Get the price agreement list of the supplier
     */
    public function agreements(Supplier $supplier): JsonResponse
    {
        $agreements = $supplier->priceAgreements()
            ->with('product')
            ->latest('start_date')
            ->get()
            ->map(function ($agreement) {
                return [
                    'id' => $agreement->id,
                    'product_id' => $agreement->product_id,
                    'product_name' => $agreement->product->name,
                    'price' => $agreement->price,
                    'discounted_price' => $agreement->getDiscountedPrice(),
                    'min_quantity' => $agreement->min_quantity,
                    'discount_rate' => $agreement->discount_rate,
                    'start_date' => $agreement->start_date->format('Y-m-d'),
                    'end_date' => $agreement->end_date ? $agreement->end_date->format('Y-m-d') : null,
                    'terms' => $agreement->terms,
                    'status' => $agreement->getStatus(),
                ];
            });

        return response()->json($agreements);
    }

    /**
     * Add supplier price agreement
     */
    public function storeAgreement(SupplierAgreementRequest $request, Supplier $supplier): JsonResponse
    {
        try {
            Log::info('Start creating a price agreement', [
                'supplier_id' => $supplier->id,
                'request_data' => $request->all(),
                'validated_data' => $request->validated(),
                'headers' => $request->headers->all(),
                'user' => auth()->user()?->id
            ]);

            // Check if the product exists
            $product = Product::find($request->product_id);
            if (!$product) {
                Log::error('The product does not exist', ['product_id' => $request->product_id]);
                return response()->json([
                    'message' => 'The product does not exist',
                    'errors' => ['product_id' => ['The specified product does not exist']]
                ], 422);
            }

            // Check if there is an overlapping protocol
            $existingAgreement = $supplier->priceAgreements()
                ->where('product_id', $request->product_id)
                ->where('min_quantity', $request->min_quantity)
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where(function ($q) use ($request) {
                                $q->where('end_date', '>=', $request->start_date)
                                    ->orWhereNull('end_date');
                            });
                    });
                    if ($request->end_date) {
                        $query->orWhere(function ($q) use ($request) {
                            $q->where('start_date', '<=', $request->end_date)
                                ->where(function ($q) use ($request) {
                                    $q->where('end_date', '>=', $request->end_date)
                                        ->orWhereNull('end_date');
                                });
                        });
                    }
                })
                ->first();

            if ($existingAgreement) {
                Log::warning('There are already overlapping price agreements', [
                    'existing_agreement' => $existingAgreement,
                    'new_request' => $request->validated()
                ]);
                return response()->json([
                    'message' => 'Price agreement of the same order of magnitude already exists within a specified time period',
                    'errors' => [
                        'min_quantity' => ['Price agreement already exists for this order of magnitude']
                    ]
                ], 422);
            }

            Log::info('Verification is passed, ready to create a price agreement', [
                'supplier_id' => $supplier->id,
                'product_id' => $request->product_id,
                'data' => $request->validated()
            ]);

            // Create a protocol
            $agreement = $supplier->priceAgreements()->create($request->validated());

            Log::info('Price agreement was created successfully', [
                'agreement_id' => $agreement->id,
                'agreement_data' => $agreement->toArray()
            ]);

            return response()->json([
                'message' => 'Price agreement added successfully',
                'agreement' => [
                    'id' => $agreement->id,
                    'product_id' => $agreement->product_id,
                    'product_name' => $agreement->product->name,
                    'price' => $agreement->price,
                    'discounted_price' => $agreement->getDiscountedPrice(),
                    'min_quantity' => $agreement->min_quantity,
                    'discount_rate' => $agreement->discount_rate,
                    'start_date' => $agreement->start_date->format('Y-m-d'),
                    'end_date' => $agreement->end_date ? $agreement->end_date->format('Y-m-d') : null,
                    'terms' => $agreement->terms,
                    'status' => $agreement->getStatus(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create a price agreement', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'message' => 'Failed to create a price agreement: ' . $e->getMessage(),
                'errors' => ['general' => ['Creating the price agreement failed, please try again']]
            ], 500);
        }
    }

    /**
     * Update supplier price agreement
     */
    public function updateAgreement(SupplierAgreementRequest $request, Supplier $supplier, $agreementId): JsonResponse
    {
        $agreement = $supplier->priceAgreements()->findOrFail($agreementId);

        // Check if there are the same number of other protocols
        $existingAgreement = $supplier->priceAgreements()
            ->where('product_id', $request->product_id)
            ->where('min_quantity', $request->min_quantity)
            ->where('id', '!=', $agreementId)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_date', '<=', $request->start_date)
                        ->where(function ($q) use ($request) {
                            $q->where('end_date', '>=', $request->start_date)
                                ->orWhereNull('end_date');
                        });
                })->orWhere(function ($q) use ($request) {
                    $q->where('start_date', '<=', $request->end_date)
                        ->where(function ($q) use ($request) {
                            $q->where('end_date', '>=', $request->end_date)
                                ->orWhereNull('end_date');
                        });
                });
            })
            ->first();

        if ($existingAgreement) {
            return response()->json([
                'message' => 'Price agreement of this order of magnitude already exists within a specified time period',
                'errors' => [
                    'min_quantity' => ['Price agreement already exists in this order of magnitude'],
                ]
            ], 422);
        }

        $agreement->update($request->validated());

        return response()->json([
            'message' => 'Price agreement updated successfully',
            'agreement' => [
                'id' => $agreement->id,
                'product_id' => $agreement->product_id,
                'product_name' => $agreement->product->name,
                'price' => $agreement->price,
                'discounted_price' => $agreement->getDiscountedPrice(),
                'min_quantity' => $agreement->min_quantity,
                'discount_rate' => $agreement->discount_rate,
                'start_date' => $agreement->start_date->format('Y-m-d'),
                'end_date' => $agreement->end_date ? $agreement->end_date->format('Y-m-d') : null,
                'terms' => $agreement->terms,
                'status' => $agreement->getStatus(),
            ]
        ]);
    }

    /**
     * Delete the supplier price agreement
     */
    public function destroyAgreement(Supplier $supplier, SupplierPriceAgreement $agreement): JsonResponse
    {
        try {
            // Verify whether the agreement belongs to the supplier
            if ($agreement->supplier_id !== $supplier->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Infernal price protocol'
                ], 403);
            }

            // Delete the protocol directly
            $agreement->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'The price protocol has been deleted'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete the price agreement failed', [
                'supplier_id' => $supplier->id,
                'agreement_id' => $agreement->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Delete the price agreement failed'
            ], 500);
        }
    }

    /**
     * Get the list of suppliers (API)
     */
    public function getSuppliers(): JsonResponse
    {
        try {
            Log::info('Start getting the list of suppliers', [
                'user_id' => auth()->id()
            ]);

            $suppliers = Supplier::where('is_active', true)
                ->select(['id', 'name', 'code', 'contact_person', 'phone'])
                ->orderBy('name')
                ->get();

            Log::info('Successful obtaining supplier list', [
                'count' => $suppliers->count()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            Log::error('Obtaining the list of suppliers failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Obtaining the list of suppliers failed'
            ], 500);
        }
    }

    /**
     * Obtain supplier product price agreement
     */
    public function getPriceAgreement(Supplier $supplier, Product $product): JsonResponse
    {
        $priceAgreement = $supplier->priceAgreements()
            ->where('product_id', $product->id)
            ->where('start_date', '<=', now())
            ->where(function($query) {
                $query->where('end_date', '>=', now())
                    ->orWhereNull('end_date');
            })
            ->first();

        if (!$priceAgreement) {
            return response()->json(null);
        }

        return response()->json([
            'unit_price' => $priceAgreement->unit_price,
            'moq' => $priceAgreement->moq,
            'start_date' => $priceAgreement->start_date,
            'end_date' => $priceAgreement->end_date,
        ]);
    }
} 