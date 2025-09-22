<!-- Banner Form -->
<div class="bg-white p-4 rounded-lg shadow-sm mb-6">
    <h3 class="text-lg font-medium text-gray-800 mb-4">Banner Details</h3>
    
    <div id="banner-image-preview-container" class="mb-6" x-data="{ 
        imageUrl: '{{ $banner['image'] ?? '' }}',
        imageFile: null,
        
        handleFileChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            this.imageFile = file;
            this.imageUrl = URL.createObjectURL(file);
        }
    }">
        <label class="block text-sm font-medium text-gray-700 mb-2">Banner Image</label>
        <div class="mt-1 flex flex-col items-center">
            <!-- Image preview -->
            <div class="w-full h-48 mb-4 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden" 
                 :class="{ 'border-2 border-dashed border-gray-300': !imageUrl }">
                <template x-if="imageUrl">
                    <img :src="imageUrl" class="w-full h-full object-cover" />
                </template>
                <template x-if="!imageUrl">
                    <div class="text-center p-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-1 text-sm text-gray-600">
                            Drag and drop an image or click to browse
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Recommended size: 1920×600 pixels (16:5 ratio)
                        </p>
                    </div>
                </template>
            </div>
            
            <input type="file" accept="image/*" class="hidden" id="banner-image" name="file" x-on:change="handleFileChange">
            <button type="button" 
                    @click="document.getElementById('banner-image').click()"
                    class="mt-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Select Image
            </button>
        </div>
        <p class="mt-2 text-sm text-red-600 hidden" id="banner-image-error"></p>
    </div>
    
    <div class="grid grid-cols-1 gap-6">
        <div>
            <label for="banner-title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" id="banner-title" name="title" value="{{ $banner['title'] ?? '' }}" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>
        
        <div>
            <label for="banner-subtitle" class="block text-sm font-medium text-gray-700">Subtitle</label>
            <input type="text" id="banner-subtitle" name="subtitle" value="{{ $banner['subtitle'] ?? '' }}" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>
        
        <div>
            <label for="banner-button-text" class="block text-sm font-medium text-gray-700">Button Text</label>
            <input type="text" id="banner-button-text" name="button_text" value="{{ $banner['button_text'] ?? '' }}" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>
        
        <div>
            <label for="banner-button-link" class="block text-sm font-medium text-gray-700">Button Link</label>
            <input type="text" id="banner-button-link" name="button_link" value="{{ $banner['button_link'] ?? '' }}" 
                placeholder="/products or https://example.com" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>
        
        <div class="flex items-center">
            <input type="checkbox" id="banner-active" name="is_active" value="1" 
                {{ isset($banner['is_active']) && $banner['is_active'] ? 'checked' : '' }} 
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="banner-active" class="ml-2 block text-sm text-gray-700">Active</label>
        </div>
    </div>
</div> 