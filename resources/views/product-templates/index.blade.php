<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center space-y-3 md:space-y-0">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product Templates') }}
            </h2>
            <a href="{{ route('product-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out ">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ __('Create Template') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- View Toggle and Search Controls -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div class="flex space-x-2 bg-white rounded-lg shadow-sm p-1">
                    <button id="grid-view-btn" class="px-3 py-1.5 rounded-md text-indigo-600 bg-indigo-50 font-medium text-sm flex items-center" aria-pressed="true">
                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Grid
                    </button>
                    <button id="list-view-btn" class="px-3 py-1.5 rounded-md text-gray-500 hover:bg-gray-50 font-medium text-sm flex items-center" aria-pressed="false">
                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        List
                    </button>
                </div>
                
                <form action="{{ route('product-templates.index') }}" method="GET" class="flex w-full sm:w-auto">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <x-text-input 
                            id="search" 
                            name="search" 
                            :value="request('search')" 
                            class="pl-10 pr-10 py-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full sm:w-64"
                            placeholder="Search templates..." 
                        />
                        @if(request('search'))
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <a href="{{ route('product-templates.index') }}" class="text-gray-400 hover:text-gray-500" title="Clear search">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                    <button type="submit" class="ml-2 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Search
                    </button>
                </form>
            </div>

            @if($templates->count() > 0)
                <!-- Grid View (Default) -->
                <div id="grid-view" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($templates as $productTemplate)
                        <div class="template-card bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 animate-fade-in" style="animation-delay: {{ $loop->index * 0.05 }}s">
                            <div class="template-card-image relative h-40 rounded-t-lg overflow-hidden">
                                @if($productTemplate->image_url)
                                    <img src="{{ $productTemplate->image_url }}" alt="{{ $productTemplate->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center bg-gray-100">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-base font-semibold text-gray-900 truncate">{{ $productTemplate->name }}</h3>
                                <p class="mt-1 text-sm text-gray-500 truncate">
                                    @if(isset($productTemplate->brand) || isset($productTemplate->model))
                                        {{ $productTemplate->brand ?? '' }} {{ $productTemplate->model ?? '' }}
                                    @else
                                        <span class="italic">No brand/model info</span>
                                    @endif
                                </p>
                                <div class="mt-3 flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-900">
                                        @if(isset($productTemplate->base_price) && $productTemplate->base_price > 0)
                                            ${{ number_format($productTemplate->base_price, 2) }}
                                        @else
                                            <span class="text-gray-500">Price not set</span>
                                        @endif
                                        </span>
                                    <div class="flex space-x-1">
                                        <a href="{{ route('product-templates.show', $productTemplate) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-blue-100 transition-colors duration-150" title="View template">
                                            <svg class="h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('product-templates.edit', $productTemplate) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors duration-150" title="Edit template">
                                            <svg class="h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('product-templates.destroy', $productTemplate) }}" method="POST" class="inline delete-form" data-template-name="{{ $productTemplate->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="delete-button inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-red-100 transition-colors duration-150" title="Delete template">
                                                <svg class="h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- List View (Hidden by default) -->
                <div id="list-view" class="hidden space-y-3">
                    @foreach($templates as $productTemplate)
                        <div class="template-card bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 animate-slide-in-left" style="animation-delay: {{ $loop->index * 0.05 }}s">
                            <div class="p-4 flex items-center">
                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                    @if($productTemplate->image_url)
                                        <img src="{{ $productTemplate->image_url }}" alt="{{ $productTemplate->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gray-100">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex justify-between">
                                        <div>
                                            <h3 class="text-base font-medium text-gray-900">{{ $productTemplate->name }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                @if(isset($productTemplate->brand) || isset($productTemplate->model))
                                                    {{ $productTemplate->brand ?? '' }} {{ $productTemplate->model ?? '' }}
                                                @else
                                                    <span class="italic">No brand/model info</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">
                                                @if(isset($productTemplate->base_price) && $productTemplate->base_price > 0)
                                                    ${{ number_format($productTemplate->base_price, 2) }}
                                                @else
                                                    <span class="text-gray-500">Price not set</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex space-x-1">
                                    <a href="{{ route('product-templates.show', $productTemplate) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-blue-100 transition-colors duration-150" title="View template">
                                        <svg class="h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('product-templates.edit', $productTemplate) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors duration-150" title="Edit template">
                                        <svg class="h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('product-templates.destroy', $productTemplate) }}" method="POST" class="inline delete-form" data-template-name="{{ $productTemplate->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="delete-button inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-red-100 transition-colors duration-150" title="Delete template">
                                            <svg class="h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                            @endforeach
                </div>

                <div class="mt-6">
                        {{ $templates->links() }}
                </div>
            @else
                <div class="empty-state bg-white rounded-lg shadow-sm p-8 flex flex-col items-center text-center animate-fade-in">
                    <div class="empty-state-icon w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('No Product Templates Found') }}</h3>
                    <p class="text-sm text-gray-500 max-w-md mb-6">
                        @if(request('search'))
                            {{ __('We couldn\'t find any product templates matching your search "') . request('search') . '". Try a different search term or create a new template.' }}
                        @else
                            {{ __('You haven\'t created any product templates yet. Templates help you define product variants and streamline your product management.') }}
                        @endif
                    </p>
                    <a href="{{ route('product-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('Create Your First Template') }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initTemplateListPage();
        });
        
        // 支持Turbolinks
        document.addEventListener('turbolinks:load', function() {
            initTemplateListPage();
        });
        
        function initTemplateListPage() {
            // 初始化删除按钮
            initDeleteButtons();
            
            // 初始化视图切换
            initViewToggle();
        }
        
        function initDeleteButtons() {
            document.querySelectorAll('.delete-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    const templateName = form.dataset.templateName;
                    
                    if (window.ConfirmationDialog) {
                        window.ConfirmationDialog.danger(
                            'Confirm Delete', 
                            `Are you sure you want to delete product template "${templateName}"? This action cannot be undone and may affect associated products.`,
                            function() {
                                form.submit();
                            },
                            {
                                confirmBtnText: 'Delete',
                            }
                        );
                    } else {
                        // 回退到原生确认
                        if (confirm(`Are you sure you want to delete product template "${templateName}"? This action cannot be undone.`)) {
                            form.submit();
                        }
                    }
                });
            });
        }
        
        function initViewToggle() {
            const gridViewBtn = document.getElementById('grid-view-btn');
            const listViewBtn = document.getElementById('list-view-btn');
            const gridView = document.getElementById('grid-view');
            const listView = document.getElementById('list-view');
            
            if (!gridViewBtn || !listViewBtn || !gridView || !listView) return;
            
            // 从本地存储读取用户偏好
            const preferredView = localStorage.getItem('templateViewMode') || 'grid';
            
            // 初始化视图
            if (preferredView === 'list') {
                activateListView();
            } else {
                activateGridView();
            }
            
            // 设置点击事件
            gridViewBtn.addEventListener('click', function() {
                activateGridView();
                localStorage.setItem('templateViewMode', 'grid');
            });
            
            listViewBtn.addEventListener('click', function() {
                activateListView();
                localStorage.setItem('templateViewMode', 'list');
            });
            
            function activateGridView() {
                gridView.classList.remove('hidden');
                listView.classList.add('hidden');
                
                gridViewBtn.classList.add('text-indigo-600', 'bg-indigo-50');
                gridViewBtn.classList.remove('text-gray-500', 'hover:bg-gray-50');
                gridViewBtn.setAttribute('aria-pressed', 'true');
                
                listViewBtn.classList.remove('text-indigo-600', 'bg-indigo-50');
                listViewBtn.classList.add('text-gray-500', 'hover:bg-gray-50');
                listViewBtn.setAttribute('aria-pressed', 'false');
            }
            
            function activateListView() {
                gridView.classList.add('hidden');
                listView.classList.remove('hidden');
                
                listViewBtn.classList.add('text-indigo-600', 'bg-indigo-50');
                listViewBtn.classList.remove('text-gray-500', 'hover:bg-gray-50');
                listViewBtn.setAttribute('aria-pressed', 'true');
                
                gridViewBtn.classList.remove('text-indigo-600', 'bg-indigo-50');
                gridViewBtn.classList.add('text-gray-500', 'hover:bg-gray-50');
                gridViewBtn.setAttribute('aria-pressed', 'false');
            }
        }
    </script>
    @endpush
</x-app-layout> 