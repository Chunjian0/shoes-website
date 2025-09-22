<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\CategoryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryParameterController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view products')->only('index');
        $this->middleware('permission:edit products')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display parameter list
     */
    public function index(ProductCategory $category): View
    {
        $parameters = $category->parameters()
            ->orderBy('sort_order')
            ->paginate(10);

        return view('category-parameters.index', compact('category', 'parameters'));
    }

    /**
     * Display the form to create parameters
     */
    public function create(ProductCategory $category): View
    {
        return view('category-parameters.create', compact('category'));
    }

    /**
     * Save newly created parameters
     */
    public function store(Request $request, ProductCategory $category): RedirectResponse
    {
        try {
            // Verify request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:category_parameters,code,NULL,id,category_id,' . $category->id,
                'type' => 'required|string|in:text,number,select,radio,checkbox',
                'validation_rules' => 'nullable|string|max:255',
                'default_value' => 'nullable|string|max:255',
                'options' => 'nullable|array',
                'options.*' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer',
                'description' => 'nullable|string|max:1000',
                'is_required' => 'boolean',
                'is_searchable' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Create parameters
            $parameter = new CategoryParameter();
            $parameter->fill($validated);
            $parameter->category_id = $category->id;
            $parameter->is_required = $request->boolean('is_required');
            $parameter->is_searchable = $request->boolean('is_searchable');
            $parameter->is_active = $request->boolean('is_active', true);

            // Processing options
            if (in_array($parameter->type, ['select', 'radio', 'checkbox']) && $request->has('options')) {
                $parameter->options = array_values(array_filter($request->input('options', [])));
            }

            $parameter->save();

            return redirect()
                ->route('category-parameters.index', $category)
                ->with('success', 'Parameter creation was successful!');

        } catch (\Exception $e) {
            Log::error('Failed to create parameters:' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'The parameter creation failed, please try again!');
        }
    }

    /**
     * A form that displays edit parameters
     */
    public function edit(ProductCategory $category, CategoryParameter $parameter): View
    {
        return view('category-parameters.edit', compact('category', 'parameter'));
    }

    /**
     * Update parameters
     */
    public function update(Request $request, ProductCategory $category, CategoryParameter $parameter): RedirectResponse
    {
        try {
            // Verify request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:category_parameters,code,' . $parameter->id . ',id,category_id,' . $category->id,
                'type' => 'required|string|in:text,number,select,radio,checkbox',
                'validation_rules' => 'nullable|string|max:255',
                'default_value' => 'nullable|string|max:255',
                'options' => 'nullable|array',
                'options.*' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer',
                'description' => 'nullable|string|max:1000',
                'is_required' => 'boolean',
                'is_searchable' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Update parameters
            $parameter->fill($validated);
            $parameter->is_required = $request->boolean('is_required');
            $parameter->is_searchable = $request->boolean('is_searchable');
            $parameter->is_active = $request->boolean('is_active', true);

            // Processing options
            if (in_array($parameter->type, ['select', 'radio', 'checkbox']) && $request->has('options')) {
                $parameter->options = array_values(array_filter($request->input('options', [])));
            } elseif (!in_array($parameter->type, ['select', 'radio', 'checkbox'])) {
                $parameter->options = null;
            }

            $parameter->save();

            return redirect()
                ->route('category-parameters.index', $category)
                ->with('success', 'Parameter update was successful!');

        } catch (\Exception $e) {
            Log::error('Update parameters failed:' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Update parameters failed, please try again!');
        }
    }

    /**
     * Delete parameters
     */
    public function destroy(ProductCategory $category, CategoryParameter $parameter): RedirectResponse
    {
        try {
            $parameter->delete();

            return redirect()
                ->route('category-parameters.index', $category)
                ->with('success', 'Parameter deletion was successful!');

        } catch (\Exception $e) {
            Log::error('Failed to delete parameters:' . $e->getMessage());
            return back()->with('error', 'Deletion of parameters failed, please try again!');
        }
    }
} 