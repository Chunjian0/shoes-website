<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'discount_type' => ['required', Rule::in(['fixed_price', 'discount_rate'])],
            'price' => [
                Rule::requiredIf(fn() => $this->input('discount_type') === 'fixed_price'),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999.99'
            ],
            'discount_rate' => [
                Rule::requiredIf(fn() => $this->input('discount_type') === 'discount_rate'),
                'nullable',
                'numeric',
                'between:0,100'
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'terms' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'discount_type' => 'Discount Type',
            'price' => 'Fixed price',
            'start_date' => 'Effective Date',
            'end_date' => 'Expiration date',
            'min_quantity' => 'Minimum quantity',
            'discount_rate' => 'Discount rate',
            'terms' => 'Terms of the Agreement',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product',
            'product_id.exists' => 'The selected product does not exist',
            'discount_type.required' => 'Please select a discount type',
            'discount_type.in' => 'Invalid discount type',
            'price.required_if' => 'When using a fixed price, the price cannot be empty',
            'price.numeric' => 'The price must be a number',
            'price.min' => 'The price cannot be less than 0',
            'discount_rate.required_if' => 'When using discount rate, the discount rate cannot be empty',
            'discount_rate.numeric' => 'Discount rate must be number',
            'discount_rate.between' => 'The discount rate must be 0-100 between',
            'start_date.required' => 'The effective date cannot be empty',
            'start_date.date' => 'The effective date format is incorrect',
            'end_date.date' => 'Incorrect expiration date format',
            'end_date.after' => 'The expiration date must be later than the effective date',
            'min_quantity.required' => 'The minimum number cannot be empty',
            'min_quantity.integer' => 'The minimum number must be an integer',
            'min_quantity.min' => 'The minimum number cannot be less than 1',
        ];
    }
} 