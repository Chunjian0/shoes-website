<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Verification Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">邮箱验证测试</h1>
        
        <div id="step1" class="space-y-4">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">邮箱地址</label>
                <input type="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="请输入邮箱">
            </div>
            
            <div class="flex items-center justify-between">
                <button id="sendCode" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    发送验证码
                </button>
            </div>
            
            <div id="sendResult" class="mt-4 hidden">
                <div id="sendSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded hidden">
                    验证码已发送，请查收邮件并输入验证码。
                </div>
                <div id="sendError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded hidden">
                    验证码发送失败，请稍后重试。
                </div>
            </div>
        </div>
        
        <div id="step2" class="space-y-4 hidden mt-6 border-t pt-4">
            <div class="mb-4">
                <label for="code" class="block text-gray-700 text-sm font-bold mb-2">验证码</label>
                <input type="text" id="code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="请输入收到的验证码">
            </div>
            
            <div class="flex items-center justify-between">
                <button id="verifyCode" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    验证
                </button>
            </div>
            
            <div id="verifyResult" class="mt-4 hidden">
                <div id="verifySuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded hidden">
                    验证成功！
                </div>
                <div id="verifyError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded hidden">
                    验证失败，请检查验证码是否正确。
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sendCodeBtn = document.getElementById('sendCode');
            const verifyCodeBtn = document.getElementById('verifyCode');
            const emailInput = document.getElementById('email');
            const codeInput = document.getElementById('code');
            
            // 发送验证码
            sendCodeBtn.addEventListener('click', async function() {
                const email = emailInput.value.trim();
                if (!email) {
                    alert('请输入邮箱地址');
                    return;
                }
                
                try {
                    sendCodeBtn.disabled = true;
                    sendCodeBtn.innerHTML = '发送中...';
                    
                    const response = await fetch('/api/customer/send-verification-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ email })
                    });
                    
                    if (!response.ok) {
                        throw new Error(`服务器返回错误: ${response.status}`);
                    }
                    
                    const result = await response.json();
                    
                    document.getElementById('sendResult').classList.remove('hidden');
                    
                    if (result.status === 'success') {
                        document.getElementById('sendSuccess').classList.remove('hidden');
                        document.getElementById('sendError').classList.add('hidden');
                        document.getElementById('step2').classList.remove('hidden');
                    } else {
                        document.getElementById('sendSuccess').classList.add('hidden');
                        document.getElementById('sendError').classList.remove('hidden');
                        document.getElementById('sendError').textContent = result.message || '验证码发送失败';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('sendResult').classList.remove('hidden');
                    document.getElementById('sendSuccess').classList.add('hidden');
                    document.getElementById('sendError').classList.remove('hidden');
                    document.getElementById('sendError').textContent = '网络错误，请稍后重试: ' + error.message;
                } finally {
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerHTML = '发送验证码';
                }
            });
            
            // 验证验证码
            verifyCodeBtn.addEventListener('click', async function() {
                const email = emailInput.value.trim();
                const code = codeInput.value.trim();
                
                if (!email || !code) {
                    alert('请输入邮箱和验证码');
                    return;
                }
                
                try {
                    verifyCodeBtn.disabled = true;
                    verifyCodeBtn.innerHTML = '验证中...';
                    
                    const response = await fetch('/api/customer/verify-email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ 
                            email,
                            verification_code: code
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error(`服务器返回错误: ${response.status}`);
                    }
                    
                    const result = await response.json();
                    
                    document.getElementById('verifyResult').classList.remove('hidden');
                    
                    if (result.status === 'success') {
                        document.getElementById('verifySuccess').classList.remove('hidden');
                        document.getElementById('verifyError').classList.add('hidden');
                    } else {
                        document.getElementById('verifySuccess').classList.add('hidden');
                        document.getElementById('verifyError').classList.remove('hidden');
                        document.getElementById('verifyError').textContent = result.message || '验证失败';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('verifyResult').classList.remove('hidden');
                    document.getElementById('verifySuccess').classList.add('hidden');
                    document.getElementById('verifyError').classList.remove('hidden');
                    document.getElementById('verifyError').textContent = '网络错误，请稍后重试: ' + error.message;
                } finally {
                    verifyCodeBtn.disabled = false;
                    verifyCodeBtn.innerHTML = '验证';
                }
            });
        });
    </script>
</body>
</html> 