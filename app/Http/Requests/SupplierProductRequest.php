<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'supplier_product_code' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'lead_time' => ['nullable', 'integer', 'min:0'],
            'is_preferred' => ['boolean'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'supplier_product_code' => 'Supplier product number',
            'purchase_price' => 'Purchase price',
            'tax_rate' => 'tax rate',
            'min_order_quantity' => 'Minimum order quantity',
            'lead_time' => 'Delivery cycle',
            'is_preferred' => 'Preferred supplier',
            'remarks' => 'Remark',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product',
            'product_id.exists' => 'The selected product does not exist',
            'purchase_price.required' => 'The purchase price cannot be empty',
            'purchase_price.min' => 'The purchase price cannot be less than 0',
            'min_order_quantity.min' => 'The minimum order quantity cannot be less than1',
            'lead_time.min' => 'The delivery cycle cannot be less than 0',
        ];
    }
} 