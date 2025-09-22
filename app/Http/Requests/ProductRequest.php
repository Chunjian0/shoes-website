<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProductCategory;
use Illuminate\Support\Str;
use App\Models\Product;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data before verification
     */
    protected function prepareForValidation()
    {
        // if SKU is empty, generate a new one
        if (empty($this->input('sku'))) {
            $this->merge([
                'sku' => $this->generateSKU()
            ]);
        }

        // If the barcode is empty, a new one is generated
        if (empty($this->input('barcode'))) {
            $this->merge([
                'barcode' => $this->generateBarcode()
            ]);
        }
    }

    /**
     * Get verification rules
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products')->ignore($this->product)
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products')->ignore($this->product)
            ],
            'category_id' => ['required', 'exists:product_categories,id'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'selling_price' => ['required', 'numeric', 'min:0.01'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'specifications' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['boolean'],
            'parameters' => ['nullable', 'array'],
            'parameters.*' => ['nullable'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_start_date' => ['nullable', 'date'],
            'discount_end_date' => ['nullable', 'date', 'after_or_equal:discount_start_date'],
            'min_quantity_for_discount' => ['nullable', 'integer', 'min:0'],
            'max_quantity_for_discount' => ['nullable', 'integer', 'min:0'],
        ];

        // If a classification is selected, add a validation rule for parameter values
        if ($this->filled('category_id')) {
            $category = ProductCategory::findOrFail($this->input('category_id'));
            $parameters = $category->parameters()->where('is_active', true)->get();

            foreach ($parameters as $parameter) {
                $paramRules = ['nullable'];

                // If the parameters are required
                if ($parameter->is_required) {
                    $paramRules = ['required'];
                }

                // Add validation rules according to parameter type
                switch ($parameter->type) {
                    case 'number':
                        $paramRules[] = 'numeric';
                        break;
                    case 'text':
                        $paramRules[] = 'string';
                        $paramRules[] = 'max:255';
                        break;
                    case 'select':
                    case 'radio':
                        $paramRules[] = Rule::in($parameter->options ?? []);
                        break;
                    case 'checkbox':
                        $paramRules = ['nullable', 'array'];
                        $rules["parameters.{$parameter->code}.*"] = ['required', Rule::in($parameter->options ?? [])];
                        break;
                }

                // Add custom validation rules
                if ($parameter->validation_rules) {
                    $customRules = json_decode($parameter->validation_rules, true);
                    if (is_array($customRules)) {
                        $paramRules = array_merge($paramRules, $customRules);
                    }
                }

                // Use parameter code as key
                $rules["parameters.{$parameter->code}"] = $paramRules;
            }
        }

        return $rules;
    }

    /**
     * Generate unique SKU
     */
    protected function generateSKU(): string
    {
        $prefix = 'SKU';
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        $sku = $prefix . $timestamp . $random;

        // make sure SKU It's the only one
        while (Product::where('sku', $sku)->exists()) {
            $random = strtoupper(Str::random(4));
            $sku = $prefix . $timestamp . $random;
        }

        return $sku;
    }

    /**
     * Generate unique barcodes
     */
    protected function generateBarcode(): string
    {
        $prefix = '69'; // Custom prefixes
        $timestamp = now()->format('ymdHi');
        $random = str_pad((string)mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        $barcode = $prefix . $timestamp . $random;

        // Make sure the barcode is unique
        while (Product::where('barcode', $barcode)->exists()) {
            $random = str_pad((string)mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
            $barcode = $prefix . $timestamp . $random;
        }

        return $barcode;
    }

    /**
     * Get custom properties for verification errors
     */
    public function attributes(): array
    {
        return [
            'name' => 'Product Name',
            'sku' => 'SKU',
            'barcode' => 'Barcode',
            'category_id' => 'Product Category',
            'selling_price' => 'Sales price',
            'parameters' => 'Product parameters',
            'parameters.*' => 'Product parameters',
            'description' => 'Product Description',
            'is_active' => 'Whether to enable',
            'images' => 'Product pictures',
            'images.*' => 'Product pictures'
        ];
    }

    /**
     * Get a custom message for verification errors
     */
    public function messages(): array
    {
        $messages = [
            'category_id.required' => 'Product category cannot be empty',
            'category_id.exists' => 'Product category does not exist',
            'name.required' => 'Product name cannot be empty',
            'sku.required' => 'SKU cannot be empty',
            'sku.unique' => 'SKU already exists',
            'selling_price.required' => 'The sales price cannot be empty',
            'selling_price.min' => 'The sales price must be greater than 0',
            'selling_price.numeric' => 'The sales price must be a number',
        ];

        // If the classification is selected, add a verification message for parameter values
        if ($this->filled('category_id')) {
            $category = ProductCategory::findOrFail($this->input('category_id'));
            $parameters = $category->parameters()->where('is_active', true)->get();

            foreach ($parameters as $parameter) {
                if ($parameter->is_required) {
                    $messages["parameters.{$parameter->code}.required"] = "{$parameter->name} cannot be empty";
                }

                switch ($parameter->type) {
                    case 'number':
                        $messages["parameters.{$parameter->code}.numeric"] = "{$parameter->name} must be a number";
                        break;
                    case 'select':
                    case 'radio':
                        $messages["parameters.{$parameter->code}.in"] = "Selected {$parameter->name} is invalid";
                        break;
                    case 'checkbox':
                        $messages["parameters.{$parameter->code}.array"] = "{$parameter->name} must be an array";
                        $messages["parameters.{$parameter->code}.*.in"] = "Selected {$parameter->name} has invalid option";
                        break;
                }
            }
        }

        return $messages;
    }
} 