{{-- 产品模板变体选择器组件 --}}
<div 
    id="variant-selector-{{ $productTemplate->id ?? 'new' }}" 
    class="variant-selector bg-white p-4 rounded-lg shadow-sm border border-gray-200"
    data-template-id="{{ $productTemplate->id ?? 'new' }}"
>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Select Product Variants</h3>

    {{-- 变体选择区域 --}}
    <div class="variant-options-container space-y-4">
        @if(isset($productTemplate) && $productTemplate->variantOptions->count() > 0)
            @foreach($productTemplate->variantOptions->sortBy('sort_order') as $variantOption)
                <div 
                    class="variant-option-group" 
                    data-option-id="{{ $variantOption->id }}"
                    data-option-name="{{ $variantOption->name }}"
                    data-required="{{ $variantOption->is_required ? 'true' : 'false' }}"
                >
                    <label class="variant-option-group-title block text-sm font-medium text-gray-700 mb-2">
                        {{ $variantOption->name }}
                        @if($variantOption->is_required)
                            <span class="required-mark text-red-500">*</span>
                        @endif
                    </label>
                    
                    <div class="variant-options-list flex flex-wrap gap-2">
                        @foreach(explode(',', $variantOption->values) as $value)
                            @php
                                $value = trim($value);
                                $valueSlug = \Illuminate\Support\Str::slug($value);
                            @endphp
                            <div 
                                class="variant-option" 
                                data-option-id="{{ $variantOption->id }}"
                                data-value="{{ $value }}"
                                data-value-slug="{{ $valueSlug }}"
                            >
                                {{ $value }}
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="error-message mt-1 text-xs text-red-500 hidden">
                        Please select the {{ $variantOption->name }} option
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state p-8 text-center bg-gray-50 rounded-md border border-dashed border-gray-300">
                <div class="empty-state-icon mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h4 class="text-gray-700 font-medium mb-1">No variant options available</h4>
                <p class="text-gray-500 text-sm mb-4">The product template has no variant options set</p>
                
                @if(isset($canEdit) && $canEdit)
                    <a href="{{ route('product-templates.variants', ['productTemplate' => $productTemplate->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Variant Options
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- 选择预览 --}}
    <div class="variant-selection-preview mt-6 border-t border-gray-200 pt-4">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Variants:</h4>
        <div class="selected-variants-container">
            <div class="selected-variants-placeholder text-gray-500 text-sm italic">
                No variants selected yet
            </div>
            <div class="selected-variants-list hidden space-y-1"></div>
        </div>
    </div>

    {{-- 变体表单隐藏字段 --}}
    <input type="hidden" name="selected_variants" id="selected-variants-input-{{ $productTemplate->id ?? 'new' }}" value="{}">
</div>

{{-- 选择项模板（用于Javascript克隆） --}}
<template id="selected-variant-template">
    <div class="selected-variant flex items-center justify-between py-1 px-2 bg-gray-50 rounded">
        <span class="option-name font-medium text-sm text-gray-700"></span>
        <span class="option-value text-sm text-gray-900"></span>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initVariantSelectors();
});

// 支持Turbolinks
document.addEventListener('turbolinks:load', function() {
    initVariantSelectors();
});

function initVariantSelectors() {
    document.querySelectorAll('.variant-selector').forEach(selector => {
        const templateId = selector.dataset.templateId;
        
        // 避免重复初始化
        if (selector.dataset.initialized === 'true') return;
        selector.dataset.initialized = 'true';
        
        // 初始化变体选择
        const options = selector.querySelectorAll('.variant-option');
        const hiddenInput = document.getElementById(`selected-variants-input-${templateId}`);
        const selectedVariantsPlaceholder = selector.querySelector('.selected-variants-placeholder');
        const selectedVariantsList = selector.querySelector('.selected-variants-list');
        const selectedTemplate = document.getElementById('selected-variant-template');
        
        // 存储选择状态
        const selectionState = {};
        
        // 更新预览和隐藏输入
        function updateSelection() {
            // 更新隐藏输入值
            hiddenInput.value = JSON.stringify(selectionState);
            
            // 更新预览显示
            const hasSelections = Object.keys(selectionState).length > 0;
            
            if (hasSelections) {
                selectedVariantsPlaceholder.classList.add('hidden');
                selectedVariantsList.classList.remove('hidden');
                
                // 清除现有预览
                selectedVariantsList.innerHTML = '';
                
                // 添加新预览项
                Object.entries(selectionState).forEach(([optionId, data]) => {
                    const clone = selectedTemplate.content.cloneNode(true);
                    const item = clone.querySelector('.selected-variant');
                    
                    item.querySelector('.option-name').textContent = data.name + ': ';
                    item.querySelector('.option-value').textContent = data.value;
                    
                    selectedVariantsList.appendChild(item);
                });
            } else {
                selectedVariantsPlaceholder.classList.remove('hidden');
                selectedVariantsList.classList.add('hidden');
            }
        }
        
        // 处理变体选项点击
        options.forEach(option => {
            option.addEventListener('click', function() {
                const optionId = this.dataset.optionId;
                const value = this.dataset.value;
                const optionGroup = selector.querySelector(`.variant-option-group[data-option-id="${optionId}"]`);
                const optionName = optionGroup.dataset.optionName;
                const errorMsg = optionGroup.querySelector('.error-message');
                
                // 处理禁用状态
                if (this.classList.contains('disabled')) return;
                
                // 取消同组中的其他选择
                optionGroup.querySelectorAll('.variant-option').forEach(opt => {
                    opt.classList.remove('selected', 'animate-selected');
                });
                
                // 选中当前选项
                this.classList.add('selected', 'animate-selected');
                
                // 更新选择状态
                selectionState[optionId] = {
                    name: optionName,
                    value: value,
                    valueSlug: this.dataset.valueSlug
                };
                
                // 隐藏错误提示
                errorMsg.classList.add('hidden');
                
                // 更新预览
                updateSelection();
            });
        });
        
        // 表单提交前验证
        const form = selector.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // 检查是否所有必需选项都已选择
                const requiredGroups = selector.querySelectorAll('.variant-option-group[data-required="true"]');
                let hasErrors = false;
                
                requiredGroups.forEach(group => {
                    const optionId = group.dataset.optionId;
                    const errorMsg = group.querySelector('.error-message');
                    
                    if (!selectionState[optionId]) {
                        // 显示错误
                        errorMsg.classList.remove('hidden');
                        hasErrors = true;
                        
                        // 添加抖动动画
                        group.classList.add('animate-shake');
                        group.addEventListener('animationend', () => {
                            group.classList.remove('animate-shake');
                        }, { once: true });
                    }
                });
                
                if (hasErrors) {
                    e.preventDefault();
                    
                    // 滚动到第一个错误处
                    const firstError = selector.querySelector('.error-message:not(.hidden)');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    
                    // 如果有ToastSystem，显示错误通知
                    if (window.ToastSystem) {
                        window.ToastSystem.error('Form validation failed', 'Please select all required variant options');
                    }
                }
            });
        }
    });
}
</script>
@endpush

{{-- 使用说明:
    该组件用于产品模板的变体选择。

    基本用法:
    @include('product_template_variant_selector', ['productTemplate' => $productTemplate])
    
    可选参数:
    - canEdit (boolean): 是否显示添加变体选项按钮，默认为false
    
    注意:
    - 该组件需要包含在一个form标签内
    - 选中的变体将以JSON格式存储在名为"selected_variants"的隐藏字段中
    - 组件会自动验证必填变体选项，并在表单提交时阻止提交
--}} 