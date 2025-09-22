<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Variants for') }}: {{ $template->name }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('product-templates.show', $template) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Back to Template') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Current Variant Options -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Current Variant Options') }}</h3>
                    
                    @if($template->variantOptions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Required') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sort Order') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($template->variantOptions as $option)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $option->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($option->type) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <form action="{{ route('product-templates.update-variant', [$template->id, $option->id]) }}" method="POST" class="inline-flex items-center">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="sort_order" value="{{ $option->pivot->sort_order }}">
                                                    <label class="inline-flex items-center">
                                                        <input type="checkbox" name="is_required" class="toggle-required form-checkbox h-5 w-5 text-blue-600" {{ $option->pivot->is_required ? 'checked' : '' }} onChange="this.form.submit()">
                                                    </label>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <form action="{{ route('product-templates.update-variant', [$template->id, $option->id]) }}" method="POST" class="inline-flex items-center space-x-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_required" value="{{ $option->pivot->is_required ? '1' : '0' }}">
                                                    <input type="number" name="sort_order" value="{{ $option->pivot->sort_order }}" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm w-16 text-sm">
                                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs">
                                                        {{ __('Update') }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <form action="{{ route('product-templates.destroy-variant', [$template->id, $option->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to remove this variant option?')">
                                                        {{ __('Remove') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">{{ __('No variant options assigned to this template yet.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Add New Variant Option -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Add Variant Option') }}</h3>
                    
                    @if($availableOptions->count() > 0)
                        <form action="{{ route('product-templates.store-variant', $template->id) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="variant_option_id" value="{{ __('Variant Option') }}" />
                                    <select id="variant_option_id" name="variant_option_id" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" required>
                                        <option value="">{{ __('Select a variant option') }}</option>
                                        @foreach($availableOptions as $option)
                                            <option value="{{ $option->id }}">{{ $option->name }} ({{ ucfirst($option->type) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <x-input-label for="is_required" value="{{ __('Required') }}" />
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" id="is_required" name="is_required" class="form-checkbox h-5 w-5 text-blue-600">
                                            <span class="ml-2 text-gray-700">{{ __('Yes') }}</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div>
                                    <x-input-label for="sort_order" value="{{ __('Sort Order') }}" />
                                    <x-text-input id="sort_order" class="block mt-1 w-full" type="number" name="sort_order" value="0" />
                                </div>
                                
                                <div class="md:col-span-4">
                                    <x-primary-button class="mt-4">
                                        {{ __('Add Variant Option') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('No available variant options to add. Create variant options first.') }}
                                    </p>
                                    <div class="mt-4">
                                        <a href="{{ route('variant-options.create') }}" class="text-sm font-medium text-yellow-700 underline hover:text-yellow-600">
                                            {{ __('Create New Variant Option') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 