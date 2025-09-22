<!-- Banner Carousel Form -->
<div class="space-y-6">
    <!-- Variables initialization -->
    @php
        $settings = $section->content['settings'] ?? [
            'autoplay' => true,
            'delay' => 5000,
            'transition' => 'slide',
            'show_navigation' => true,
            'show_indicators' => true
        ];
        
        $banners = $section->content['images'] ?? [];
    @endphp
    
    <!-- Carousel Settings Section -->
    @include('admin.homepage.partials.carousel-settings', ['settings' => $settings])
    
    <!-- Banners Management Section -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-800">Banners</h3>
            <button type="button" id="add-banner-item-btn" 
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Banner
            </button>
        </div>
        
        <!-- Banners Container -->
        <div id="banners-container" class="space-y-4">
            <!-- Existing Banners -->
            @if(!empty($banners))
                @foreach($banners as $index => $banner)
                    <div class="banner-item border border-gray-200 rounded-lg p-4 relative" data-index="{{ $index }}">
                        <button type="button" class="remove-banner absolute top-2 right-2 text-red-500 hover:text-red-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <div class="flex items-center mb-2">
                            <div class="mr-2 cursor-move handle">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Banner #{{ $index + 1 }}</span>
                        </div>
                        @include('admin.homepage.partials.banner-form', ['banner' => $banner])
                    </div>
                @endforeach
            @endif
            
            <!-- Template for new banner (hidden) -->
            <template id="banner-template">
                <div class="banner-item border border-gray-200 rounded-lg p-4 relative" data-index="{index}">
                    <button type="button" class="remove-banner absolute top-2 right-2 text-red-500 hover:text-red-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="flex items-center mb-2">
                        <div class="mr-2 cursor-move handle">
                            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Banner #{index}</span>
                    </div>
                    @include('admin.homepage.partials.banner-form', ['banner' => []])
                </div>
            </template>
            
            <!-- No banners message -->
            <div id="no-banners-message" class="{{ !empty($banners) ? 'hidden' : '' }} text-center py-8 bg-gray-50 border border-dashed border-gray-300 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No banners added yet.</p>
                <p class="text-sm text-gray-500">Click "Add Banner" to create your first banner.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bannersContainer = document.getElementById('banners-container');
    const addBannerBtn = document.getElementById('add-banner-item-btn');
    const noBannersMessage = document.getElementById('no-banners-message');
    const bannerTemplate = document.getElementById('banner-template');
    
    // 初始化排序
    if (window.Sortable && bannersContainer) {
        new Sortable(bannersContainer.querySelector(':scope > .banner-item')?.parentNode || bannersContainer, {
            handle: '.handle',
            animation: 150,
            ghostClass: 'bg-blue-50',
            onEnd: function() {
                updateBannerIndices();
            }
        });
    }
    
    // 添加新Banner
    if (addBannerBtn) {
        addBannerBtn.addEventListener('click', function() {
            addNewBanner();
        });
    }
    
    // 绑定删除按钮事件
    bindRemoveBannerEvents();
    
    // 添加新Banner的函数
    function addNewBanner() {
        const bannerItems = document.querySelectorAll('.banner-item');
        const newIndex = bannerItems.length + 1;
        
        // 创建新的Banner HTML
        let newBannerHtml = bannerTemplate.innerHTML
            .replace(/{index}/g, newIndex);
        
        // 创建DOM元素
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newBannerHtml;
        const newBanner = tempDiv.firstElementChild;
        
        // 添加到容器
        bannersContainer.appendChild(newBanner);
        
        // 重新绑定删除事件
        bindRemoveBannerEvents();
        
        // 隐藏"无Banner"消息
        if (noBannersMessage) {
            noBannersMessage.classList.add('hidden');
        }
        
        // 初始化Alpine.js组件
        if (window.Alpine) {
            window.Alpine.initTree(newBanner);
        }
        
        // 更新索引
        updateBannerIndices();
    }
    
    // 绑定删除按钮事件
    function bindRemoveBannerEvents() {
        document.querySelectorAll('.remove-banner').forEach(button => {
            button.addEventListener('click', function() {
                const bannerItem = this.closest('.banner-item');
                
                if (confirm('Are you sure you want to remove this banner?')) {
                    bannerItem.remove();
                    
                    // 检查是否还有Banner
                    const remainingBanners = document.querySelectorAll('.banner-item');
                    if (remainingBanners.length === 0 && noBannersMessage) {
                        noBannersMessage.classList.remove('hidden');
                    }
                    
                    // 更新索引
                    updateBannerIndices();
                }
            });
        });
    }
    
    // 更新Banner索引
    function updateBannerIndices() {
        const bannerItems = document.querySelectorAll('.banner-item');
        
        bannerItems.forEach((item, index) => {
            // 更新data-index属性
            item.dataset.index = index;
            
            // 更新表单输入名称
            const inputs = item.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.name && input.name.includes('banner[')) {
                    const newName = input.name.replace(/banner\[(.*?)\]/, `content[images][${index}][$1]`);
                    input.name = newName;
                }
            });
            
            // 更新标题
            const titleEl = item.querySelector('.text-sm.font-medium');
            if (titleEl) {
                titleEl.textContent = `Banner #${index + 1}`;
            }
        });
    }
});
</script> 