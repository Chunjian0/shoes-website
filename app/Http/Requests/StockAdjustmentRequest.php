<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
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
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'adjustment_date' => ['required', 'date'],
            'adjustment_type' => ['required', Rule::in(['increase', 'decrease'])],
            'reason' => ['required', Rule::in([
                'inventory_count',  // Inventory adjustment
                'damage',          // Damage
                'expiry',          // Expire
                'gift',            // Gift adjustment
                'other',           // Other reasons
            ])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
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
            'warehouse_id.required' => 'Please select the warehouse',
            'warehouse_id.exists' => 'The selected warehouse does not exist',
            'adjustment_date.required' => 'Please select the adjustment date',
            'adjustment_date.date' => 'The format of the adjustment date is incorrect',
            'adjustment_type.required' => 'Please select the adjustment type',
            'adjustment_type.in' => 'The adjustment type is incorrect',
            'reason.required' => 'Please select the reason for adjustment',
            'reason.in' => 'The reasons for adjustment are incorrect',
            'items.required' => 'Please add adjustment details',
            'items.array' => 'Adjust the bright format incorrectly',
            'items.min' => 'At least need to add one adjustment details',
            'items.*.product_id.required' => 'Please select the product',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.quantity.required' => 'Please enter the quantity',
            'items.*.quantity.numeric' => 'The quantity must be numbers',
            'items.*.quantity.min' => 'The quantity must be greater than0',
            'items.*.unit_cost.required' => 'Please enter the unit cost',
            'items.*.unit_cost.numeric' => 'The cost of the unit must be numbers',
            'items.*.unit_cost.min' => 'The cost of the unit cannot be less than0',
            'items.*.batch_number.max' => 'The batch number cannot exceed50Individual character',
            'items.*.notes.max' => 'Remarks cannot be exceeded1000Individual character',
        ];
    }

    /**
     * Configuration verification instance
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check the warehouse status
            $warehouse = \App\Models\Warehouse::find($this->input('warehouse_id'));
            if ($warehouse && !$warehouse->status) {
                $validator->errors()->add('warehouse_id', 'The selected warehouse has been disabled');
            }

            // Check the status of the product
            $productIds = collect($this->input('items'))->pluck('product_id');
            $disabledProducts = \App\Models\Product::whereIn('id', $productIds)
                ->where('status', false)
                ->get();

            if ($disabledProducts->isNotEmpty()) {
                $names = $disabledProducts->pluck('name')->join(',');
                $validator->errors()->add('items', "The following products have been disabled: {$names}");
            }

            // If it is reduced inventory,Check whether the inventory of each product is sufficient
            if ($this->input('adjustment_type') === 'decrease') {
                $stockService = app(\App\Services\StockService::class);
                
                foreach ($this->input('items', []) as $index => $item) {
                    $product = \App\Models\Product::find($item['product_id']);
                    $currentStock = $stockService->getProductStock($product, $warehouse);

                    if ($item['quantity'] > $currentStock) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "merchandise {$product->name} Insufficient inventory"
                        );
                    }
                }
            }
        });
    }
} 