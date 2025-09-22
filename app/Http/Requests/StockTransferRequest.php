<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTransferRequest extends FormRequest
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
            'source_warehouse_id' => ['required', 'exists:warehouses,id'],
            'target_warehouse_id' => [
                'required',
                'exists:warehouses,id',
                'different:source_warehouse_id'
            ],
            'transfer_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
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
            'source_warehouse_id.required' => 'Please select the source warehouse',
            'source_warehouse_id.exists' => 'The selected source warehouse does not exist',
            'target_warehouse_id.required' => 'Please select the target warehouse',
            'target_warehouse_id.exists' => 'The selected target warehouse does not exist',
            'target_warehouse_id.different' => 'The target warehouse cannot be the same as the source warehouse',
            'transfer_date.required' => 'Please select the dating date',
            'transfer_date.date' => 'The format of the transfer date is incorrect',
            'items.required' => 'Please add metastasis details',
            'items.array' => 'The diverting Mingxin format is incorrect',
            'items.min' => 'At least need to add one transfer detail',
            'items.*.product_id.required' => 'Please select the product',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.quantity.required' => 'Please enter the quantity',
            'items.*.quantity.numeric' => 'The quantity must be numbers',
            'items.*.quantity.min' => 'The quantity must be greater than0',
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
            // Check the status of the source warehouse
            $sourceWarehouse = \App\Models\Warehouse::find($this->input('source_warehouse_id'));
            if ($sourceWarehouse && !$sourceWarehouse->status) {
                $validator->errors()->add('source_warehouse_id', 'The source warehouse has been disabled');
            }

            // Check the target warehouse status
            $targetWarehouse = \App\Models\Warehouse::find($this->input('target_warehouse_id'));
            if ($targetWarehouse && !$targetWarehouse->status) {
                $validator->errors()->add('target_warehouse_id', 'The target warehouse has been disabled');
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

            // Check whether the source warehouse inventory is sufficient
            $stockService = app(\App\Services\StockService::class);
            
            foreach ($this->input('items', []) as $index => $item) {
                $product = \App\Models\Product::find($item['product_id']);
                $currentStock = $stockService->getProductStock($product, $sourceWarehouse);

                if ($item['quantity'] > $currentStock) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "merchandise {$product->name} Insufficient inventory in the source warehouse"
                    );
                }
            }
        });
    }
} 