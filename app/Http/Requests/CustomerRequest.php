<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'ic_number' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('customers')->ignore($this->customer)
            ],
            'birthday' => ['nullable', 'date'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'array'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Customer name cannot be empty',
            'ic_number.required' => 'The ID number cannot be empty',
            'ic_number.unique' => 'The ID number already exists',
            'email.email' => 'The email format is incorrect',
        ];
    }
} 