@props([
    'modelId' => null,
    'modelType' => 'App\\Models\\Product',
    'maxFiles' => 5,
    'images' => [],
    'tempId' => null
])

<div id="product-images-uploader" class="mt-1 relative" 
    data-model-id="{{ $modelId }}"
    data-model-type="{{ $modelType }}"
    data-max-files="{{ $maxFiles }}"
    data-temp-id="{{ $tempId }}"
    data-csrf-token="{{ csrf_token() }}"
    data-upload-url="{{ route('media.store') }}">
    
    <!-- Preview area -->
    <div id="images-preview-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-4">
        <!-- 预览区域将通过JavaScript动态填充 -->
    </div>

    <!-- Upload area -->
    <div id="upload-area" class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
        <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-gray-600">
                <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                    <span>Upload pictures</span>
                    <input id="file-input" type="file" class="sr-only" accept="image/*">
                </label>
                <p class="pl-1">Or drag the file here</p>
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, GIF maximum 5MB</p>
            <p class="text-xs text-gray-500">Upload up to {{ $maxFiles }} pictures</p>
        </div>
    </div>

    <!-- Uploading status -->
    <div id="upload-loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center" style="display: none;">
        <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initProductImagesUploader();
});

document.addEventListener('turbolinks:load', function() {
    initProductImagesUploader();
});

function initProductImagesUploader() {
    const uploader = document.getElementById('product-images-uploader');
    if (!uploader) return;

    // 获取数据属性
    const modelId = uploader.dataset.modelId;
    const modelType = uploader.dataset.modelType;
    const maxFiles = parseInt(uploader.dataset.maxFiles);
    const tempId = uploader.dataset.tempId;
    const csrfToken = uploader.dataset.csrfToken;
    const uploadUrl = uploader.dataset.uploadUrl;
    
    // 初始化图片数组
    let images = @json($images);
    
    // 元素引用
    const previewContainer = document.getElementById('images-preview-container');
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('file-input');
    const uploadLoading = document.getElementById('upload-loading');
    
    // 初始渲染
    renderImages();
    updateFileInputState();
    
    // 文件输入事件
    fileInput.addEventListener('change', function(event) {
        if (event.target.files.length > 0) {
            uploadFile(event.target.files[0]);
            event.target.value = null; // 重置文件输入
        }
    });
    
    // 拖拽事件
    uploadArea.addEventListener('dragover', function(event) {
        event.preventDefault();
        uploadArea.classList.add('border-indigo-500', 'bg-indigo-50');
    });
    
    uploadArea.addEventListener('dragleave', function(event) {
        event.preventDefault();
        uploadArea.classList.remove('border-indigo-500', 'bg-indigo-50');
    });
    
    uploadArea.addEventListener('drop', function(event) {
        event.preventDefault();
        uploadArea.classList.remove('border-indigo-500', 'bg-indigo-50');
        
        if (event.dataTransfer.files.length > 0) {
            uploadFile(event.dataTransfer.files[0]);
        }
    });
    
    // 渲染图片方法
    function renderImages() {
        previewContainer.innerHTML = '';
        
        images.forEach((image, index) => {
            const imageContainer = document.createElement('div');
            imageContainer.className = 'relative aspect-square bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg shadow-sm overflow-hidden border border-gray-200';
            
            // 检查图片URL是否有效
            let imageUrl = image.url;
            if (!imageUrl && image.path) {
                imageUrl = `/storage/${image.path}`;
            }
            
            const img = document.createElement('img');
            img.src = imageUrl;
            img.alt = image.name || `图片 ${index+1}`;
            img.className = 'w-full h-full object-cover';
            img.onerror = function() {
                this.src = '/images/placeholder.png'; // 默认图片
                this.alt = '加载失败';
            };
            
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 focus:outline-none transform hover:scale-110 transition-all duration-200';
            deleteButton.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;
            deleteButton.addEventListener('click', () => deleteImage(image.id, index));
            
            imageContainer.appendChild(img);
            imageContainer.appendChild(deleteButton);
            previewContainer.appendChild(imageContainer);
        });
    }
    
    // 上传文件方法
    async function uploadFile(file) {
        if (images.length >= maxFiles) {
            showToast('Maximum file limit has been reached', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('model_type', modelType);
        formData.append('collection_name', 'images');
        
        if (modelId) {
            formData.append('model_id', modelId);
        }
        
        if (tempId) {
            formData.append('temp_id', tempId);
        }

        try {
            setLoading(true);
            
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Upload failed');
            }

            const data = await response.json();
            images.push(data);
            renderImages();
            updateFileInputState();
            showToast('File uploaded successfully', 'success');

        } catch (error) {
            showToast(error.message, 'error');
            console.error('Upload error:', error);
        } finally {
            setLoading(false);
        }
        }
    
    // 删除图片方法
    async function deleteImage(id, index) {
        showConfirm('确认要删除这张图片吗？', async (confirmed) => {
            if (!confirmed) return;

        try {
                setLoading(true);
                
            const response = await fetch(`/media/${id}`, {
                method: 'DELETE',
                headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Deletion failed');
            }

                images.splice(index, 1);
                renderImages();
                updateFileInputState();
                showToast('图片已成功删除', 'success');

        } catch (error) {
                showToast(error.message, 'error');
                console.error('Delete error:', error);
            } finally {
                setLoading(false);
            }
        });
    }
    
    // 辅助方法
    function updateFileInputState() {
        if (images.length >= maxFiles) {
            fileInput.disabled = true;
            uploadArea.style.display = 'none';
        } else {
            fileInput.disabled = false;
            uploadArea.style.display = 'flex';
        }
    }
    
    function setLoading(isLoading) {
        uploadLoading.style.display = isLoading ? 'flex' : 'none';
    }
    
    function showToast(message, type = 'info') {
        // 使用自定义的Toast通知
        if (typeof window.showCustomToast === 'function') {
            window.showCustomToast(message, type);
        } else {
            // 创建一个简单的toast元素
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            } text-white`;
            
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // 3秒后自动移除
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 500);
            }, 3000);
        }
    }
    
    function showConfirm(message, callback) {
        // 创建确认对话框
        const confirmContainer = document.createElement('div');
        confirmContainer.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        
        const dialogContent = document.createElement('div');
        dialogContent.className = 'bg-white rounded-lg p-6 max-w-md mx-auto shadow-xl';
        
        dialogContent.innerHTML = `
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">确认</h3>
                <p class="text-gray-600">${message}</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button id="cancel-btn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md text-gray-800">取消</button>
                <button id="confirm-btn" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md text-white">确认</button>
            </div>
        `;
        
        confirmContainer.appendChild(dialogContent);
        document.body.appendChild(confirmContainer);
        
        // 添加事件监听器
        document.getElementById('cancel-btn').addEventListener('click', () => {
            document.body.removeChild(confirmContainer);
            if (callback) callback(false);
        });
        
        document.getElementById('confirm-btn').addEventListener('click', () => {
            document.body.removeChild(confirmContainer);
            if (callback) callback(true);
        });
    }
}
</script> 