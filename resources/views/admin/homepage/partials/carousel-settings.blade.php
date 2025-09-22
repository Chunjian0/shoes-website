<!-- Carousel Settings Form -->
<div class="bg-white p-4 rounded-lg shadow-sm mb-6">
    <h3 class="text-lg font-medium text-gray-800 mb-4">Carousel Settings</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Autoplay Setting -->
        <div class="md:col-span-1">
            <label class="flex items-center mb-1">
                <input type="checkbox" name="settings[autoplay]" id="settings_autoplay" value="1"
                    {{ (isset($settings['autoplay']) && $settings['autoplay']) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Autoplay</span>
            </label>
            <p class="text-xs text-gray-500">Enable automatic sliding of banners</p>
        </div>
    
        <!-- Delay Setting -->
        <div class="md:col-span-1">
            <label for="settings_delay" class="block text-sm font-medium text-gray-700 mb-1">Delay (ms)</label>
            <input type="number" name="settings[delay]" id="settings_delay" 
                value="{{ $settings['delay'] ?? 5000 }}" min="1000" max="10000" step="500"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-gray-500">Time between slides in milliseconds (1000 = 1 second)</p>
        </div>
        
        <!-- Navigation Setting -->
        <div class="md:col-span-1">
            <label class="flex items-center mb-1">
                <input type="checkbox" name="settings[show_navigation]" id="settings_show_navigation" value="1"
                    {{ (isset($settings['show_navigation']) && $settings['show_navigation']) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Show Navigation</span>
            </label>
            <p class="text-xs text-gray-500">Display next/previous buttons</p>
        </div>
        
        <!-- Indicator Setting -->
        <div class="md:col-span-1">
            <label class="flex items-center mb-1">
                <input type="checkbox" name="settings[show_indicators]" id="settings_show_indicators" value="1"
                    {{ (isset($settings['show_indicators']) && $settings['show_indicators']) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Show Indicators</span>
            </label>
            <p class="text-xs text-gray-500">Display dot indicators for slides</p>
        </div>
        
        <!-- Transition Effect -->
        <div class="md:col-span-1">
            <label for="settings_transition" class="block text-sm font-medium text-gray-700 mb-1">Transition Effect</label>
            <select name="settings[transition]" id="settings_transition"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="slide" {{ (isset($settings['transition']) && $settings['transition'] === 'slide') ? 'selected' : '' }}>Slide</option>
                <option value="fade" {{ (isset($settings['transition']) && $settings['transition'] === 'fade') ? 'selected' : '' }}>Fade</option>
                <option value="zoom" {{ (isset($settings['transition']) && $settings['transition'] === 'zoom') ? 'selected' : '' }}>Zoom</option>
            </select>
            <p class="text-xs text-gray-500">Effect when transitioning between slides</p>
        </div>

        <!-- 轮播高度设置 -->
        <div class="md:col-span-1">
            <label for="settings_height" class="block text-sm font-medium text-gray-700 mb-1">Carousel Height (px)</label>
            <input type="number" name="settings[height]" id="settings_height" 
                value="{{ $settings['height'] ?? 500 }}" min="300" max="800" step="10"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-gray-500">Height of the carousel container on desktop</p>
        </div>

        <!-- 移动端轮播高度 -->
        <div class="md:col-span-1">
            <label for="settings_mobile_height" class="block text-sm font-medium text-gray-700 mb-1">Mobile Height (px)</label>
            <input type="number" name="settings[mobile_height]" id="settings_mobile_height" 
                value="{{ $settings['mobile_height'] ?? 350 }}" min="200" max="600" step="10"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-gray-500">Height of the carousel container on mobile devices</p>
        </div>

        <!-- 轮播样式设置 -->
        <div class="md:col-span-1">
            <label for="settings_style" class="block text-sm font-medium text-gray-700 mb-1">Carousel Style</label>
            <select name="settings[style]" id="settings_style"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="default" {{ (isset($settings['style']) && $settings['style'] === 'default') ? 'selected' : '' }}>Default</option>
                <option value="fullwidth" {{ (isset($settings['style']) && $settings['style'] === 'fullwidth') ? 'selected' : '' }}>Full Width</option>
                <option value="boxed" {{ (isset($settings['style']) && $settings['style'] === 'boxed') ? 'selected' : '' }}>Boxed</option>
                <option value="minimal" {{ (isset($settings['style']) && $settings['style'] === 'minimal') ? 'selected' : '' }}>Minimal</option>
            </select>
            <p class="text-xs text-gray-500">Visual style of the carousel</p>
        </div>

        <!-- 暂停悬停设置 -->
        <div class="md:col-span-1">
            <label class="flex items-center mb-1">
                <input type="checkbox" name="settings[pause_on_hover]" id="settings_pause_on_hover" value="1"
                    {{ (isset($settings['pause_on_hover']) && $settings['pause_on_hover']) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Pause on Hover</span>
            </label>
            <p class="text-xs text-gray-500">Pause autoplay when user hovers over carousel</p>
        </div>
    </div>
</div> 