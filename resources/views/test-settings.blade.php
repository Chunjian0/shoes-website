<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings Test Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Settings Test Page</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Save Test Setting</h2>
            <form id="test-form" class="space-y-4">
                <div>
                    <label for="group" class="block text-sm font-medium text-gray-700 mb-1">Group</label>
                    <input type="text" id="group" name="group" value="homepage" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="key" class="block text-sm font-medium text-gray-700 mb-1">Key</label>
                    <input type="text" id="key" name="key" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                    <input type="text" id="value" name="value"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Setting
                    </button>
                </div>
            </form>
            <div id="form-result" class="mt-4 p-4 border rounded-md hidden"></div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Homepage Settings</h2>
            <button id="load-homepage-settings" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 mb-4">
                Load Homepage Settings
            </button>
            <div id="homepage-settings-result" class="mt-2 overflow-x-auto">
                <pre class="p-4 bg-gray-100 rounded-md text-sm"></pre>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">All Settings</h2>
            <button id="load-all-settings" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 mb-4">
                Load All Settings
            </button>
            <div id="all-settings-result" class="mt-2 overflow-x-auto">
                <pre class="p-4 bg-gray-100 rounded-md text-sm"></pre>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testForm = document.getElementById('test-form');
            const formResult = document.getElementById('form-result');
            const loadHomepageSettings = document.getElementById('load-homepage-settings');
            const homepageSettingsResult = document.querySelector('#homepage-settings-result pre');
            const loadAllSettings = document.getElementById('load-all-settings');
            const allSettingsResult = document.querySelector('#all-settings-result pre');
            
            // 保存测试设置
            testForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const data = {
                    group: document.getElementById('group').value,
                    key: document.getElementById('key').value,
                    value: document.getElementById('value').value
                };
                
                fetch('/test/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    formResult.classList.remove('hidden');
                    formResult.innerHTML = `
                        <div class="${data.success ? 'text-green-600' : 'text-red-600'}">
                            <p class="font-semibold">${data.success ? 'Success' : 'Error'}</p>
                            <p>${data.message}</p>
                        </div>
                        ${data.setting ? `<pre class="mt-2 bg-gray-100 p-2 rounded">${JSON.stringify(data.setting, null, 2)}</pre>` : ''}
                    `;
                })
                .catch(error => {
                    formResult.classList.remove('hidden');
                    formResult.innerHTML = `
                        <div class="text-red-600">
                            <p class="font-semibold">Error</p>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
            });
            
            // 加载首页设置
            loadHomepageSettings.addEventListener('click', function() {
                homepageSettingsResult.textContent = 'Loading...';
                
                fetch('/test/settings/homepage')
                    .then(response => response.json())
                    .then(data => {
                        homepageSettingsResult.textContent = JSON.stringify(data, null, 2);
                    })
                    .catch(error => {
                        homepageSettingsResult.textContent = `Error: ${error.message}`;
                    });
            });
            
            // 加载所有设置
            loadAllSettings.addEventListener('click', function() {
                allSettingsResult.textContent = 'Loading...';
                
                fetch('/test/settings')
                    .then(response => response.json())
                    .then(data => {
                        allSettingsResult.textContent = JSON.stringify(data, null, 2);
                    })
                    .catch(error => {
                        allSettingsResult.textContent = `Error: ${error.message}`;
                    });
            });
        });
    </script>
</body>
</html> 