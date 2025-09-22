<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Product Template') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form id="edit-form" action="{{ route('product-templates.update', $productTemplate) }}" method="POST" enctype="multipart/form-data" onsubmit="return prepareFormSubmit()">
                        @csrf
                        @method('PUT')

                        @if(session('error'))
                            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                                <p class="font-medium">{{ __('Error') }}</p>
                                <p>{{ session('error') }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-6">
                            <!-- Basic Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Basic Information') }}</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Name -->
                                    <div>
                                        <x-input-label for="name" value="{{ __('Name') }}" />
                                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $productTemplate->name)" required autofocus />
                                        @error('name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <x-input-label for="category_id" value="{{ __('Category') }}" />
                                        <select id="category_id" name="category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2.5 px-4 h-11 block mt-1 w-full" required>
                                            <option value="">{{ __('Select a category') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $productTemplate->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Is Active -->
                                    <div>
                                        <label for="is_active" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <input type="checkbox" id="is_active" name="is_active" class="toggle-checkbox sr-only" {{ old('is_active', $productTemplate->is_active) ? 'checked' : '' }}>
                                                <div class="block bg-gray-200 w-14 h-8 rounded-full"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                                    </div>
                                            <div class="ml-3 text-gray-700 font-medium">
                                                {{ __('Active') }}
                                    </div>
                                        </label>
                                    </div>

                                    <!-- Promo Page URL -->
                                    <div>
                                        <x-input-label for="promo_page_url" value="{{ __('Promo Page URL') }}" />
                                        <x-text-input id="promo_page_url" class="block mt-1 w-full" type="text" name="promo_page_url" :value="old('promo_page_url', $productTemplate->promo_page_url)" placeholder="e.g., /promo/my-cool-product" />
                                        <p class="text-sm text-gray-500 mt-1">{{ __('Optional: Internal path for the dedicated promo page (React route).') }}</p>
                                        @error('promo_page_url')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Description') }}</h3>
                                <div>
                                    <x-input-label for="description" value="{{ __('Description') }}" />
                                    <!-- Restore standard textarea -->
                                    <textarea id="description" name="description" rows="6" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('description', $productTemplate->description) }}</textarea>
                                    @error('description')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Images -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('Images') }}</h3>
                                            </div>
                                
                                <div class="mb-2">
                                    <p class="text-sm text-gray-600">{{ __('Add product template images. You can upload up to 5 images.') }}</p>
                                </div>
                                
                               
                                
                                <x-product-images-uploader
                                    :modelId="$productTemplate->id"
                                    modelType="App\\Models\\ProductTemplate"
                                    :tempId="uniqid('temp_')"
                                    :maxFiles="5"
                                    :images="$productTemplate->images ?? []"
                                />
                                
                                @error('images')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                @error('images.*')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Parameters -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Parameters') }}</h3>
                                <div id="parameters-container" x-data="parameterManager()" x-init="init()">
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600">{{ __('Add parameter groups (e.g., Size, Color, Model) that will be used to generate product variants.') }}</p>
                                        <p class="text-sm text-gray-600 mt-2">{{ __('After creating this template, you can go to the template details page and click "Add Product" to create individual products.') }}</p>
                                        <p class="text-sm text-gray-600 mt-2">{{ __('Each product will have specific brand, model, price, and stock information based on the parameter combinations defined here.') }}</p>
                            </div>

                                    <template x-for="(group, groupIndex) in parameterGroups" :key="'group-' + groupIndex">
                                        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-center mb-3">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                                        <span class="text-indigo-800 font-bold" x-text="groupIndex + 1"></span>
                                                    </div>
                                                    <input type="text" x-model="group.name" 
                                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 px-3 h-10"
                                                        placeholder="Parameter Group Name (e.g., Size, Color)" required>
                                                </div>
                                                <button type="button" @click="removeGroup(groupIndex)" 
                                                    class="text-red-600 hover:text-red-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <label class="block text-sm font-medium text-gray-700">Parameter Values</label>
                                                    <button type="button" @click="addValue(groupIndex)" 
                                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        + Add Value
                                                    </button>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <template x-for="(value, valueIndex) in group.values" :key="'value-' + groupIndex + '-' + valueIndex">
                                                        <div class="flex items-center">
                                                            <input type="text" x-model="group.values[valueIndex]" 
                                                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 px-3 h-10 flex-grow"
                                                                placeholder="Parameter Value" required>
                                                            <button type="button" @click="removeValue(groupIndex, valueIndex)" 
                                                                class="ml-2 text-red-600 hover:text-red-800">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                    </div>
                                                    </template>
                                                    
                                                    <div x-show="group.values.length === 0" class="text-sm text-gray-500 italic">
                                                        No values added yet. Click "Add Value" to create parameter values.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <div x-show="parameterGroups.length === 0" class="text-gray-500 italic mb-4">
                                        No parameter groups added yet. Click "Add Parameter Group" to create a group.
                                    </div>
                                    
                                    <button type="button" @click="addGroup" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Parameter Group
                                    </button>
                                    
                                    <input type="hidden" name="parameters" id="parameters" :value="JSON.stringify(parameterGroups)">
                                    
                                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded p-4" x-show="parameterGroups.length > 0">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3 flex-1 md:flex md:justify-between">
                                                <p class="text-sm text-blue-700">
                                                    Products will be generated with all possible combinations of parameter values.
                                                    <span x-show="getTotalCombinations() > 0" x-text="'Total expected products: ' + getTotalCombinations()"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            {{-- Preview Buttons --}}
                            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/products/{{ $productTemplate->id }}" target="_blank" 
                               class="inline-flex items-center px-4 py-2 bg-purple-100 border border-transparent rounded-md font-semibold text-xs text-purple-700 uppercase tracking-widest hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                {{ __('Preview Storefront') }}
                            </a>
                            @if($productTemplate->promo_page_url)
                            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}{{ $productTemplate->promo_page_url }}" target="_blank" 
                               class="inline-flex items-center px-4 py-2 bg-teal-100 border border-transparent rounded-md font-semibold text-xs text-teal-700 uppercase tracking-widest hover:bg-teal-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                {{ __('Preview Promo Page') }}
                            </a>
                            @endif

                            <a href="{{ route('product-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Removed TinyMCE Script --}}

    <script>
        function prepareFormSubmit() {
            try {
                // Ensure parameters data is updated
                if (typeof Alpine !== 'undefined' && document.getElementById('parameters-container').__x) {
                    const parameterGroups = document.getElementById('parameters-container').__x.$data.parameterGroups;
                    document.getElementById('parameters').value = JSON.stringify(parameterGroups);
                }
                
                // 处理复选框值
                const isActiveCheckbox = document.getElementById('is_active');
                
                // 查找是否已经存在隐藏的is_active字段
                let hiddenInput = document.querySelector('input[type="hidden"][name="is_active"]');
                
                if (!hiddenInput) {
                    // 如果不存在，创建一个
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'is_active';
                    document.getElementById('edit-form').appendChild(hiddenInput);
                }
                
                // 设置值为1(选中)或0(未选中)
                hiddenInput.value = isActiveCheckbox.checked ? '1' : '0';
                
                return true;
            } catch (e) {
                console.error('Form submission preparation error:', e);
                return false;
            }
        }
        
        // Alpine.js 参数管理器
        function parameterManager() {
            return {
                parameterGroups: [],
                
                init() {
                    // 尝试从已有数据初始化参数组
                    try {
                        const existingParams = {!! json_encode($productTemplate->parameters ?? []) !!};
                        
                        if (existingParams && existingParams.length > 0) {
                            this.parameterGroups = existingParams;
                        } else {
                            // 如果没有现有参数，初始化一个空数组
                            this.parameterGroups = [];
                        }
                    } catch (e) {
                        console.error('Error initializing parameters:', e);
                        this.parameterGroups = [];
                    }
                },
                
                addGroup() {
                    this.parameterGroups.push({
                        name: '',
                        values: []
                    });
                },
                
                removeGroup(index) {
                    this.parameterGroups.splice(index, 1);
                },
                
                addValue(groupIndex) {
                    if (!this.parameterGroups[groupIndex].values) {
                        this.parameterGroups[groupIndex].values = [];
                    }
                    this.parameterGroups[groupIndex].values.push('');
                },
                
                removeValue(groupIndex, valueIndex) {
                    this.parameterGroups[groupIndex].values.splice(valueIndex, 1);
                },
                
                getTotalCombinations() {
                    if (this.parameterGroups.length === 0) return 0;
                    
                    let total = 1;
                    for (const group of this.parameterGroups) {
                        if (group.values && group.values.length > 0) {
                            total *= group.values.length;
                        }
                    }
                    return total;
                }
            };
        }

        // 初始化切换开关样式
        document.addEventListener('DOMContentLoaded', function() {
            initToggleCheckboxes();
        });
        
        document.addEventListener('turbolinks:load', function() {
            initToggleCheckboxes();
        });
        
        function initToggleCheckboxes() {
            const toggleCheckboxes = document.querySelectorAll('.toggle-checkbox');
            
            toggleCheckboxes.forEach(checkbox => {
                const updateToggleStyle = () => {
                    const dot = checkbox.parentNode.querySelector('.dot');
                    const block = checkbox.parentNode.querySelector('.block');
                    
                    if (checkbox.checked) {
                        dot.classList.add('translate-x-6');
                        block.classList.add('bg-indigo-600');
                        block.classList.remove('bg-gray-200');
                    } else {
                        dot.classList.remove('translate-x-6');
                        block.classList.remove('bg-indigo-600');
                        block.classList.add('bg-gray-200');
                    }
                };
                
                // 初始状态
                updateToggleStyle();
                
                // 监听变化
                checkbox.addEventListener('change', updateToggleStyle);
            });
        }
    </script>
    @endpush
</x-app-layout> 