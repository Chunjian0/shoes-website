<?php

namespace App\Http\Requests\Stock;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;

class InventoryRequest extends FormRequest
{
    /**
     * Determine whether the user has the right to make this request
     */
    public function authorize(): bool
    {
        return true; // TODO: Judgment according to actual permissions
    }

    /**
     * Get verification rules
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'count_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:255'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.system_stock' => ['required', 'numeric', 'min:0'],
            'items.*.actual_stock' => ['required', 'numeric', 'min:0'],
            'items.*.difference' => ['required', 'numeric'],
            'items.*.batch_number' => ['nullable', 'string', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get verification error message
     */
    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Please select the warehouse',
            'warehouse_id.exists' => 'The selected warehouse does not exist',
            'count_date.required' => 'Please select the inventory date',
            'count_date.date' => 'Incorrect inventory date format',
            'count_date.before_or_equal' => 'The inventory date cannot exceed today',
            'notes.max' => 'Remarks cannot be exceeded255Individual character',
            
            'items.required' => 'Please add inventory products',
            'items.array' => 'Incorrect data format of the product',
            'items.min' => 'At least need to add one product',
            'items.*.product_id.required' => 'Please select the product',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.system_stock.required' => 'System inventory cannot be empty',
            'items.*.system_stock.numeric' => 'System inventory must be numbers',
            'items.*.system_stock.min' => 'System inventory cannot be less than0',
            'items.*.actual_stock.required' => 'The actual inventory cannot be empty',
            'items.*.actual_stock.numeric' => 'The actual inventory must be numbers',
            'items.*.actual_stock.min' => 'The actual inventory cannot be smaller than0',
            'items.*.difference.required' => 'The difference cannot be empty',
            'items.*.difference.numeric' => 'The difference must be numbers',
            'items.*.batch_number.max' => 'The batch number cannot exceed50Individual character',
            'items.*.notes.max' => 'Remarks cannot be exceeded255Individual character',
        ];
    }

    /**
     * Configuration verification instance
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify warehouse status
            $warehouse = Warehouse::find($this->input('warehouse_id'));
            if ($warehouse && !$warehouse->status) {
                $validator->errors()->add('warehouse_id', 'The selected warehouse has been disabled');
            }

            // Verify product status
            $productIds = collect($this->input('items'))->pluck('product_id')->unique();
            $inactiveProducts = Product::whereIn('id', $productIds)
                ->where('status', false)
                ->get();

            if ($inactiveProducts->isNotEmpty()) {
                $names = $inactiveProducts->pluck('name')->implode(', ');
                $validator->errors()->add('items', "The following products are disabled: {$names}");
            }

            // Verify whether the difference in the difference is correct
            foreach ($this->input('items', []) as $index => $item) {
                $calculatedDifference = $item['actual_stock'] - $item['system_stock'];
                if ($calculatedDifference != $item['difference']) {
                    $validator->errors()->add(
                        "items.{$index}.difference",
                        'The number of differences is incorrect'
                    );
                }
            }
        });
    }
} 