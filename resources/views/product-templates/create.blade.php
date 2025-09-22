<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Product Template') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 移除 Alpine.js 初始化检查 -->
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form id="create-form" action="{{ route('product-templates.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return prepareFormSubmit()">
                        @csrf

                        <!-- 移除调试信息显示区域 -->
                        
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
                                        <x-input-label for="name" value="{{ __('Name') }}" class="font-medium" />
                                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="Enter product template name" />
                                        @error('name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <x-input-label for="category_id" value="{{ __('Category') }}" class="font-medium" />
                                        <select id="category_id" name="category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2.5 px-4 h-11 block mt-1 w-full" required>
                                            <option value="">{{ __('Select a category') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                                <input type="checkbox" id="is_active" name="is_active" class="toggle-checkbox sr-only" {{ old('is_active') ? 'checked' : '' }}>
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
                                        <x-text-input id="promo_page_url" class="block mt-1 w-full" type="text" name="promo_page_url" :value="old('promo_page_url')" placeholder="e.g., /promo/my-cool-product" />
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
                                    <textarea id="description" name="description" rows="6" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">{{ old('description') }}</textarea>
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
                                    :tempId="uniqid('temp_')"
                                    modelType="App\\Models\\ProductTemplate"
                                    :maxFiles="5"
                                    :images="old('images', [])"
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
                                <div id="parameters-container" data-parameters="{{ old('parameters', '[]') }}">
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600">{{ __('Add parameter groups (e.g., Size, Color, Model) that will be used to generate product variants.') }}</p>
                                        <p class="text-sm text-gray-600 mt-2">{{ __('After creating this template, you can go to the template details page and click "Add Product" to create individual products.') }}</p>
                                        <p class="text-sm text-gray-600 mt-2">{{ __('Each product will have specific brand, model, price, and stock information based on the parameter combinations defined here.') }}</p>
                                        </div>
                                    
                                    <!-- 参数组列表 -->
                                    <div id="parameter-groups-list" class="mb-4 space-y-4">
                                        <!-- 参数组将在这里动态生成 -->
                                    </div>
                                    
                                    <div id="no-parameters-message" class="text-gray-500 italic mb-4">
                                        No parameter groups added yet. Click "Add Parameter Group" to create a group.
                            </div>

                                    <button type="button" id="add-parameter-group" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Parameter Group
                                    </button>
                                    
                                    <input type="hidden" name="parameters" id="parameters" value="{{ old('parameters', '[]') }}">
                                    
                                    <div id="parameters-info" class="mt-6 bg-blue-50 border border-blue-200 rounded p-4" style="display: none;">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                </div>
                                            <div class="ml-3 flex-1 md:flex md:justify-between">
                                                <p class="text-sm text-blue-700">
                                                    Products will be generated with all possible combinations of parameter values.
                                                    <span id="combinations-count"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('product-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Create') }}
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
        document.addEventListener('DOMContentLoaded', function() {
            initCreatePage();
        });
        
        document.addEventListener('turbolinks:load', function() {
            initCreatePage();
        });
        
        function initCreatePage() {
            initParameterManager();
            initToggleCheckboxes();
        }
        
        function prepareFormSubmit() {
            try {
                // Handle checkbox value
                const isActiveCheckbox = document.getElementById('is_active');
                
                // 查找是否已经存在隐藏的is_active字段
                let hiddenInput = document.querySelector('input[type="hidden"][name="is_active"]');
                
                if (!hiddenInput) {
                    // 如果不存在，创建一个
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'is_active';
                    document.getElementById('create-form').appendChild(hiddenInput);
                }
                
                // 设置值为1(选中)或0(未选中)
                hiddenInput.value = isActiveCheckbox.checked ? '1' : '0';
                
                // 添加临时ID到表单，确保可以关联临时上传的图片
                const uploader = document.getElementById('product-images-uploader');
                if (uploader && uploader.dataset.tempId) {
                    // 如果临时ID存在，则添加到表单
                    let tempIdInput = document.querySelector('input[type="hidden"][name="temp_id"]');
                    if (!tempIdInput) {
                        tempIdInput = document.createElement('input');
                        tempIdInput.type = 'hidden';
                        tempIdInput.name = 'temp_id';
                        document.getElementById('create-form').appendChild(tempIdInput);
                    }
                    tempIdInput.value = uploader.dataset.tempId;
                }
                
                return true;
            } catch (e) {
                // 表单提交准备出错
                return false;
            }
        }
        
        // 参数管理器
        function initParameterManager() {
            let parameterGroups = [];
            try {
                const initialData = document.getElementById('parameters-container').dataset.parameters;
                if (initialData) {
                    parameterGroups = JSON.parse(initialData);
                }
            } catch (e) {
                // 参数解析错误，使用空数组
                parameterGroups = [];
            }
            
            // 渲染参数组
            renderParameterGroups();
            
            // 添加参数组按钮事件
            document.getElementById('add-parameter-group').addEventListener('click', function() {
                parameterGroups.push({
                    name: '',
                    values: ['']
                });
                renderParameterGroups();
                updateParametersHiddenInput();
            });
            
            // 渲染所有参数组
            function renderParameterGroups() {
                const container = document.getElementById('parameter-groups-list');
                container.innerHTML = '';
                
                if (parameterGroups.length === 0) {
                    document.getElementById('no-parameters-message').style.display = 'block';
                    document.getElementById('parameters-info').style.display = 'none';
                    return;
                }
                
                document.getElementById('no-parameters-message').style.display = 'none';
                document.getElementById('parameters-info').style.display = 'block';
                
                parameterGroups.forEach((group, groupIndex) => {
                    const groupEl = document.createElement('div');
                    groupEl.className = 'mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200';
                    
                    // 参数组标题和删除按钮
                    const headerHTML = `
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-indigo-800 font-bold">${groupIndex + 1}</span>
                                </div>
                                <input type="text" value="${group.name}" 
                                    class="parameter-group-name border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 px-3 h-10"
                                    placeholder="Parameter Group Name (e.g., Size, Color)" required>
                            </div>
                            <button type="button" class="remove-group text-red-600 hover:text-red-800" data-index="${groupIndex}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                    
                    groupEl.innerHTML = headerHTML;
                    
                    // 参数值区域
                    const valuesContainer = document.createElement('div');
                    valuesContainer.className = 'mt-3';
                    
                    const valuesHeaderHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Parameter Values</label>
                            <button type="button" class="add-value inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-group-index="${groupIndex}">
                                + Add Value
                            </button>
                        </div>
                    `;
                    
                    valuesContainer.innerHTML = valuesHeaderHTML;
                    
                    // 参数值列表
                    const valuesList = document.createElement('div');
                    valuesList.className = 'space-y-2 parameter-values';
                    valuesList.dataset.groupIndex = groupIndex;
                    
                    if (group.values && group.values.length > 0) {
                        group.values.forEach((value, valueIndex) => {
                            const valueItem = document.createElement('div');
                            valueItem.className = 'flex items-center';
                            valueItem.innerHTML = `
                                <input type="text" value="${value}" 
                                    class="parameter-value border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 px-3 h-10 flex-grow"
                                    placeholder="Parameter Value" data-group-index="${groupIndex}" data-value-index="${valueIndex}" required>
                                <button type="button" class="remove-value ml-2 text-red-600 hover:text-red-800" data-group-index="${groupIndex}" data-value-index="${valueIndex}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            `;
                            valuesList.appendChild(valueItem);
                        });
                    } else {
                        valuesList.innerHTML = `
                            <div class="text-sm text-gray-500 italic">
                                No values added yet. Click "Add Value" to create parameter values.
                            </div>
                        `;
                    }
                    
                    valuesContainer.appendChild(valuesList);
                    groupEl.appendChild(valuesContainer);
                    container.appendChild(groupEl);
                });
                
                // 添加事件监听器
                addEventListeners();
                
                // 更新组合计数
                updateCombinationsCount();
            }
            
            // 添加事件监听器
            function addEventListeners() {
                // 删除参数组
                document.querySelectorAll('.remove-group').forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        parameterGroups.splice(index, 1);
                        renderParameterGroups();
                        updateParametersHiddenInput();
                    });
                });
                
                // 添加参数值
                document.querySelectorAll('.add-value').forEach(button => {
                    button.addEventListener('click', function() {
                        const groupIndex = parseInt(this.dataset.groupIndex);
                        parameterGroups[groupIndex].values.push('');
                        renderParameterGroups();
                        updateParametersHiddenInput();
                    });
                });
                
                // 删除参数值
                document.querySelectorAll('.remove-value').forEach(button => {
                    button.addEventListener('click', function() {
                        const groupIndex = parseInt(this.dataset.groupIndex);
                        const valueIndex = parseInt(this.dataset.valueIndex);
                        parameterGroups[groupIndex].values.splice(valueIndex, 1);
                        renderParameterGroups();
                        updateParametersHiddenInput();
                    });
                });
                
                // 更新参数组名称
                document.querySelectorAll('.parameter-group-name').forEach(input => {
                    input.addEventListener('input', function() {
                        const groupIndex = Array.from(document.querySelectorAll('.parameter-group-name')).indexOf(this);
                        parameterGroups[groupIndex].name = this.value;
                        updateParametersHiddenInput();
                    });
                });
                
                // 更新参数值
                document.querySelectorAll('.parameter-value').forEach(input => {
                    input.addEventListener('input', function() {
                        const groupIndex = parseInt(this.dataset.groupIndex);
                        const valueIndex = parseInt(this.dataset.valueIndex);
                        parameterGroups[groupIndex].values[valueIndex] = this.value;
                        updateParametersHiddenInput();
                    });
                });
            }
            
            // 更新隐藏输入字段的值
            function updateParametersHiddenInput() {
                document.getElementById('parameters').value = JSON.stringify(parameterGroups);
                updateCombinationsCount();
            }
            
            // 更新组合计数
            function updateCombinationsCount() {
                const count = getTotalCombinations();
                const countElement = document.getElementById('combinations-count');
                
                if (count > 0) {
                    countElement.textContent = `Total expected products: ${count}`;
                } else {
                    countElement.textContent = '';
                }
            }
            
            // 计算所有组合的总数
            function getTotalCombinations() {
                if (parameterGroups.length === 0) return 0;
                
                let total = 1;
                for (const group of parameterGroups) {
                    if (group.values && group.values.length > 0) {
                        total *= group.values.length;
                    }
                }
                return total;
            }
        }
        
        // 初始化切换开关样式
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