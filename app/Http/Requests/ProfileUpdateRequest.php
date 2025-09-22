<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determines whether the user has the right to initiate this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules applicable to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'], // maximum 2MB
        ];
    }

    /**
     * Get custom properties for verification errors
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'telephone number',
            'avatar' => 'avatar',
        ];
    }

    /**
     * Get the error message for defined validation rules
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name',
            'name.max' => 'The name cannot be exceeded255Characters',
            'email.required' => 'Please enter your email address',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email cannot be exceeded255Characters',
            'email.unique' => 'This email has been used',
            'phone.max' => 'The phone number cannot exceed20Characters',
            'avatar.image' => 'The avatar must be in the picture format',
            'avatar.max' => 'The size of the avatar cannot exceed2MB',
        ];
    }
} 