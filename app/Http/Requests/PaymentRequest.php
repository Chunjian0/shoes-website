<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'check'])],
            'reference_number' => ['nullable', 'string', 'max:50'],
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
            'payment_date.required' => 'Please select a payment date',
            'payment_date.date' => 'Payment date format is incorrect',
            'amount.required' => 'Please enter the payment amount',
            'amount.numeric' => 'Payment amount must be a number',
            'amount.min' => 'The payment amount must be greater than0',
            'payment_method.required' => 'Please select a payment method',
            'payment_method.in' => 'Incorrect payment method',
            'reference_number.max' => 'The reference number cannot be exceeded50Characters',
            'notes.max' => 'Notes cannot be exceeded1000Characters',
        ];
    }

    /**
     * Configure Verifier Instance
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Purchase $purchase */
            $purchase = $this->route('purchase');

            // Check the status of the purchase order
            if (!in_array($purchase->purchase_status->value, ['approved', 'ordered', 'partially_received', 'received'])) {
                $validator->errors()->add('purchase_id', 'Payment is not allowed in the purchase order status');
            }

            // Check if the payment amount exceeds the unpaid amount
            $unpaidAmount = $purchase->final_amount - $purchase->payments()->sum('amount');
            if ($this->input('amount') > $unpaidAmount) {
                $validator->errors()->add('amount', 'The payment amount cannot exceed the unpaid amount');
            }

            // Check if the payment date is after the purchase order date
            if ($this->input('payment_date') < $purchase->order_date->format('Y-m-d')) {
                $validator->errors()->add('payment_date', 'Payment date cannot be earlier than the purchase order date');
            }
        });
    }
} 