@props([
    'modelId' => null,
    'modelType' => 'App\\Models\\Setting',
    'maxFiles' => 1,
    'images' => [],
    'tempId' => null
])

<div id="banner-image-uploader" x-data="{
    images: @js($images),
    uploading: false,
    maxFiles: @js($maxFiles),
    dragover: false,
    
    init() {
        // 确保每个图片对象都有必要的属性
        this.images = this.images.map(image => ({
            ...image,
            isLoaded: false,
            loadError: false
        }));
        
        this.$watch('images', value => {
            if (value.length >= this.maxFiles) {
                this.$refs.fileInput.disabled = true;
            } else {
                this.$refs.fileInput.disabled = false;
            }
            
            // 更新数据属性，供表单提交时使用
            this.$refs.uploadedImages.dataset.images = JSON.stringify(value);
            
            console.log('Banner images updated', {
                images: value,
                dataString: this.$refs.uploadedImages.dataset.images
            });

            // 验证图片URL
            if (value.length > 0 && value[0].url) {
                this.validateImageUrl(value[0].url);
            }
        });
        
        // 初始验证图片
        if (this.images.length > 0 && this.images[0].url) {
            this.validateImageUrl(this.images[0].url);
        }
        
        console.log('Banner Image Uploader initialized', {
            images: this.images,
            maxFiles: this.maxFiles,
            modelType: '{{ $modelType }}',
            modelId: {{ $modelId ?? 'null' }}
        });
    },

    validateImageUrl(url) {
        // 检查图片URL是否有效
        const img = new Image();
        img.onload = () => {
            console.log('图片验证成功:', url);
        };
        img.onerror = () => {
            console.error('图片验证失败，URL无效:', url);
            
            // 尝试修复URL
            let fixedUrl = url;
            let needRetry = false;
            
            // 检查是否app_models_settings路径但缺少storage/前缀
            if (url.includes('app_models_settings') && !url.startsWith('storage/') && !url.includes('/storage/')) {
                console.log('检测到app_models_settings路径缺少storage/前缀，尝试修复URL');
                
                // 如果是完整URL，在域名后添加storage/
                if (url.match(/^https?:\/\//)) {
                    fixedUrl = url.replace(/^(https?:\/\/[^\/]+\/)/, '$1storage/');
                } else {
                    // 否则直接添加storage/前缀
                    fixedUrl = 'storage/' + url.replace(/^\/+/, '');
                }
                
                console.log('修复后的URL:', fixedUrl);
                needRetry = true;
            }
            // 检查是否临时文件路径
            else if (url.includes('temp/')) {
                console.log('检测到临时路径，尝试修复URL');
                fixedUrl = url.replace('temp/', 'storage/temp/');
                needRetry = true;
            }
            
            if (needRetry) {
                // 使用修复后的URL再次尝试加载
                const retryImg = new Image();
                retryImg.onload = () => {
                    console.log('使用修复后的URL加载成功:', fixedUrl);
                    // 更新图片URL
                    const index = this.images.findIndex(image => image.url === url);
                    if (index !== -1) {
                        this.images[index].url = fixedUrl;
                    }
                };
                retryImg.onerror = () => {
                    console.error('修复URL后仍然加载失败:', fixedUrl);
                    
                    // 尝试其他修复方法
                    if (fixedUrl.includes('storage/app_models_settings')) {
                        // 尝试使用asset()辅助函数的格式
                        const assetUrl = `{{ asset('') }}${fixedUrl}`;
                        console.log('尝试使用asset路径:', assetUrl);
                        
                        const finalRetryImg = new Image();
                        finalRetryImg.onload = () => {
                            console.log('使用asset路径加载成功:', assetUrl);
                            const index = this.images.findIndex(image => image.url === url);
                            if (index !== -1) {
                                this.images[index].url = assetUrl;
                            }
                        };
                        finalRetryImg.onerror = () => {
                            console.error('所有修复方法均失败，移除无效图片');
                            // 移除图片
                            const index = this.images.findIndex(image => image.url === url);
                            if (index !== -1) {
                                this.images.splice(index, 1);
                            }
                        };
                        finalRetryImg.src = assetUrl;
                    } else {
                        // 移除图片
                        const index = this.images.findIndex(image => image.url === url);
                        if (index !== -1) {
                            console.log('移除无效图片', this.images[index]);
                            this.images.splice(index, 1);
                        }
                    }
                };
                retryImg.src = fixedUrl;
            } else {
                // 如果没有可用的修复方法，移除图片
                const index = this.images.findIndex(image => image.url === url);
                if (index !== -1) {
                    console.log('移除无效图片', this.images[index]);
                    this.images.splice(index, 1);
                }
            }
        };
        img.src = url;
    },

    async uploadFile(file) {
        if (this.images.length >= this.maxFiles) {
            Toast.fire({
                icon: 'error',
                title: 'Maximum file limit reached'
            });
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('model_type', '{{ $modelType }}');
        formData.append('collection_name', 'banner');
        formData.append('model_id', 1 );

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
            // 确保图片对象包含必要的属性
            this.images = [{
                ...data,
                isLoaded: false,
                loadError: false
            }];
            
            // 验证新上传的图片
            if (data.url) {
                this.validateImageUrl(data.url);
            }
            
            // 显示更详细的成功消息
            Swal.fire({
                icon: 'success',
                title: '横幅图片上传成功',
                text: '图片已自动保存到首页设置中',
                confirmButtonText: '确定'
            });

        } catch (error) {
            console.error('Upload error:', error);
            Swal.fire({
                title: 'Upload Failed',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } finally {
            this.uploading = false;
        }
    },

    async deleteImage(id, index) {
        const result = await Swal.fire({
            title: 'Confirm Deletion',
            text: 'Are you sure you want to delete this banner image?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        });
        
        if (!result.isConfirmed) {
            return;
        }

        try {
            // 如果ID为0或者undefined，说明这是一个占位图或者无效图片，直接从本地数组中移除
            if (!id || id === 0) {
                console.log('移除占位图或无效图片');
                this.images.splice(index, 1);
                Toast.fire({
                    icon: 'success',
                    title: 'Image removed from display'
                });
                return;
            }

            const response = await fetch(`/media/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const error = await response.json();
                // 如果是文件不存在的错误，我们仍然从数据库中删除记录
                if (error.message && (error.message.includes('No such file') || error.message.includes('file does not exist'))) {
                    console.warn('文件不存在，但仍然删除记录');
                    // 允许继续执行，移除图片
                } else {
                    throw new Error(error.message || 'Deletion failed');
                }
            }

            this.images.splice(index, 1);
            
            Toast.fire({
                icon: 'success',
                title: 'Banner image deleted'
            });

        } catch (error) {
            console.error('Delete error:', error);
            
            // 特别处理"No query results for model"错误，这通常意味着记录不存在
            if (error.message && error.message.includes('No query results for model')) {
                console.warn('记录不存在，但仍然从显示中移除');
                this.images.splice(index, 1);
                
                Toast.fire({
                    icon: 'info',
                    title: 'Image removed (record not found)'
                });
                return;
            }
            
            Swal.fire({
                title: 'Deletion Failed',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    },

    handleImageError($event, url) {
        console.error('Image loading failed:', url);
        
        // 设置图片的loadError标志
        const index = this.images.findIndex(image => image.url === url);
        if (index !== -1) {
            this.images[index].loadError = true;
        }
        
        // 隐藏图片元素
        $event.target.style.display = 'none';
        
        // 在父元素上显示错误提示
        const parent = $event.target.parentElement;
        if (parent) {
            // 创建错误信息元素
            const errorDiv = document.createElement('div');
            errorDiv.className = 'absolute inset-0 flex items-center justify-center bg-gray-100 bg-opacity-90';
            errorDiv.innerHTML = `
                <div class="text-center p-4">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Failed to load image</p>
                    <button class="mt-2 text-xs text-blue-600 hover:text-blue-800 focus:outline-none" x-on:click="removeFailedImage(url)">
                        Remove
                    </button>
                </div>
            `;
            parent.appendChild(errorDiv);
        }
        
        // 将src置为空，避免继续加载占位图
        $event.target.src = '';
    },

    // 新增方法：移除加载失败的图片
    removeFailedImage(url) {
        console.log('移除加载失败的图片:', url);
        const index = this.images.findIndex(image => image.url === url);
        if (index !== -1) {
            this.images.splice(index, 1);
        }
    }
}"
class="relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <!-- 添加隐藏的数据存储元素 -->
    <div x-ref="uploadedImages" class="uploaded-images hidden" data-images="{{ json_encode($images) }}"></div>
    
    <!-- Component Header -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-200">
        <h3 class="text-base font-medium text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Banner Image Manager
        </h3>
    </div>

    <div class="p-4">
        <!-- Preview Area -->
        <div x-show="images.length > 0 && images[0].url" class="mb-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Banner</h4>
            <template x-for="(image, index) in images" :key="image.id">
                <div class="relative" x-show="image.url">
                    <!-- Image Preview Card -->
                    <div class="rounded-lg shadow-md overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 border border-gray-200 transition duration-300 hover:shadow-lg">
                        <!-- Image Display -->
                        <div class="aspect-[3/1] overflow-hidden relative">
                            <!-- 加载中状态 -->
                            <div class="absolute inset-0 flex items-center justify-center bg-gray-50" x-show="!image.isLoaded && !image.loadError">
                                <svg class="w-8 h-8 text-blue-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <img 
                                :src="image.url" 
                                :alt="image.name" 
                                class="w-full h-full object-cover transition duration-500 hover:scale-105"
                                x-on:error="handleImageError($event, image.url)"
                                x-on:load="image.isLoaded = true"
                                x-bind:class="{'hidden': image.loadError}"
                            >
                        </div>
                        
                        <!-- Image Info -->
                        <div class="p-3 bg-white bg-opacity-90 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <div class="truncate">
                                    <span class="text-xs font-medium text-gray-500" x-text="image.name || 'Banner Image'"></span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="deleteImage(image.id, index)"
                                    class="flex items-center text-sm text-red-600 hover:text-red-800 transition-colors focus:outline-none"
                                    title="Delete banner image"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Overlay -->
                    <div class="absolute top-2 right-2 flex space-x-2">
                        <button 
                            type="button" 
                            @click="deleteImage(image.id, index)"
                            class="p-2 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 focus:outline-none transform hover:scale-110 transition-all duration-200 backdrop-blur-sm bg-opacity-70"
                            title="Delete banner image"
                        >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
        </div>

        <!-- Upload Area -->
        <div 
            x-show="images.length < maxFiles"
            class="border-2 border-dashed rounded-xl transition-all duration-300 overflow-hidden"
            x-bind:class="dragover ? 'border-blue-500 bg-blue-50 shadow-inner' : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50'"
        >
            <!-- Drag & Drop Zone -->
            <div 
         @dragover.prevent="dragover = true"
         @dragleave.prevent="dragover = false"
         @drop.prevent="dragover = false; uploadFile($event.dataTransfer.files[0])"
                class="px-6 py-8 flex flex-col items-center justify-center text-center"
            >
                <!-- Upload Icon -->
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-blue-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                
                <!-- Upload Instructions -->
                <h4 class="mb-2 text-base font-medium text-gray-700">Upload banner image</h4>
                <p class="mb-4 text-sm text-gray-500">Drag and drop your image here, or click to browse</p>
            
                <!-- Upload Button -->
                <div class="mb-4">
                    <label class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 transition ease-in-out duration-150 cursor-pointer shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Select File
                        <input type="file" class="sr-only" accept="image/*" @change="uploadFile($event.target.files[0]); $event.target.value = null" x-ref="fileInput">
                    </label>
                </div>
                
                <!-- File Requirements -->
                <div class="text-xs text-gray-500 space-y-1 max-w-sm mx-auto">
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Accepted formats: PNG, JPG, GIF (max 2MB)</span>
                    </div>
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <span>Recommended dimensions: 1920x600 pixels</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Status -->
    <div 
        x-show="uploading" 
        class="absolute inset-0 bg-white bg-opacity-80 backdrop-blur-sm flex flex-col items-center justify-center transition-opacity duration-300 z-10"
    >
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm mx-auto text-center">
            <svg class="animate-spin h-10 w-10 text-blue-500 mb-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-medium text-gray-700 mb-1">Uploading image...</p>
            <p class="text-xs text-gray-500">Please wait while we process your file</p>
        </div>
    </div>
</div> 
