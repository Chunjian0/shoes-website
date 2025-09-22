<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryLossRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loss_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'loss_date.required' => 'Please select the loss date',
            'loss_date.date' => 'The loss date format is incorrect',
            'reason.required' => 'Please enter the reason for the loss',
            'reason.string' => 'The reason for the loss must be a string',
            'reason.max' => 'The reason for the loss cannot be exceeded255Characters',
            'items.required' => 'Please add a loss-reported product',
            'items.array' => 'The format of the loss product data is incorrect',
            'items.min' => 'Add at least one loss-reporting product',
            'items.*.product_id.required' => 'Please select a product',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.quantity.required' => 'Please enter the amount of loss',
            'items.*.quantity.integer' => 'The number of losses must be integers',
            'items.*.quantity.min' => 'The number of losses must be greater than0',
            'items.*.notes.string' => 'The note must be a string',
        ];
    }
} 