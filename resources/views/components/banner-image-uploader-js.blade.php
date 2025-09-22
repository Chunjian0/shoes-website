@props([
    'modelId' => null,
    'modelType' => 'App\\Models\\Setting',
    'maxFiles' => 1,
    'images' => [],
    'tempId' => null
])

<div id="banner-image-uploader" class="relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <!-- 添加隐藏的数据存储元素 -->
    <div id="uploadedImages" class="uploaded-images hidden" data-images="{{ json_encode($images) }}"></div>
    
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
        <div id="preview-area" class="mb-4" style="display: none;">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Banner</h4>
            <div id="images-container">
                <!-- 图片预览会被JS动态添加到这里 -->
            </div>
        </div>

        <!-- Upload Area -->
        <div id="upload-area" class="border-2 border-dashed rounded-xl transition-all duration-300 overflow-hidden border-gray-300 hover:border-blue-400 hover:bg-gray-50">
            <!-- Drag & Drop Zone -->
            <div id="drop-zone" class="px-6 py-8 flex flex-col items-center justify-center text-center">
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
                        <input type="file" id="fileInput" class="sr-only" accept="image/*">
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
    <div id="upload-status" class="absolute inset-0 bg-white bg-opacity-80 backdrop-blur-sm flex flex-col items-center justify-center transition-opacity duration-300 z-10" style="display: none;">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 防止重复初始化
    if (window.bannerUploaderInitialized) {
        console.log('Banner uploader已经初始化过，跳过');
        return;
    }
    window.bannerUploaderInitialized = true;
    
    // 初始化banner图片上传器
    initBannerImageUploader();
});

