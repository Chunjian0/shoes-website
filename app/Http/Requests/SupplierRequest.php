<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:50', 'unique:suppliers,code' . ($this->supplier ? ',' . $this->supplier->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'payment_term' => ['nullable', 'integer', 'min:0', 'max:365'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['required', 'string', 'max:50'],
            'contacts.*.position' => ['nullable', 'string', 'max:50'],
            'contacts.*.phone' => ['nullable', 'string', 'max:20'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.is_primary' => ['boolean'],
            'contacts.*.remarks' => ['nullable', 'string', 'max:1000'],
        ];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'code' => 'Supplier number',
            'name' => 'Supplier name',
            'contact_person' => 'Contact',
            'phone' => 'Contact number',
            'email' => 'Email',
            'address' => 'address',
            'credit_limit' => 'Credit limit',
            'payment_term' => 'Accounting period',
            'remarks' => 'Remark',
            'is_active' => 'state',
            'contacts.*.name' => 'Contact name',
            'contacts.*.position' => 'Position',
            'contacts.*.phone' => 'Contact number',
            'contacts.*.email' => 'Email',
            'contacts.*.is_primary' => 'Main contact person',
            'contacts.*.remarks' => 'Remark',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The supplier number cannot be empty',
            'code.unique' => 'Supplier number already exists',
            'name.required' => 'The supplier name cannot be empty',
            'credit_limit.min' => 'The credit limit cannot be less than 0',
            'payment_term.min' => 'The account period cannot be less than 0',
            'payment_term.max' => 'The account period cannot exceed 365 day',
            'contacts.*.name.required' => 'Contact name cannot be empty',
            'contacts.*.email.email' => 'The contact email format is incorrect',
        ];
    }
} 