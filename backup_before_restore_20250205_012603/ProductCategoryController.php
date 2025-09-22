<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ProductCategoryController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view products')->only(['index', 'show']);
        $this->middleware('permission:create products')->only(['create', 'store']);
        $this->middleware('permission:edit products')->only(['edit', 'update']);
        $this->middleware('permission:delete products')->only('destroy');
    }

    /**
     * Show product category list
     */
    public function index(Request $request): View
    {
        $query = ProductCategory::query()
            ->withCount('products')
            ->orderBy('id');

        // Search criteria
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status'));
        }

        $categories = $query->paginate(10);

        return view('product-categories.index', compact('categories'));
    }

    /**
     * Show the form to create a product category
     */
    public function create(): View
    {
        return view('product-categories.create');
    }

    /**
     * Save newly created product categories
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Verify request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'parameters' => 'nullable|array',
                'parameters.*.name' => 'required|string|max:255',
                'parameters.*.type' => 'required|string|in:text,number,select,radio,checkbox',
                'parameters.*.validation_rules' => 'nullable|array',
                'parameters.*.validation_rules.*' => 'nullable|string',
                'parameters.*.min_length' => 'nullable|integer|min:0',
                'parameters.*.max_length' => 'nullable|integer|min:0',
                'parameters.*.options' => 'nullable|array',
                'parameters.*.options.*' => 'nullable|string|max:255',
                'parameters.*.is_required' => 'boolean',
            ]);

            // Create a product category
            $category = new ProductCategory();
            $category->fill([
                'name' => $validated['name'],
                'code' => strtolower(str_replace(' ', '-', $validated['name'])) . '-' . uniqid(),
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $category->save();

            // Create classification parameters
            if ($request->has('parameters')) {
                foreach ($request->input('parameters') as $index => $paramData) {
                    // Build verification rules
                    $rules = $paramData['validation_rules'] ?? [];
                    
                    // Add length verification rules
                    if (!empty($paramData['min_length'])) {
                        $rules[] = 'min:' . $paramData['min_length'];
                    }
                    if (!empty($paramData['max_length'])) {
                        $rules[] = 'max:' . $paramData['max_length'];
                    }

                    $parameter = $category->parameters()->create([
                        'name' => $paramData['name'],
                        'code' => strtolower(str_replace(' ', '-', $paramData['name'])) . '-' . uniqid(),
                        'type' => $paramData['type'],
                        'is_required' => (bool) ($paramData['is_required'] ?? false),
                        'options' => in_array($paramData['type'], ['select', 'radio', 'checkbox']) ? $paramData['options'] : null,
                        'validation_rules' => !empty($rules) ? json_encode($rules) : null,
                        'is_searchable' => true,
                        'is_active' => true,
                    ]);
                }
            }

            return redirect()
                ->route('product-categories.index')
                ->with('success', 'Product classification was successfully created!');

        } catch (\Exception $e) {
            Log::error('Failed to create a product category:' . $e->getMessage());
            Log::error('Error stack:' . $e->getTraceAsString());
            return back()
                ->withInput()
                ->with('error', 'Creating the product category failed, please try again!');
        }
    }

    /**
     * Show product category details
     */
    public function show(ProductCategory $category): View
    {
        return view('product-categories.show', compact('category'));
    }

    /**
     * Display the form to edit product categories
     */
    public function edit(ProductCategory $category): View
    {
        $category->load('parameters');
        
        // Verification rules for handling parameters
        $category->parameters = $category->parameters->map(function ($parameter) {
            $rules = json_decode($parameter->validation_rules, true) ?? [];
            
            // Extract from the verification ruleminandmaxvalue
            foreach ($rules as $rule) {
                if (preg_match('/^max:(\d+)$/', $rule, $matches)) {
                    $parameter->max_length = (int) $matches[1];
                }
                if (preg_match('/^min:(\d+)$/', $rule, $matches)) {
                    $parameter->min_length = (int) $matches[1];
                }
            }
            
            return $parameter;
        });
        
        return view('product-categories.edit', compact('category'));
    }

    /**
     * Update product classification
     */
    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        try {
            // Verify request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'parameters' => 'nullable|array',
                'parameters.*.name' => 'required|string|max:255',
                'parameters.*.type' => 'required|string|in:text,number,select,radio,checkbox',
                'parameters.*.min_length' => 'nullable|integer|min:0',
                'parameters.*.max_length' => 'nullable|integer|min:0',
                'parameters.*.options' => 'nullable|array',
                'parameters.*.options.*' => 'nullable|string|max:255',
                'parameters.*.is_required' => 'boolean',
            ]);

            // Update basic information on product classification
            $category->fill([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $category->save();

            // Delete old parameters
            $category->parameters()->delete();

            // Create new parameters
            if ($request->has('parameters')) {
                foreach ($request->input('parameters') as $paramData) {
                    // Build verification rules
                    $rules = [];
                    
                    // Add length verification rules
                    if (!empty($paramData['min_length'])) {
                        $rules[] = 'min:' . $paramData['min_length'];
                    }
                    if (!empty($paramData['max_length'])) {
                        $rules[] = 'max:' . $paramData['max_length'];
                    }

                    $parameter = $category->parameters()->create([
                        'name' => $paramData['name'],
                        'code' => strtolower(str_replace(' ', '-', $paramData['name'])) . '-' . uniqid(),
                        'type' => $paramData['type'],
                        'is_required' => (bool) ($paramData['is_required'] ?? false),
                        'options' => in_array($paramData['type'], ['select', 'radio', 'checkbox']) ? $paramData['options'] : null,
                        'validation_rules' => !empty($rules) ? json_encode($rules) : null,
                        'is_searchable' => true,
                        'is_active' => true,
                    ]);
                }
            }

            return redirect()
                ->route('product-categories.index')
                ->with('success', 'Product classification update was successful!');

        } catch (\Exception $e) {
            Log::error('Failed to update the product category:' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'The product classification has failed, please try again!');
        }
    }

    /**
     * Delete product categories
     */
    public function destroy(ProductCategory $category): RedirectResponse
    {
        try {
            // Check if the product is related
            if ($category->products()->exists()) {
                return back()->with('error', 'Categories containing products cannot be deleted!');
            }

            $category->delete();

            return redirect()
                ->route('product-categories.index')
                ->with('success', 'Product classification was deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete the product classification:' . $e->getMessage());
            return back()->with('error', 'The product classification failed to be deleted, please try again!');
        }
    }

    /**
     * Get classification parameters
     */
    public function parameters(ProductCategory $category): JsonResponse
    {
        try {
            $parameters = $category->parameters()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($parameter) {
                    return [
                        'id' => $parameter->id,
                        'name' => $parameter->name,
                        'code' => $parameter->code,
                        'type' => $parameter->type,
                        'is_required' => $parameter->is_required,
                        'validation_rules' => $parameter->validation_rules ? json_decode($parameter->validation_rules, true) : [],
                        'options' => $parameter->options,
                        'sort_order' => $parameter->sort_order
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $parameters
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to obtain classification parameters:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get classification parameters'
            ], 500);
        }
    }

    /**
     * Get all product categories
     */
    public function all(): JsonResponse
    {
        $categories = ProductCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get the product category list (Supplier moduleAPIuse)
     */
    public function getCategories(Request $request): JsonResponse
    {
        try {
            $query = ProductCategory::query()
                ->where('is_active', true)
                ->orderBy('name');

            // Search criteria
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $categories = $query->get(['id', 'name', 'code']);

            return response()->json([
                'status' => 'success',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to obtain product classification list:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get product category list'
            ], 500);
        }
    }
} 