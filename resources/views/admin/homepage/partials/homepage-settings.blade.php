<!-- Homepage General Settings Form -->
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Site Title Setting -->
        <div class="md:col-span-1">
            <label for="settings_site_title" class="block text-sm font-medium text-gray-700 mb-1">Site Title</label>
            <input type="text" name="settings[site_title]" id="settings_site_title" 
                value="{{ $settings['site_title'] ?? 'Optic System' }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm">
        <p class="mt-1 text-xs text-gray-500">Main title displayed in the homepage header.</p>
        </div>
        
    <!-- Site Description Setting -->
        <div class="md:col-span-1">
            <label for="settings_site_description" class="block text-sm font-medium text-gray-700 mb-1">Site Description</label>
            <input type="text" name="settings[site_description]" id="settings_site_description" 
                value="{{ $settings['site_description'] ?? 'Your one-stop shop for quality optical products' }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm">
        <p class="mt-1 text-xs text-gray-500">Short description for homepage meta tags and SEO.</p>
        </div>
        
    <!-- Products Per Page Setting -->
        <div class="md:col-span-1">
            <label for="settings_products_per_page" class="block text-sm font-medium text-gray-700 mb-1">Products Per Page</label>
            <input type="number" name="settings[products_per_page]" id="settings_products_per_page" 
                value="{{ $settings['products_per_page'] ?? 12 }}" min="4" max="48" step="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm">
        <p class="mt-1 text-xs text-gray-500">Number of products displayed per page in sections like New Arrivals.</p>
        </div>
        
    <!-- New Product Days Setting -->
        <div class="md:col-span-1">
            <label for="settings_new_product_days" class="block text-sm font-medium text-gray-700 mb-1">New Product Days</label>
            <input type="number" name="settings[new_product_days]" id="settings_new_product_days" 
                value="{{ $settings['new_product_days'] ?? 30 }}" min="1" max="90"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm">
        <p class="mt-1 text-xs text-gray-500">Duration (in days) a product is considered 'new'.</p>
        </div>
        
    <!-- Homepage Layout Setting (Custom Select) -->
        <div class="md:col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">Homepage Layout</label>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 9h16M4 15h16M9 4v16"></path></svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-base font-medium text-gray-900">Select Layout Style</h3>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none toggle-recipients" data-type="layout_select">
                            Choose Layout
                            <svg class="inline-block ml-1 w-4 h-4 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="recipient-selector hidden mt-3" id="selector-layout_select">
                    <div class="border border-gray-300 rounded-md shadow-sm overflow-y-auto max-h-60">
                        <div class="user-list p-2" data-type="layout_select">
                            @php 
                                $layoutOptions = ['standard' => 'Standard', 'modern' => 'Modern', 'minimal' => 'Minimal', 'grid' => 'Grid-Focused'];
                                $currentLayout = $settings['layout'] ?? 'standard';
                            @endphp
                            @foreach($layoutOptions as $value => $label)
                            <label class="user-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                <input type="radio" name="settings[layout]" value="{{ $value }}" class="form-radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ $currentLayout === $value ? 'checked' : '' }}>
                                <div class="ml-3 flex items-center">
                                    <div class="ml-2">
                                        <p class="text-sm font-medium text-gray-700">{{ $label }}</p>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-3 selected-recipients" id="selected-layout_select">
                    <div class="text-sm text-gray-700">Selected: 
                        <span class="font-medium text-blue-600" id="selected-layout-text">{{ $layoutOptions[$currentLayout] ?? 'Standard' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <p class="mt-1 text-xs text-gray-500">Overall layout style of the homepage.</p>
        </div>
        
    <!-- Show Promotion Section Setting -->
    <div class="md:col-span-1 flex items-center pt-5">
        <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="settings[show_promotion]" id="settings_show_promotion" value="1"
                    {{ (isset($settings['show_promotion']) && $settings['show_promotion']) ? 'checked' : '' }}
                class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
            <span class="ml-3 text-sm font-medium text-gray-700">Show Promotion Section</span>
            </label>
        </div>
        
    <!-- Show Brands Section Setting -->
    <div class="md:col-span-1 flex items-center pt-5">
        <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="settings[show_brands]" id="settings_show_brands" value="1"
                    {{ (isset($settings['show_brands']) && $settings['show_brands']) ? 'checked' : '' }}
                class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
            <span class="ml-3 text-sm font-medium text-gray-700">Show Brands Section</span>
            </label>
        </div>
        
    <!-- Auto Add New Products Setting -->
    <div class="md:col-span-1 flex items-center pt-5">
        <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="settings[auto_add_new_products]" id="settings_auto_add_new_products" value="1"
                    {{ (isset($settings['auto_add_new_products']) && $settings['auto_add_new_products']) ? 'checked' : '' }}
                class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
            <span class="ml-3 text-sm font-medium text-gray-700">Auto Add New Products to Homepage</span>
            </label>
        </div>
        
    <!-- Auto Add Sale Products Setting -->
    <div class="md:col-span-1 flex items-center pt-5">
        <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="settings[auto_add_sale_products]" id="settings_auto_add_sale_products" value="1"
                    {{ (isset($settings['auto_add_sale_products']) && $settings['auto_add_sale_products']) ? 'checked' : '' }}
                class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
            <span class="ml-3 text-sm font-medium text-gray-700">Auto Add Sale Products to Homepage</span>
            </label>
    </div>
</div> 
<!-- No Save Button here, handled by global JS --> 