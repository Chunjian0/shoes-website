<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.actual_count' => ['required', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'check_date.required' => 'Please select the inventory date',
            'check_date.date' => 'Incorrect date format',
            'items.required' => 'Please add inventory of products',
            'items.array' => 'Incorrect format of inventory product data',
            'items.min' => 'Add at least one inventory item',
            'items.*.product_id.required' => 'Please select a product',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.actual_count.required' => 'Please enter the actual inventory',
            'items.*.actual_count.integer' => 'The actual inventory must be an integer',
            'items.*.actual_count.min' => 'The actual inventory cannot be less than0',
            'items.*.notes.string' => 'The note must be a string',
        ];
    }
} 