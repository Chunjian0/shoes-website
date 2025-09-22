<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ProductCategory::withCount('products')->paginate(10);
        return view('product-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Verification request data
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

            // Create a product classification
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
                ->with('success', 'The creation of product classification is successful!');

        } catch (\Exception $e) {
            Log::error('Failure to create a product classification:' . $e->getMessage());
            Log::error('Error stack:' . $e->getTraceAsString());
            return back()
                ->withInput()
                ->with('error', 'Failure to create a product classification, please try it out!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $category)
    {
        return view('product-categories.show', compact('category'));
    }

    /**
     * Show the form of editing product classification
     */
    public function edit(ProductCategory $category): View
    {
        $category->load('parameters');
        
        // Processing the verification rules of the parameters
        $parameters = $category->parameters->map(function ($parameter) {
            $rules = json_decode($parameter->validation_rules, true) ?? [];
            
            // Extracted from the verification rulesminandmaxvalue
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
        
        return view('product-categories.edit', compact('category', 'parameters'));
    }

    /**
     * Update product classification
     */
    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        try {
            // Verification request data
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

            // Update basic information of product classification
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
                ->with('success', 'Product classification updates successfully!');

        } catch (\Exception $e) {
            Log::error('Update product classification failed:' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Update the product classification failed, please try it out!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $category)
    {
        try {
            // Check if there are related products
            if ($category->products()->exists()) {
                return back()->with('error', 'Cannot delete a classification that has products!');
            }
            
            // Delete parameters first
            $category->parameters()->delete();
            
            // Delete the classification itself
            $category->delete();
            
            return redirect()
                ->route('product-categories.index')
                ->with('success', 'The deletion of product classification is successful!');
        } catch (\Exception $e) {
            Log::error('Failed to delete product classification: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete product classification, please try again!');
        }
    }

    /**
     * Get parameters for the given category as JSON
     */
    public function parameters(ProductCategory $category)
    {
        $parameters = $category->parameters()
            ->where('is_active', true)
            ->get()
            ->map(function ($param) {
                return [
                    'id' => $param->id,
                    'name' => $param->name,
                    'code' => $param->code,
                    'type' => $param->type,
                    'is_required' => (bool) $param->is_required,
                    'options' => $param->options,
                    'validation_rules' => $param->validation_rules ? json_decode($param->validation_rules) : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $parameters
        ]);
    }
}
