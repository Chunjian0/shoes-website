<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')->id;
        $parameterId = $this->route('parameter')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category_parameters')
                    ->where('category_id', $categoryId)
                    ->ignore($parameterId),
            ],
            'type' => ['required', 'string', Rule::in(['text', 'number', 'select', 'radio', 'checkbox'])],
            'is_required' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
            'validation_rules' => ['nullable', 'string'],
            'default_value' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'is_searchable' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Parameter name',
            'code' => 'Parameter code',
            'type' => 'Parameter Type',
            'is_required' => 'Is it required',
            'options' => 'Options',
            'options.*' => 'Option value',
            'validation_rules' => 'Verification rules',
            'default_value' => 'default value',
            'sort_order' => 'Sort',
            'description' => 'describe',
            'is_searchable' => 'Is it searchable',
            'is_active' => 'Whether to enable',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This parameter code already exists in the current classification',
            'type.in' => 'The parameter type must be:text,number,select,radio,checkbox No. 1',
        ];
    }
} 