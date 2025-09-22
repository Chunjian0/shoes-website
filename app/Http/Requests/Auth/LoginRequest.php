<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        if (!Auth::attempt($this->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The credentials provided are incorrect.'],
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'email.required' => 'The email cannot be empty',
            'email.email' => 'The email format is incorrect',
            'password.required' => 'Password cannot be empty',
        ];
    }
} 