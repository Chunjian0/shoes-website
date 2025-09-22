<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users,employee_id'],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:1024'], // maximum1MB
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];

        // You must have a password when creating it, and you can choose the password when updating it.
        if ($this->isMethod('post')) {
            $rules['password'] = ['required', Password::defaults()];
        } else {
            $rules['password'] = ['nullable', Password::defaults()];
            
            // Exclude current users when updating
            $employee = $this->route('employee');
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $employee->id];
            $rules['employee_id'] = ['required', 'string', 'max:50', 'unique:users,employee_id,' . $employee->id];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Telephone',
            'employee_id' => 'Employee number',
            'password' => 'password',
            'is_active' => 'state',
            'avatar' => 'avatar',
            'roles' => 'Role',
            'roles.*' => 'Role',
        ];
    }
} 