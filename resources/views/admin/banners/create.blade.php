@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Create New Banner</h1>
                    <a href="{{ route('admin.banners.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to List
                    </a>
                </div>

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('admin.banners.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Banner Content</h2>
                            
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" 
                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                @error('title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}" 
                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                @error('subtitle')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="button_text" class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                <input type="text" name="button_text" id="button_text" value="{{ old('button_text') }}" 
                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                @error('button_text')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="button_link" class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                                <input type="text" name="button_link" id="button_link" value="{{ old('button_link') }}" 
                                       placeholder="e.g. /products or https://example.com"
                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                @error('button_link')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="is_active" class="flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                                           {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                                @error('is_active')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Banner Image</h2>
                            
                            <div class="mb-4">
                                <input type="hidden" name="media_id" id="media_id" value="{{ old('media_id') }}">
                                
                                <div x-data="{ 
                                    selectedImage: null,
                                    uploading: false,
                                    
                                    async uploadFile(file) {
                                        if (!file) return;
                                        
                                        const formData = new FormData();
                                        formData.append('file', file);
                                        formData.append('model_type', 'App\\Models\\Banner');
                                        formData.append('collection_name', 'banner_images');
                                        
                                        try {
                                            this.uploading = true;
                                            const response = await fetch('{{ route('media.store') }}', {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                    'Accept': 'application/json',
                                                },
                                            });
                                            
                                            if (!response.ok) {
                                                const error = await response.json();
                                                throw new Error(error.message || 'Upload failed');
                                            }
                                            
                                            const data = await response.json();
                                            document.getElementById('media_id').value = data.id;
                                            this.selectedImage = data.url;
                                            
                                            // Show success message
                                            const toast = document.createElement('div');
                                            toast.className = 'fixed top-4 right-4 p-4 bg-green-500 text-white rounded shadow-lg z-50';
                                            toast.textContent = 'Image uploaded successfully';
                                            document.body.appendChild(toast);
                                            
                                            setTimeout(() => {
                                                toast.remove();
                                            }, 3000);
                                            
                                        } catch (error) {
                                            alert(error.message);
                                            console.error(error);
                                        } finally {
                                            this.uploading = false;
                                        }
                                    }
                                }">
                                    <!-- Preview area -->
                                    <div class="mb-4 relative">
                                        <template x-if="selectedImage">
                                            <div class="relative">
                                                <img :src="selectedImage" class="w-full h-auto object-cover rounded-lg border border-gray-200 mb-2" style="max-height: 300px;">
                                                <button type="button" 
                                                    @click="selectedImage = null; document.getElementById('media_id').value = '';"
                                                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 focus:outline-none transform hover:scale-110 transition-all duration-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <template x-if="!selectedImage">
                                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex flex-col items-center text-sm text-gray-600 mt-2">
                                                    <p class="mb-2">Drag and drop an image here or click to select a file</p>
                                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <!-- Uploading status -->
                                        <div x-show="uploading" 
                                            class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
                                            <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- File input -->
                                    <input type="file" 
                                        @change="uploadFile($event.target.files[0]); $event.target.value = null;" 
                                        accept="image/*" 
                                        class="hidden" 
                                        x-ref="fileInput">
                                    
                                    <!-- Upload button -->
                                    <button type="button" 
                                        @click="$refs.fileInput.click()" 
                                        class="w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        :disabled="uploading">
                                        <span x-text="uploading ? 'Uploading...' : 'Select Image'"></span>
                                    </button>
                                </div>
                                
                                @error('media_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mt-8 bg-gray-50 p-4 rounded-md shadow-inner">
                                <h3 class="text-md font-medium text-gray-700 mb-2">Tips for a good banner</h3>
                                <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
                                    <li>Use high-quality images with good resolution</li>
                                    <li>Recommended size: 1920×600 pixels (16:5 ratio)</li>
                                    <li>Keep text concise and clear</li>
                                    <li>Make sure button text clearly indicates the action</li>
                                    <li>Test the banner on different screen sizes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-5 border-t border-gray-200">
                        <div class="flex justify-end">
                            <a href="{{ route('admin.banners.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Save Banner
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
@endpush 