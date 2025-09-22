@extends('layouts.app')

@section('title', 'Add product classification')

@section('content')
    <div class="max-w-7xl mx-auto py-6 md:py-8 px-4 sm:px-6 lg:px-8 fade-in-up">
        <div class="mt-2">
            <form action="{{ route('product-categories.store') }}" method="POST" class="space-y-6 md:space-y-8" x-data="{ parameters: [] }">
                @csrf

                <!-- Basic information -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 slide-in">
                    <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center">
                        <div class="flex-shrink-0 mr-3 hidden sm:block">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Basic information</h3>
                    </div>

                    <div class="px-4 sm:px-6 py-5 space-y-6">
                        <!-- Classification name -->
                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                            <label for="name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                Classification name <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="w-full shadow-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-lg text-sm transition-all duration-200 hover:border-indigo-300 @error('name') border-red-300 @enderror">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600 pulse-animation">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- describe -->
                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                            <label for="description" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                describe
                            </label>
                            <div class="mt-1 sm:mt-0 sm:col-span-2">    
                                <textarea name="description" id="description" rows="3"
                                    class="w-full shadow-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-lg text-sm transition-all duration-200 hover:border-indigo-300">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- Whether to enable -->
                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <div class="sm:col-span-3">
                                <div class="flex items-center">
                                    <div class="relative inline-block w-12 mr-2 align-middle select-none toggle-container">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                                            {{ old('is_active', true) ? 'checked' : '' }}
                                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-300 appearance-none cursor-pointer"/>
                                        <label for="is_active" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                    </div>
                                    <label for="is_active" class="font-medium text-gray-700 text-sm inline-block ml-2">Open up</label>
                                </div>
                                <p class="text-gray-500 text-sm mt-1 ml-12">Whether this category is enabled immediately</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classification parameter -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 slide-in delay-100" x-data="{ 
                    parameters: {{ 
                        old('parameters') 
                        ? json_encode(old('parameters')) 
                        : '[]' 
                    }},
                    showAddEffect: false,
                    addParameter() {
                        this.parameters.push({
                            name: '',
                            type: 'text',
                            is_required: false,
                            min_length: null,
                            max_length: null,
                            options: []
                        });
                        this.showAddEffect = true;
                        setTimeout(() => {
                            this.showAddEffect = false;
                        }, 1500);
                    }
                }">
                    <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 mr-3 hidden sm:block">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Classification parameter</h3>
                        </div>
                        <button type="button" @click="addParameter()"
                        class="inline-flex items-center px-3 sm:px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Add parameter</span>
                        </button>
                    </div>

                    <div class="px-4 sm:px-6 py-5 space-y-4">
                        <template x-for="(parameter, index) in parameters" :key="index">
                            <div class="border border-gray-200 rounded-lg p-4 space-y-4 transition-all duration-300 hover:shadow-md"
                                 :class="{'shake-animation': showAddEffect && index === parameters.length - 1}">
                                <div class="flex justify-between items-center border-b border-gray-200 pb-3">
                                    <h4 class="text-md font-medium text-gray-900">parameter #<span x-text="index + 1"></span></h4>
                                    <button type="button" @click="parameters.splice(index, 1)"
                                        class="text-gray-500 hover:text-red-600 transition-colors duration-200 flex items-center group">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 group-hover:rotate-12 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        delete
                                    </button>
                                </div>

                                <!-- Parameter name -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Parameter name</label>
                                        <input type="text" x-model="parameter.name" :name="'parameters['+index+'][name]'"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200 hover:border-indigo-300">
                                    </div>

                                    <!-- Parameter -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Parameter</label>
                                        <div class="relative">
                                            <select x-model="parameter.type" :name="'parameters['+index+'][type]'"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pr-10 transition-all duration-200 hover:border-indigo-300 appearance-none">
                                                <option value="text">text</option>
                                                <option value="number">number</option>
                                                <option value="select">Drop -down selection</option>
                                                <option value="radio">Alone</option>
                                                <option value="checkbox">Choice</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200 transform" 
                                                    :class="{'rotate-180': parameter.showSelectOptions }"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verification rules -->
                                <div class="space-y-2 bg-gray-50 p-3 rounded-md">
                                    <label class="block text-sm font-medium text-gray-700">Verification rules</label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="flex items-center p-2 border border-gray-200 rounded bg-white hover:bg-gray-50 transition-colors duration-200">
                                            <input type="hidden" :name="'parameters['+index+'][is_required]'" value="0">
                                            <input type="checkbox" x-model="parameter.is_required" :name="'parameters['+index+'][is_required]'" value="1"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-colors duration-200">
                                            <span class="ml-2 text-sm text-gray-600">Must -have</span>
                                        </label>
                                        
                                        <!-- Minimum length -->
                                        <div class="flex items-center p-2 border border-gray-200 rounded bg-white hover:bg-gray-50 transition-colors duration-200">
                                            <label class="flex items-center flex-1">
                                                <span class="text-sm text-gray-600">Minimum length</span>
                                            </label>
                                            <input type="number" x-model="parameter.min_length" :name="'parameters['+index+'][min_length]'"
                                                class="block w-16 sm:w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200 hover:border-indigo-300"
                                                min="0" placeholder="Unlimited">
                                        </div>

                                        <!-- Maximum length -->
                                        <div class="flex items-center p-2 border border-gray-200 rounded bg-white hover:bg-gray-50 transition-colors duration-200">
                                            <label class="flex items-center flex-1">
                                                <span class="text-sm text-gray-600">Maximum length</span>
                                            </label>
                                            <input type="number" x-model="parameter.max_length" :name="'parameters['+index+'][max_length]'"
                                                class="block w-16 sm:w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200 hover:border-indigo-300"
                                                min="0" placeholder="Unlimited">
                                        </div>
                                    </div>
                                </div>

                                <!-- Option( Only the type is select/radio/checkbox Timely display) -->
                                <div x-show="['select', 'radio', 'checkbox'].includes(parameter.type)"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform -translate-y-4"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 transform translate-y-0"
                                    x-transition:leave-end="opacity-0 transform -translate-y-4"
                                    style="display: none;">
                                    <div class="space-y-2 bg-gray-50 p-3 rounded-md">
                                        <label class="block text-sm font-medium text-gray-700">Option</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="text" 
                                                :id="'option-input-'+index"
                                                class="option-input block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200 hover:border-indigo-300"
                                                placeholder="Entrusted item value"
                                                @keydown.enter.prevent="
                                                    const value = $event.target.value.trim();
                                                    if (value) {
                                                        if (!parameter.options) parameter.options = [];
                                                        parameter.options.push(value);
                                                        $event.target.value = '';
                                                    }
                                                ">
                                            <button type="button" 
                                                class="add-option-btn inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 active:scale-95 whitespace-nowrap"
                                                @click="
                                                    const input = $el.previousElementSibling;
                                                    const value = input.value.trim();
                                                    if (value) {
                                                        if (!parameter.options) parameter.options = [];
                                                        parameter.options.push(value);
                                                        input.value = '';
                                                    }
                                                ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Add option
                                            </button>
                                        </div>
                                        <div class="mt-2 space-y-2 max-h-40 overflow-y-auto pr-1 custom-scrollbar">
                                            <template x-for="(option, optionIndex) in parameter.options" :key="optionIndex">
                                                <div class="flex items-center justify-between bg-white px-3 py-2 rounded border border-gray-200 hover:shadow-sm transition-all duration-200">
                                                    <span x-text="option" class="text-gray-700 text-sm truncate flex-1"></span>
                                                    <input type="hidden" :name="'parameters['+index+'][options][]'" :value="option">
                                                    <button type="button" 
                                                        @click="parameter.options.splice(optionIndex, 1)"
                                                        class="text-gray-400 hover:text-red-600 transition-colors duration-200 ml-2 flex-shrink-0">
                                                        <svg class="h-4 w-4 transform transition-transform duration-200 hover:rotate-90" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" :name="'parameters['+index+'][is_searchable]'" value="1">
                            </div>
                        </template>
                        
                        <!-- 无参数时的提示 -->
                        <div x-show="parameters.length === 0" class="text-center py-8 fade-in">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 pulse-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No parameters added yet. Click the "Add parameter" button to add one.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit button -->
                <div class="pt-5 slide-in delay-200">
                    <div class="flex flex-col-reverse sm:flex-row justify-end space-y-3 space-y-reverse sm:space-y-0 sm:space-x-3 action-buttons">
                        <a href="{{ route('product-categories.index') }}"
                            class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 cancel-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 active:scale-95 submit-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            keep
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* 自定义开关样式 */
        .toggle-checkbox {
            left: 0;
            transition: all 0.3s ease;
        }
        .toggle-checkbox:checked {
            left: auto;
            right: 0;
            border-color: #4F46E5;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #4F46E5;
        }
        .toggle-label {
            transition: background-color 0.3s ease;
        }
        
        /* 美化输入框样式 */
        input[type="text"], 
        input[type="number"], 
        input[type="email"],
        textarea,
        select {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-width: 1.5px;
            padding: 0.625rem 0.75rem;
            height: auto;
            min-height: 2.5rem;
        }
        
        input[type="text"]:hover, 
        input[type="number"]:hover, 
        input[type="email"]:hover,
        textarea:hover,
        select:hover {
            border-color: #a5b4fc;
            box-shadow: 0 2px 5px rgba(79, 70, 229, 0.15);
        }
        
        input[type="text"]:focus, 
        input[type="number"]:focus, 
        input[type="email"]:focus,
        textarea:focus,
        select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
            outline: none;
        }
        
        /* 自定义滚动条 */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* 基础动画效果 */
        .fade-in {
            animation: fadeIn 0.8s ease-in-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .slide-in {
            animation: slideIn 0.5s ease-out forwards;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .delay-100 {
            animation-delay: 0.1s;
        }
        .delay-200 {
            animation-delay: 0.2s;
        }
        
        .pulse-animation {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pulse-slow {
            animation: pulseSlow 3s infinite;
        }
        @keyframes pulseSlow {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 0.3; }
        }
        
        .shake-animation {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        button:active:not(:disabled) {
            transform: scale(0.95);
        }
        
        /* 修改参数区域输入框样式 */
        .option-input {
            min-height: 2.5rem;
            padding: 0.625rem 0.75rem;
        }
        
        /* 参数名称和类型的输入框宽度 */
        .grid-cols-2 input[type="text"],
        .grid-cols-2 select {
            width: 100%;
            min-height: 2.5rem;
        }
        
        /* ================= 移动设备专用样式 ================= */
        @media (max-width: 640px) {
            /* 基础布局调整 */
            body {
                background-color: #f9fafb;
            }
            
            .max-w-7xl {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            /* 卡片式表单布局 */
            .space-y-6.md\:space-y-8 > div {
                margin-bottom: 1rem;
                border-radius: 0 !important;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            /* 表单区域布局调整 */
            .sm\:grid {
                display: block !important;
            }
            
            .px-4.sm\:px-6.py-5 {
                padding: 1rem !important;
            }
            
            /* 标题样式调整 */
            .bg-gray-50.px-4.sm\:px-6.py-4 {
                padding: 0.75rem 1rem !important;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .bg-gray-50.px-4.sm\:px-6.py-4 h3 {
                font-size: 1rem !important;
            }
            
            /* 标题图标调整 */
            .w-8.h-8 {
                width: 1.5rem !important;
                height: 1.5rem !important;
            }
            
            .h-4.w-4 {
                width: 0.75rem !important;
                height: 0.75rem !important;
            }
            
            /* 表单标签和输入框调整 */
            .sm\:col-span-2 {
                margin-top: 0.5rem;
            }
            
            .sm\:mt-px.sm\:pt-2 {
                margin-top: 0 !important;
                margin-bottom: 0.25rem !important;
                display: block !important;
                font-weight: 500 !important;
            }
            
            input, select, textarea {
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 0.375rem !important;
            }
            
            /* 开关样式优化 */
            .toggle-container {
                width: 2.5rem !important;
                height: 1.25rem !important;
            }
            
            .toggle-checkbox {
                width: 1rem !important;
                height: 1rem !important;
                top: 0.125rem !important;
                border-width: 2px !important;
            }
            
            .toggle-label {
                height: 1.25rem !important;
            }
            
            /* 参数区域的样式 */
            .px-4.sm\:px-6.py-5.space-y-4 > div {
                border-radius: 0.5rem !important;
                margin-bottom: 1rem;
            }
            
            /* 无参数提示优化 */
            .py-8.fade-in {
                padding: 2rem 0 !important;
            }
            
            .py-8.fade-in svg {
                width: 3rem !important;
                height: 3rem !important;
            }
            
            /* 底部按钮优化 */
            .pt-5.slide-in.delay-200 {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                box-shadow: 0 -1px 10px rgba(0, 0, 0, 0.1);
                padding: 0.75rem 1rem;
                margin: 0 !important;
                z-index: 50;
            }
            
            .flex.flex-col-reverse {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0.5rem !important;
            }
            
            .space-y-3.space-y-reverse > :not([hidden]) ~ :not([hidden]) {
                margin-top: 0 !important;
            }
            
            .space-y-3, .space-y-reverse {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
            
            .pt-5.slide-in.delay-200 a,
            .pt-5.slide-in.delay-200 button {
                width: 100% !important;
                justify-content: center !important;
                padding: 0.625rem 1rem !important;
                border-radius: 0.375rem !important;
                height: 2.75rem !important;
                font-size: 0.875rem !important;
            }
            
            /* 为底部固定按钮留出空间 */
            form {
                padding-bottom: 4.5rem !important;
            }
            
            /* 按钮图标调整 */
            .pt-5.slide-in.delay-200 svg {
                margin-right: 0.375rem !important;
            }
            
            /* 参数区域优化 */
            .bg-indigo-600:not([type="submit"]) {
                padding: 0.5rem 0.75rem !important;
                height: auto !important;
                font-size: 0.875rem !important;
            }
            
            /* 添加参数按钮优化 */
            button[type="button"].bg-indigo-600:not([type="submit"]) {
                min-height: 2.5rem !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            /* 添加参数动画 */
            .parameter-enter {
                animation: mobileParameterEnter 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            
            @keyframes mobileParameterEnter {
                from {
                    opacity: 0;
                    transform: scale(0.95) translateY(-5px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            /* 自定义过渡动画 */
            .slide-in {
                animation: mobileSlideIn 0.4s ease-out forwards;
            }
            
            @keyframes mobileSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* 优化选项管理区域 */
            .space-y-2.bg-gray-50.p-3 {
                padding: 0.75rem !important;
            }
            
            /* 让添加选项区域更紧凑 */
            .flex.items-center.space-x-2 {
                display: grid !important;
                grid-template-columns: 1fr auto !important;
                gap: 0.5rem !important;
            }
            
            .add-option-btn {
                height: 2.5rem !important;
                width: auto !important;
                white-space: nowrap !important;
            }
            
            /* 验证规则区域的布局 */
            .grid-cols-1.md\:grid-cols-3 {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 0.5rem !important;
            }
            
            /* 微调最小最大长度输入框 */
            .flex.items-center.p-2 {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            
            .w-16.sm\:w-20 {
                width: 4rem !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 检测设备类型
            const isMobile = window.innerWidth <= 640;
            
            if (isMobile) {
                // 为移动设备添加特定类
                document.body.classList.add('mobile-view');
                
                // 在移动设备上优化标题显示
                const sectionTitles = document.querySelectorAll('.bg-gray-50 h3');
                sectionTitles.forEach(title => {
                    const originalText = title.textContent;
                    // 在标题旁加上小图标表示展开/折叠
                    const expandIcon = document.createElement('span');
                    expandIcon.innerHTML = '▼';
                    expandIcon.className = 'ml-2 text-xs text-gray-500';
                    title.appendChild(expandIcon);
                    
                    // 为标题添加点击折叠功能
                    const section = title.closest('.bg-white');
                    const content = section.querySelector('.px-4.sm\\:px-6.py-5');
                    
                    title.parentElement.addEventListener('click', function() {
                        // 切换内容区域的显示状态
                        if (content.style.display === 'none') {
                            content.style.display = 'block';
                            expandIcon.innerHTML = '▼';
                            section.classList.remove('section-collapsed');
                        } else {
                            content.style.display = 'none';
                            expandIcon.innerHTML = '▶';
                            section.classList.add('section-collapsed');
                        }
                    });
                });
                
                // 优化参数添加动画
                const addParamButton = document.querySelector('button[type="button"].bg-indigo-600:not([type="submit"])');
                if (addParamButton) {
                    // 滚动到参数区域
                    addParamButton.addEventListener('click', function() {
                        setTimeout(() => {
                            const parameters = document.querySelectorAll('.border.border-gray-200.rounded-lg.p-4.space-y-4');
                            if (parameters.length > 0) {
                                const lastParameter = parameters[parameters.length - 1];
                                lastParameter.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                lastParameter.classList.add('parameter-enter');
                                setTimeout(() => lastParameter.classList.remove('parameter-enter'), 1000);
                            }
                        }, 100);
                    });
                }
                
                // 移动设备上的底部按钮优化
                const submitButton = document.querySelector('button[type="submit"]');
                const cancelButton = document.querySelector('a[href*="product-categories"]');
                
                if (submitButton && cancelButton) {
                    // 重排底部按钮顺序，在移动设备上把确认按钮放在右侧
                    const buttonContainer = submitButton.parentElement;
                    buttonContainer.appendChild(submitButton);
                    
                    // 添加一点延迟效果，确保主按钮有更多注意力
                    submitButton.style.transitionDelay = '0.1s';
                }
                
                // 监听设备旋转事件
                window.addEventListener('orientationchange', function() {
                    // 重新计算固定底部区域的宽度
                    setTimeout(() => {
                        const buttonArea = document.querySelector('.pt-5.slide-in.delay-200');
                        if (buttonArea) {
                            buttonArea.style.width = '100%';
                        }
                    }, 100);
                });
            }
        });
    </script>
@endsection 