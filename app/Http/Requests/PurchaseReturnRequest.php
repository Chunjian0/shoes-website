<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReturnRequest extends FormRequest
{
    /**
     * Determine whether the user has permission to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get verification rules
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'purchase_id' => ['required', 'exists:purchases,id'],
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.return_quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.reason' => ['nullable', 'string', 'max:1000'],
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
            'purchase_id.required' => 'Please select a purchase order',
            'purchase_id.exists' => 'The selected purchase order does not exist',
            'return_date.required' => 'Please select a return date',
            'return_date.date' => 'Return date format is incorrect',
            'reason.required' => 'Please enter the reason for the return',
            'reason.max' => 'The reason for return cannot be exceeded 1000 Characters',
            'notes.max' => 'Notes cannot be exceeded 1000 Characters',
            
            'items.required' => 'Please add a return item',
            'items.array' => 'The return item format is incorrect',
            'items.min' => 'At least one return item is required',
            'items.*.purchase_item_id.required' => 'Please select a procurement project',
            'items.*.purchase_item_id.exists' => 'The selected procurement project does not exist',
            'items.*.return_quantity.required' => 'Please enter the return quantity',
            'items.*.return_quantity.integer' => 'The return quantity must be an integer',
            'items.*.return_quantity.min' => 'The number of returns must be greater than 0',
            'items.*.unit_price.required' => 'Please enter the unit price',
            'items.*.unit_price.numeric' => 'The unit price must be number',
            'items.*.unit_price.min' => 'The unit price cannot be less than 0',
            'items.*.tax_rate.required' => 'Please enter the tax rate',
            'items.*.tax_rate.numeric' => 'The tax rate must be a number',
            'items.*.tax_rate.min' => 'The tax rate cannot be less than 0',
            'items.*.tax_rate.max' => 'The tax rate cannot be greater than 100',
            'items.*.reason.max' => 'The reason for return cannot be exceeded 1000 Characters',
        ];
    }
} 