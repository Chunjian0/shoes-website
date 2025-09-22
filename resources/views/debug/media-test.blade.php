<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Media Upload Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Media Upload Test</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">1. 直接表单上传测试</h2>
            <form action="/media" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">文件</label>
                    <input type="file" name="file" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">模型类型</label>
                    <input type="text" name="model_type" value="App\Models\Setting" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">集合名称</label>
                    <input type="text" name="collection_name" value="banner" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    上传文件
                </button>
            </form>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8" x-data="{ uploading: false, result: null, error: null }">
            <h2 class="text-xl font-semibold mb-4">2. Fetch API 上传测试</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">文件</label>
                    <input type="file" id="fetch-file" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100">
                </div>
                <button 
                    @click="uploadWithFetch()"
                    :disabled="uploading"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                    <span x-show="!uploading">上传文件</span>
                    <span x-show="uploading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        上传中...
                    </span>
                </button>
                
                <template x-if="result">
                    <div class="mt-4 p-4 bg-green-50 text-green-800 rounded-md">
                        <h3 class="font-medium">上传成功</h3>
                        <pre class="mt-2 text-xs overflow-auto" x-text="JSON.stringify(result, null, 2)"></pre>
                    </div>
                </template>
                
                <template x-if="error">
                    <div class="mt-4 p-4 bg-red-50 text-red-800 rounded-md">
                        <h3 class="font-medium">上传失败</h3>
                        <p class="mt-1" x-text="error"></p>
                    </div>
                </template>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">3. 组件上传测试</h2>
            <x-banner-image-uploader :images="[]" />
        </div>
    </div>
    
    <script>
        function uploadWithFetch() {
            const fileInput = document.getElementById('fetch-file');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('请选择文件');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('model_type', 'App\\Models\\Setting');
            formData.append('collection_name', 'banner');
            
            this.uploading = true;
            this.result = null;
            this.error = null;
            
            console.log('Uploading file:', {
                name: file.name,
                size: file.size,
                type: file.type
            });
            
            fetch('/media', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || '上传失败');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Upload successful:', data);
                this.result = data;
            })
            .catch(error => {
                console.error('Upload failed:', error);
                this.error = error.message;
            })
            .finally(() => {
                this.uploading = false;
            });
        }
    </script>
</body>
</html> 