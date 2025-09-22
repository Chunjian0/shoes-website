<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.adjustment' => ['nullable', 'numeric'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check the supplier status
            $supplier = \App\Models\Supplier::find($this->input('supplier_id'));
            if (!$supplier->is_active) {
                $validator->errors()->add('supplier_id', 'Supplier has been disabled');
            }

            // Check product status
            $productIds = collect($this->input('items'))->pluck('product_id');
            $disabledProducts = \App\Models\Product::whereIn('id', $productIds)
                ->where('is_active', false)
                ->get();

            if ($disabledProducts->isNotEmpty()) {
                $names = $disabledProducts->pluck('name')->join(', ');
                $validator->errors()->add('items', "The following products have been disabled: {$names}");
            }

            // Check minimum order quantity
            $items = collect($this->input('items'));
            $products = \App\Models\Product::whereIn('id', $productIds)
                ->with(['suppliers' => function ($query) use ($supplier) {
                    $query->where('supplier_id', $supplier->id);
                }])
                ->get();

            foreach ($items as $index => $item) {
                $product = $products->firstWhere('id', $item['product_id']);
                if ($product && $product->suppliers->isNotEmpty()) {
                    $minOrderQuantity = $product->suppliers->first()->pivot->min_order_quantity;
                    if ($minOrderQuantity && $item['quantity'] < $minOrderQuantity) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "merchandise {$product->name} The minimum order quantity is {$minOrderQuantity}"
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Please select a supplier',
            'supplier_id.exists' => 'The selected supplier does not exist',
            'order_date.required' => 'Please select the order date',
            'order_date.date' => 'The order date format is incorrect',
            'expected_date.date' => 'The expected arrival date format is incorrect',
            'expected_date.after_or_equal' => 'The estimated arrival date cannot be earlier than the order date',
            'shipping_fee.numeric' => 'Shipping must be number',
            'shipping_fee.min' => 'The shipping fee cannot be less than 0',
            'items.required' => 'Please add purchases',
            'items.array' => 'The purchase product format is incorrect',
            'items.min' => 'At least one product needs to be added',
            'items.*.product_id.required' => 'Please select a product',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.quantity.required' => 'Please enter quantity',
            'items.*.quantity.integer' => 'The quantity must be an integer',
            'items.*.quantity.min' => 'The quantity cannot be less than1',
            'items.*.unit_price.required' => 'Please enter the unit price',
            'items.*.unit_price.numeric' => 'The unit price must be a number',
            'items.*.unit_price.min' => 'The unit price cannot be less than 0',
            'items.*.tax_rate.numeric' => 'The tax rate must be a number',
            'items.*.tax_rate.min' => 'The tax rate cannot be less than 0',
            'items.*.adjustment.numeric' => 'The amount of adjustment must be a number',
        ];
    }
} 