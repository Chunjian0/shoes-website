@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">调试上传测试</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">CSRF 令牌信息</h2>
            <div class="bg-gray-100 p-4 rounded-lg mb-4">
                <p><strong>Meta标签中的CSRF令牌:</strong> <span id="meta-csrf-token">检查中...</span></p>
                <p><strong>服务器端CSRF令牌:</strong> <span id="server-csrf-token">检查中...</span></p>
            </div>
            
            <button id="check-csrf" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                检查CSRF令牌
            </button>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">测试上传</h2>
            
            <form id="test-upload-form" class="mb-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">选择文件</label>
                    <input type="file" name="file" id="test-file" class="border border-gray-300 p-2 w-full rounded">
                </div>
                
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    测试上传
                </button>
            </form>
            
            <div id="upload-result" class="hidden bg-gray-100 p-4 rounded-lg">
                <h3 class="font-semibold mb-2">上传结果:</h3>
                <pre id="upload-result-content" class="whitespace-pre-wrap break-all"></pre>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">测试Banner图片上传组件</h2>
            
            <div class="mb-6">
                <x-banner-image-uploader
                    modelType="App\\Models\\Setting"
                    :modelId="null"
                    :maxFiles="1"
                    :images="[]"
                />
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 显示Meta标签中的CSRF令牌
        const metaCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 'Not found';
        document.getElementById('meta-csrf-token').textContent = metaCsrfToken;
        
        // 检查CSRF令牌按钮
        document.getElementById('check-csrf').addEventListener('click', async function() {
            try {
                const response = await fetch('/debug/check-csrf', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': metaCsrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ test: 'data' })
                });
                
                const data = await response.json();
                document.getElementById('server-csrf-token').textContent = data.csrf_token;
                console.log('CSRF check response:', data);
            } catch (error) {
                console.error('CSRF check failed:', error);
                document.getElementById('server-csrf-token').textContent = 'Error: ' + error.message;
            }
        });
        
        // 测试上传表单
        document.getElementById('test-upload-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const fileInput = document.getElementById('test-file');
            
            if (fileInput.files.length === 0) {
                alert('请选择一个文件');
                return;
            }
            
            formData.append('file', fileInput.files[0]);
            
            try {
                const response = await fetch('/debug/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': metaCsrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const resultElement = document.getElementById('upload-result');
                const resultContentElement = document.getElementById('upload-result-content');
                
                let resultText = '';
                
                try {
                    const responseText = await response.text();
                    resultText = responseText;
                    
                    try {
                        const data = JSON.parse(responseText);
                        console.log('Upload response:', data);
                        resultText = JSON.stringify(data, null, 2);
                    } catch (e) {
                        console.error('Failed to parse response as JSON:', e);
                    }
                } catch (e) {
                    resultText = 'Error reading response: ' + e.message;
                }
                
                resultContentElement.textContent = resultText;
                resultElement.classList.remove('hidden');
                
            } catch (error) {
                console.error('Upload failed:', error);
                alert('上传失败: ' + error.message);
            }
        });
    });
</script>
@endpush 