function initBannerImageUploader() {
    const uploader = document.getElementById('banner-image-uploader');
    if (!uploader) return;
    
    // 获取DOM元素
    const uploadedImagesEl = document.getElementById('uploadedImages');
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('drop-zone');
    const previewArea = document.getElementById('preview-area');
    const imagesContainer = document.getElementById('images-container');
    const uploadArea = document.getElementById('upload-area');
    const uploadStatus = document.getElementById('upload-status');
    
    // 状态变量
    const maxFiles = {{ $maxFiles }};
    let images = [];
    let uploading = false;
    let dragover = false;
    
    // 初始化
    try {
        // 解析初始图片数据
        const initialImages = JSON.parse(uploadedImagesEl.dataset.images || '[]');
        
        // 过滤掉placeholder图片
        const filteredImages = initialImages.filter(img => {
            if (img.url && img.url.includes('placehold.co')) {
                console.log('初始化时跳过placeholder图片:', img.url);
                return false;
            }
            return true;
        });
        
        images = filteredImages.map(image => ({
            ...image,
            isLoaded: false,
            loadError: false
        }));
        
        console.log('Banner Image Uploader initialized', {
            images,
            maxFiles,
            modelType: '{{ $modelType }}',
            modelId: {{ $modelId ?? 'null' }}
        });
        
        // 初始化图片预览
        updateImageDisplay();
        
        // 验证初始图片URLs
        if (images.length > 0 && images[0].url) {
            validateImageUrl(images[0].url);
        }
    } catch (error) {
        console.error('Error initializing banner image uploader:', error);
    }
    
    // 绑定事件
    fileInput.addEventListener('change', handleFileSelect);
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);
    
    // 处理文件选择
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            uploadFile(file);
        }
        // 重置input，允许选择相同文件
        event.target.value = null;
    }
    
    // 处理拖放事件
    function handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        dropZone.classList.add('border-blue-500', 'bg-blue-50', 'shadow-inner');
        dropZone.classList.remove('border-gray-300');
        dragover = true;
    }
    
    function handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-inner');
        dropZone.classList.add('border-gray-300');
        dragover = false;
    }
    
    function handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-inner');
        dropZone.classList.add('border-gray-300');
        dragover = false;
        
        const file = event.dataTransfer.files[0];
        if (file) {
            uploadFile(file);
        }
    }
    
    // 更新图片显示
    function updateImageDisplay() {
        // 检查是否真的需要更新
        let needUpdate = false;
        const oldImagesJSON = uploadedImagesEl.dataset.images;
        const newImagesJSON = JSON.stringify(images);
        
        // 只有当图片数据真正变化时才更新
        if (oldImagesJSON !== newImagesJSON) {
            needUpdate = true;
        }
        
        if (!needUpdate) {
            console.log('跳过不必要的UI更新');
            return;
        }
        
        console.log('执行UI更新');
        
        // 清空现有的图片预览
        imagesContainer.innerHTML = '';
        
        // 根据图片数量更新UI
        if (images.length > 0) {
            previewArea.style.display = 'block';
            if (images.length >= maxFiles) {
                uploadArea.style.display = 'none';
                if (fileInput) fileInput.disabled = true;
            } else {
                uploadArea.style.display = 'block';
                if (fileInput) fileInput.disabled = false;
            }
            
            // 创建图片预览
            images.forEach((image, index) => {
                const imagePreview = createImagePreview(image, index);
                imagesContainer.appendChild(imagePreview);
            });
        } else {
            previewArea.style.display = 'none';
            uploadArea.style.display = 'block';
            if (fileInput) fileInput.disabled = false;
        }
        
        // 更新数据属性，供表单提交时使用
        uploadedImagesEl.dataset.images = newImagesJSON;
        console.log('Banner images updated', {
            images,
            dataString: newImagesJSON
        });
    }
    
    // 创建图片预览元素
    function createImagePreview(image, index) {
        const container = document.createElement('div');
        container.className = 'relative';
        
        // 检查是否是placeholder图片，如果是则不显示
        if (image.url && image.url.includes('placehold.co')) {
            console.log('跳过placeholder图片:', image.url);
            return container; // 返回空容器
        }
        
        container.innerHTML = `
            <div class="rounded-lg shadow-md overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 border border-gray-200 transition duration-300 hover:shadow-lg">
                <!-- Image Display -->
                <div class="aspect-[3/1] overflow-hidden relative">
                    <!-- 加载中状态 -->
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-50 ${image.isLoaded ? 'hidden' : ''} ${image.loadError ? 'hidden' : ''}">
                        <svg class="w-8 h-8 text-blue-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <img 
                        src="${image.url}" 
                        alt="${image.name || 'Banner Image'}" 
                        class="w-full h-full object-cover transition duration-500 hover:scale-105 ${image.loadError ? 'hidden' : ''}"
                        onerror="handleImageError(event, '${image.url}')"
                        onload="handleImageLoad(event, ${index})"
                    >
                </div>
                
                <!-- Image Info -->
                <div class="p-3 bg-white bg-opacity-90 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="truncate">
                            <span class="text-xs font-medium text-gray-500">${image.name || 'Banner Image'}</span>
                        </div>
                        <button 
                            type="button" 
                            class="flex items-center text-sm text-red-600 hover:text-red-800 transition-colors focus:outline-none"
                            title="Delete banner image"
                            onclick="deleteImage(${image.id}, ${index})"
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
                    class="p-2 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 focus:outline-none transform hover:scale-110 transition-all duration-200 backdrop-blur-sm bg-opacity-70"
                    title="Delete banner image"
                    onclick="deleteImage(${image.id}, ${index})"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        return container;
    }
    
    // 定义全局函数，这些函数会被HTML中的事件直接调用
    window.handleImageLoad = function(event, index) {
        if (index >= 0 && index < images.length) {
            // 防止重复触发
            if (!images[index].isLoaded) {
                images[index].isLoaded = true;
                
                // 直接修改UI，而不是调用updateImageDisplay
                const loadingIndicator = event.target.previousElementSibling;
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
                
                console.log('图片加载完成:', index);
            }
        }
    };
    
    window.handleImageError = function(event, url) {
        console.error('Image loading failed:', url);
        
        // 设置图片的loadError标志
        const index = images.findIndex(image => image.url === url);
        if (index !== -1) {
            images[index].loadError = true;
        }
        
        // 隐藏图片元素
        event.target.style.display = 'none';
        
        // 在父元素上显示错误提示
        const parent = event.target.parentElement;
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
                    <button class="mt-2 text-xs text-blue-600 hover:text-blue-800 focus:outline-none" onclick="removeFailedImage('${url}')">
                        Remove
                    </button>
                </div>
            `;
            parent.appendChild(errorDiv);
        }
        
        // 将src置为空，避免继续加载占位图
        event.target.src = '';
    };
    
    window.removeFailedImage = function(url) {
        console.log('移除加载失败的图片:', url);
        const index = images.findIndex(image => image.url === url);
        if (index !== -1) {
            images.splice(index, 1);
            updateImageDisplay();
        }
    };
    
    window.deleteImage = async function(id, index) {
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
                images.splice(index, 1);
                Toast.fire({
                    icon: 'success',
                    title: 'Image removed from display'
                });
                updateImageDisplay();
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

            images.splice(index, 1);
            updateImageDisplay();
            
            Toast.fire({
                icon: 'success',
                title: 'Banner image deleted'
            });

        } catch (error) {
            console.error('Delete error:', error);
            
            // 特别处理"No query results for model"错误，这通常意味着记录不存在
            if (error.message && error.message.includes('No query results for model')) {
                console.warn('记录不存在，但仍然从显示中移除');
                images.splice(index, 1);
                updateImageDisplay();
                
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
    };

    // 验证图片URL是否有效
    function validateImageUrl(url) {
        // 防止重复验证同一URL
        if (validateImageUrl.cache && validateImageUrl.cache[url]) {
            console.log('跳过已验证的URL:', url);
            return;
        }
        
        // 初始化缓存
        if (!validateImageUrl.cache) {
            validateImageUrl.cache = {};
        }
        
        // 标记为已验证
        validateImageUrl.cache[url] = true;
        
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
                // 防止重复验证修复后的URL
                if (validateImageUrl.cache[fixedUrl]) {
                    console.log('跳过已尝试验证的修复URL:', fixedUrl);
                    return;
                }
                validateImageUrl.cache[fixedUrl] = true;
                
                // 使用修复后的URL再次尝试加载
                const retryImg = new Image();
                retryImg.onload = () => {
                    console.log('使用修复后的URL加载成功:', fixedUrl);
                    // 更新图片URL
                    const index = images.findIndex(image => image.url === url);
                    if (index !== -1) {
                        images[index].url = fixedUrl;
                        // 只在URL实际变化时才更新UI
                        if (url !== fixedUrl) {
                            updateImageDisplay();
                        }
                    }
                };
                retryImg.onerror = () => {
                    console.error('修复URL后仍然加载失败:', fixedUrl);
                    
                    // 尝试其他修复方法
                    if (fixedUrl.includes('storage/app_models_settings')) {
                        // 尝试使用asset()辅助函数的格式
                        const assetUrl = `{{ asset('') }}${fixedUrl}`;
                        
                        // 防止重复验证
                        if (validateImageUrl.cache[assetUrl]) {
                            console.log('跳过已尝试验证的asset URL:', assetUrl);
                            return;
                        }
                        validateImageUrl.cache[assetUrl] = true;
                        
                        console.log('尝试使用asset路径:', assetUrl);
                        
                        const finalRetryImg = new Image();
                        finalRetryImg.onload = () => {
                            console.log('使用asset路径加载成功:', assetUrl);
                            const index = images.findIndex(image => image.url === url);
                            if (index !== -1) {
                                images[index].url = assetUrl;
                                // 只在URL实际变化时才更新UI
                                if (url !== assetUrl) {
                                    updateImageDisplay();
                                }
                            }
                        };
                        finalRetryImg.onerror = () => {
                            console.error('所有修复方法均失败，移除无效图片');
                            // 移除图片
                            const index = images.findIndex(image => image.url === url);
                            if (index !== -1) {
                                if (confirm('图片无法加载，是否从列表中移除?')) {
                                    images.splice(index, 1);
                                    updateImageDisplay();
                                }
                            }
                        };
                        finalRetryImg.src = assetUrl;
                    } else {
                        // 提示用户是否要移除图片
                        const index = images.findIndex(image => image.url === url);
                        if (index !== -1) {
                            if (confirm('图片无法加载，是否从列表中移除?')) {
                                console.log('移除无效图片', images[index]);
                                images.splice(index, 1);
                                updateImageDisplay();
                            }
                        }
                    }
                };
                retryImg.src = fixedUrl;
            } else {
                // 如果没有可用的修复方法，询问是否要移除图片
                const index = images.findIndex(image => image.url === url);
                if (index !== -1) {
                    if (confirm('图片无法加载，是否从列表中移除?')) {
                        console.log('移除无效图片', images[index]);
                        images.splice(index, 1);
                        updateImageDisplay();
                    }
                }
            }
        };
        img.src = url;
    }

    // 上传文件
    async function uploadFile(file) {
        if (images.length >= maxFiles) {
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
        formData.append('model_id', {{ $modelId ?? 1 }});

        try {
            uploading = true;
            uploadStatus.style.display = 'flex';
            
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
            images = [{
                ...data,
                isLoaded: false,
                loadError: false
            }];
            
            updateImageDisplay();
            
            // 验证新上传的图片
            if (data.url) {
                validateImageUrl(data.url);
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
            uploading = false;
            uploadStatus.style.display = 'none';
        }
    }
}
</script> 