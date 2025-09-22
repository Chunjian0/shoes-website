<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;

class PurchaseRequest extends FormRequest
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
        Log::info('Begin verifying purchase order request data');
        
        $rules = [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purchase_date' => ['required', 'date', 'after_or_equal:today'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.supplier_id' => ['required', 'exists:suppliers,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'supplier_shipping_fee' => ['nullable', 'array'],
            'supplier_shipping_fee.*' => ['nullable', 'numeric', 'min:0'],
            'supplier_notes' => ['nullable', 'array'],
            'supplier_notes.*' => ['nullable', 'string', 'max:500'],
        ];

        // If it is a update request,Check whether the order can be edited
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            /** @var Purchase $purchase */
            $purchase = $this->route('purchase');
            
            if (!$purchase->isEditable()) {
                abort(422, 'The current status of the purchasing order cannot be edited');
            }
        }

        Log::info('Purchase Order Verification Rules:', [
            'rules' => $rules,
            'request_data' => $this->all()
        ]);

        return $rules;
    }

    /**
     * Get verification error message
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Please select a warehouse',
            'warehouse_id.exists' => 'The selected warehouse does not exist',
            'purchase_date.required' => 'Please select the purchase date',
            'purchase_date.date' => 'Incorrect purchase date format',
            'purchase_date.after_or_equal' => 'The purchase date cannot be earlier than today',
            'expected_delivery_date.date' => 'The expected arrival date format is incorrect',
            'expected_delivery_date.after_or_equal' => 'The estimated arrival date cannot be earlier than the purchase date',
            'notes.max' => 'Notes cannot be exceeded 1000 Characters',
            'items.required' => 'Please add at least one product',
            'items.array' => 'Product data format is incorrect',
            'items.min' => 'Please add at least one product',
            'items.*.product_id.required' => 'Please select a product',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.supplier_id.required' => 'Please select a supplier',
            'items.*.supplier_id.exists' => 'The selected supplier does not exist',
            'items.*.quantity.required' => 'Please enter quantity',
            'items.*.quantity.numeric' => 'The quantity must be a number',
            'items.*.quantity.min' => 'The number must be greater than0',
            'items.*.unit_price.required' => 'Please enter the unit price',
            'items.*.unit_price.numeric' => 'The unit price must be a number',
            'items.*.unit_price.min' => 'The unit price must be greater than0',
            'items.*.tax_rate.numeric' => 'Tax rate must be number',
            'items.*.tax_rate.min' => 'The tax rate cannot be less than0',
            'items.*.tax_rate.max' => 'The tax rate cannot be greater than100',
            'items.*.discount_amount.numeric' => 'The discount amount must be a number',
            'items.*.discount_amount.min' => 'The discount amount cannot be less than 0',
            'items.*.notes.max' => 'Product notes cannot exceed500Characters',
            'supplier_shipping_fee.*.numeric' => 'Shipping must be number',
            'supplier_shipping_fee.*.min' => 'The shipping fee cannot be less than0',
            'supplier_notes.*.max' => 'Supplier notes cannot exceed 500 Characters',
        ];
    }

    /**
     * Configuration verification instance
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            Log::info('Start custom verification');
            
            // Check the status of the supplier
            $supplier = \App\Models\Supplier::find($this->input('supplier_id'));
            if ($supplier && !$supplier->is_active) {
                $validator->errors()->add('supplier_id', 'The selected supplier has been disabled');
            }

            // Check the status of the product
            $productIds = collect($this->input('items'))->pluck('product_id');
            $disabledProducts = \App\Models\Product::whereIn('id', $productIds)
                ->where('is_active', false)
                ->get();

            // Check whether the discount amount exceeds the total product
            foreach ($this->input('items', []) as $index => $item) {
                $totalAmount = $item['quantity'] * $item['unit_price'];
                $discountAmount = $item['discount_amount'] ?? 0;

                if ($discountAmount > $totalAmount) {
                    $validator->errors()->add(
                        "items.{$index}.discount_amount",
                        'The discount amount cannot be greater than the total product'
                    );
                }
            }

            // Verify that the quantity of the product meets the minimum order quantity
            $items = $this->input('items', []);
            foreach ($items as $index => $item) {
                $supplierProduct = SupplierProduct::where('product_id', $item['product_id'])
                    ->where('supplier_id', $item['supplier_id'])
                    ->first();
                
                if ($supplierProduct) {
                    Log::info('Verify the minimum order quantity of goods:', [
                        'product_id' => $item['product_id'],
                        'supplier_id' => $item['supplier_id'],
                        'quantity' => $item['quantity'],
                        'min_order_quantity' => $supplierProduct->min_order_quantity
                    ]);
                    
                    if ($item['quantity'] < $supplierProduct->min_order_quantity) {
                        Log::warning('The quantity of goods has not reached the minimum order quantity:', [
                            'product_id' => $supplierProduct->product->id,
                            'product_name' => $supplierProduct->product->name,
                            'supplier_id' => $supplierProduct->supplier->id,
                            'supplier_name' => $supplierProduct->supplier->name,
                            'quantity' => $item['quantity'],
                            'min_order_quantity' => $supplierProduct->min_order_quantity
                        ]);
                        
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "merchandise [{$supplierProduct->product->name}] The minimum order quantity is {$supplierProduct->min_order_quantity}"
                        );
                    }
                } else {
                    Log::error('Supplier product association not found:', [
                        'product_id' => $item['product_id'],
                        'supplier_id' => $item['supplier_id']
                    ]);
                    
                    $validator->errors()->add(
                        "items.{$index}.supplier_id",
                        "This product is not provided by this supplier"
                    );
                }
            }

            if ($validator->errors()->any()) {
                Log::error('Purchase order verification failed:', [
                    'errors' => $validator->errors()->toArray()
                ]);
            } else {
                Log::info('Purchase order verification passed');
            }
        });
    }
} 