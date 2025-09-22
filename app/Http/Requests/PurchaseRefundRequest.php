<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRefundRequest extends FormRequest
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
            'refund_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:100'],
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
            'refund_date.required' => 'Please select a refund date',
            'refund_date.date' => 'Refund date format is incorrect',
            'amount.required' => 'Please enter the refund amount',
            'amount.numeric' => 'The refund amount must be a number',
            'amount.min' => 'The refund amount must be greater than 0',
            'payment_method.required' => 'Please select the payment method',
            'payment_method.max' => 'Payment method cannot exceed 50 Characters',
            'reference_number.max' => 'The reference number cannot be exceeded 100 Characters',
            'notes.max' => 'Notes cannot be exceeded 1000 Characters',
        ];
    }
} 