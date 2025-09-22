@props([
    'tempId' => null,
    'modelId' => null,
    'modelType',
    'maxFiles' => 5,
    'images' => [],
])

<div x-data="{
    images: @js($images),
    debug: {
        lastUpload: null,
        loadingErrors: [],
        imageLoadSuccess: {},
    },
    uploading: false,
    maxFiles: @js($maxFiles),
    dragover: false,
    
    init() {
        console.log('Alpine init: Initial raw images data received:', @js($images)); // Log raw data
        console.log('Alpine init: Initial this.images state:', JSON.stringify(this.images)); // Log initial Alpine state

        // Fix initial image URLs using the path from the database
        this.images = this.images.map(image => {
            console.log('Alpine init: Processing image:', JSON.stringify(image)); // Log each image before processing
            if (image.path && (!image.url || !image.url.includes('/storage/'))) {
                image.url = `/storage/${image.path.startsWith('/') ? image.path.substring(1) : image.path}`;
                console.log('Alpine init: Image URL updated based on path:', image.url); // Log updated URL
            } else {
                console.log('Alpine init: Image URL not updated. Path:', image.path, 'Existing URL:', image.url); // Log why it wasn't updated
            }
            return image;
        });
        console.log('Alpine init: Final this.images state:', JSON.stringify(this.images)); // Log final Alpine state

        this.$watch('images', value => {
            if (value.length >= this.maxFiles) {
                this.$refs.fileInput.disabled = true;
            } else {
                this.$refs.fileInput.disabled = false;
            }
        });
    },

    async uploadFile(file) {
        if (this.images.length >= this.maxFiles) {
            alert('Maximum file limit has been reached');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('model_type', '{{ $modelType }}');
        @if($tempId)
            formData.append('temp_id', '{{ $tempId }}');
        @endif
        @if($modelId)
            formData.append('model_id', {{ $modelId }});
        @endif

        try {
            this.uploading = true;
            const response = await fetch('{{ route("media.store") }}', {
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
            console.log('Upload response data:', data);
            this.debug.lastUpload = data;
            this.images.push(data);

            // 测试图片是否可以加载
            const img = new Image();
            img.onload = () => {
                console.log('图片加载成功:', data.url);
                this.debug.imageLoadSuccess[data.id] = true;
            };
            img.onerror = (e) => {
                console.error('图片加载失败:', data.url, e);
                this.debug.loadingErrors.push({
                    id: data.id,
                    url: data.url,
                    time: new Date().toISOString(),
                    error: e.type
                });
            };
            img.src = data.url;

        } catch (error) {
            alert(error.message);
        } finally {
            this.uploading = false;
        }
    },

    async deleteImage(id, index) {
        if (!confirm('Are you sure you want to delete this image?')) {
            return;
        }

        try {
            const response = await fetch(`/media/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Deletion failed');
            }

            this.images.splice(index, 1);

        } catch (error) {
            alert(error.message);
        }
    }
}">
    <div class="space-y-6">
        <!-- Upload area -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <!-- Upload button -->
            <div x-show="images.length < maxFiles" class="mb-6">
                <div class="aspect-w-16 aspect-h-9 sm:aspect-w-4 sm:aspect-h-3">
                    <label class="relative block w-full h-full rounded-xl border-2 border-dashed hover:border-indigo-400 transition-all duration-200 cursor-pointer bg-gray-50 group"
                        :class="{ 'border-indigo-500 bg-indigo-50': dragover, 'border-gray-300': !dragover }"
                        @dragover.prevent="dragover = true"
                        @dragleave.prevent="dragover = false"
                        @drop.prevent="dragover = false; uploadFile($event.dataTransfer.files[0])">
                        
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-6">
                            <div class="w-16 h-16 mb-4 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-center">
                                <p class="text-base font-medium text-indigo-600 mb-2 group-hover:text-indigo-700">Click or drag to upload the image</p>
                                <p class="text-sm text-gray-500">support JPG,PNG,GIF Format</p>
                            </div>
                        </div>
                        <input 
                            x-ref="fileInput"
                            type="file" 
                            class="sr-only" 
                            accept="image/*"
                            @change="uploadFile($event.target.files[0]); $event.target.value = null"
                        >
                    </label>
                </div>
            </div>

            <!-- Image preview area -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" x-show="images.length > 0">
                <template x-for="(image, index) in images" :key="image.id">
                    <div class="relative group">
                        <div class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 shadow-sm group-hover:shadow-md transition-all duration-200">
                            <img :src="image.url" :alt="image.name" class="object-cover w-full h-full transform group-hover:scale-105 transition-transform duration-200">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <p class="text-xs text-white truncate" x-text="image.name"></p>
                                </div>
                                <button type="button" 
                                    @click="deleteImage(image.id, index)"
                                    class="absolute top-2 right-2 p-1.5 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 hover:bg-red-600 transform hover:scale-110 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Uploading status -->
        <div x-show="uploading" 
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="transform scale-95"
                x-transition:enter-end="transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="transform scale-100"
                x-transition:leave-end="transform scale-95">
                <div class="flex items-center justify-center space-x-3">
                    <svg class="animate-spin w-8 h-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-lg font-medium text-gray-900">Uploading pictures...</span>
                </div>
            </div>
        </div>

        <!-- Prompt information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-5">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center space-x-2">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Uploaded <span class="text-indigo-600" x-text="images.length"></span> / <span class="text-indigo-600" x-text="maxFiles"></span> open</p>
                            <p class="text-xs text-gray-500">Suggested size 800x800 More than pixels</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            JPG/PNG/GIF
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            maximum 2MB
                        </span>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 sm:px-5">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">Support drag or click upload</p>
                    <button type="button" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium" x-show="images.length > 0">
                        Manage pictures
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%;
}

.aspect-w-16 > * {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

@media (min-width: 640px) {
    .aspect-w-16 {
        padding-bottom: 75%;
    }
}
</style> 