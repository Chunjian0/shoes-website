<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementRequest extends FormRequest
{
    /**
     * Determine whether the user has the right to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get verification rules
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'movement_type' => ['required', Rule::in([
                'purchase_received',
                'sales_shipped',
                'stock_adjustment',
                'stock_transfer',
                'stock_return',
            ])],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get verification error message
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select the product',
            'product_id.exists' => 'The selected product does not exist',
            'warehouse_id.required' => 'Please select the warehouse',
            'warehouse_id.exists' => 'The selected warehouse does not exist',
            'movement_type.required' => 'Please select the mobile type',
            'movement_type.in' => 'The movement type is incorrect',
            'quantity.required' => 'Please enter the quantity',
            'quantity.numeric' => 'The quantity must be numbers',
            'quantity.min' => 'The quantity must be greater than0',
            'unit_cost.required' => 'Please enter the unit cost',
            'unit_cost.numeric' => 'The cost of the unit must be numbers',
            'unit_cost.min' => 'The cost of the unit cannot be less than0',
            'batch_number.max' => 'The batch number cannot exceed50Individual character',
            'notes.max' => 'Remarks cannot be exceeded1000Individual character',
        ];
    }

    /**
     * Configuration verification instance
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check the status of the product
            $product = \App\Models\Product::find($this->input('product_id'));
            if ($product && !$product->status) {
                $validator->errors()->add('product_id', 'The selected product has been disabled');
            }

            // Check the warehouse status
            $warehouse = \App\Models\Warehouse::find($this->input('warehouse_id'));
            if ($warehouse && !$warehouse->status) {
                $validator->errors()->add('warehouse_id', 'The selected warehouse has been disabled');
            }

            // If it is out of the warehouse operation,Check whether the inventory is sufficient
            if (in_array($this->input('movement_type'), ['sales_shipped', 'stock_transfer', 'stock_return'])) {
                $currentStock = app(\App\Services\StockService::class)
                    ->getProductStock($product, $warehouse);

                if ($this->input('quantity') > $currentStock) {
                    $validator->errors()->add('quantity', 'Insufficient inventory');
                }
            }
        });
    }
} 