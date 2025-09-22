@extends('layouts.app')

@section('title', $category->name . ' - Add parameters')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 inline-flex items-center">
                <svg class="w-5 h-5 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                front page
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <a href="{{ route('product-categories.index') }}" class="text-gray-700 hover:text-gray-900 ml-1 md:ml-2 text-sm font-medium">Product Category</a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <a href="{{ route('category-parameters.index', $category) }}" class="text-gray-700 hover:text-gray-900 ml-1 md:ml-2 text-sm font-medium">Parameter Management</a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-400 ml-1 md:ml-2 text-sm font-medium">Add parameters</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Add parameters
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    for {{ $category->name }} Add new parameters
                </p>
            </div>
        </div>

        <form action="{{ route('category-parameters.store', $category) }}" method="POST" class="space-y-6" x-data="{ type: '{{ old('type', 'text') }}' }">
            @csrf

            <!-- Basic information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Parameter name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Parameter code</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">type</label>
                    <select name="type" id="type" x-model="type" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="text">text</option>
                        <option value="number">number</option>
                        <option value="select">Pull down to select</option>
                        <option value="radio">Single choice</option>
                        <option value="checkbox">Multiple choices</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Option value -->
            <div x-show="['select', 'radio', 'checkbox'].includes(type)">
                <label class="block text-sm font-medium text-gray-700">Option value</label>
                <div class="mt-1 space-y-2">
                    <template x-for="(option, index) in JSON.parse('{{ old('options', '[""]') }}')" :key="index">
                        <div class="flex items-center space-x-2">
                            <input type="text" :name="'options[]'" x-model="option"
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <button type="button" @click="options.splice(index, 1)"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="options.push('')"
                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Add options
                    </button>
                </div>
                @error('options')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Verification rules -->
            <div>
                <label for="validation_rules" class="block text-sm font-medium text-gray-700">Verification rules</label>
                <input type="text" name="validation_rules" id="validation_rules" value="{{ old('validation_rules') }}"
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                       placeholder="For example:required|min:1|max:100">
                @error('validation_rules')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- default value -->
            <div>
                <label for="default_value" class="block text-sm font-medium text-gray-700">default value</label>
                <input type="text" name="default_value" id="default_value" value="{{ old('default_value') }}"
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('default_value')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- describe -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">describe</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- set up -->
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_required" id="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}
                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <label for="is_required" class="ml-2 block text-sm text-gray-700">Required parameters</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_searchable" id="is_searchable" value="1" {{ old('is_searchable') ? 'checked' : '' }}
                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <label for="is_searchable" class="ml-2 block text-sm text-gray-700">Searchable</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Enable parameters</label>
                </div>
            </div>

            <!-- Operation button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('category-parameters.index', $category) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    keep
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('parameterForm', () => ({
            type: '{{ old('type', 'text') }}',
            options: JSON.parse('{!! old('options', '[""]') !!}'),
        }))
    })
</script>
@endpush 