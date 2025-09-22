<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QualityInspectionRequest extends FormRequest
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
            'inspection_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.inspected_quantity' => ['required', 'integer', 'min:1'],
            'items.*.passed_quantity' => ['required', 'integer', 'min:0'],
            'items.*.failed_quantity' => ['required', 'integer', 'min:0'],
            'items.*.defect_description' => ['nullable', 'string', 'max:1000'],
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
            'inspection_date.required' => 'Please select the inspection date',
            'inspection_date.date' => 'Check that the date format is incorrect',
            'remarks.max' => 'Notes cannot be exceeded 1000 Characters',
            
            'items.required' => 'Please add inspection items',
            'items.array' => 'Check that the item format is incorrect',
            'items.min' => 'At least one inspection item needs to be added',
            'items.*.purchase_item_id.required' => 'Please select a procurement project',
            'items.*.purchase_item_id.exists' => 'The selected procurement project does not exist',
            'items.*.inspected_quantity.required' => 'Please enter the inspection quantity',
            'items.*.inspected_quantity.integer' => 'The number of tests must be integers',
            'items.*.inspected_quantity.min' => 'The number of inspections must be greater than 0',
            'items.*.passed_quantity.required' => 'Please enter the qualified quantity',
            'items.*.passed_quantity.integer' => 'The qualified number must be an integer',
            'items.*.passed_quantity.min' => 'The number of qualified products cannot be less than 0',
            'items.*.failed_quantity.required' => 'Please enter the unqualified quantity',
            'items.*.failed_quantity.integer' => 'The number of unqualified must be an integer',
            'items.*.failed_quantity.min' => 'The number of unqualified ones cannot be less than 0',
            'items.*.defect_description.max' => 'The defect description cannot exceed 1000 Characters',
        ];
    }
} 