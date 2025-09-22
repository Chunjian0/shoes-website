{{-- 全局Toast通知组件 --}}
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col items-end space-y-2">
    {{-- Toast消息会通过JavaScript动态插入到这里 --}}
</div>

{{-- Toast模板 (被JavaScript克隆使用) --}}
<template id="toast-template">
    <div class="toast-message animate-slide-in-right max-w-md bg-white shadow-lg rounded-lg pointer-events-auto flex items-center border-l-4 overflow-hidden"
         role="alert">
        <div class="flex p-4 w-full items-center">
            <div class="flex-shrink-0 toast-icon-container">
                {{-- 图标将由JavaScript动态添加 --}}
            </div>
            <div class="ml-3 w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 toast-title"></p>
                <p class="mt-1 text-sm text-gray-500 toast-message-body"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 toast-close-btn">
                    <span class="sr-only">关闭</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="toast-progress-container w-full h-1 bg-gray-200 absolute bottom-0 left-0">
            <div class="toast-progress-bar h-full" style="width: 100%;"></div>
        </div>
    </div>
</template>

{{-- 使用说明:
    可以通过window.ToastSystem全局对象使用此组件:
    
    <!-- 成功通知 -->
    window.ToastSystem.success('操作成功', '产品模板已成功创建！');
    
    <!-- 错误通知 -->
    window.ToastSystem.error('操作失败', '无法删除产品模板，请稍后再试。');
    
    <!-- 信息通知 -->
    window.ToastSystem.info('提示信息', '产品模板现在可以使用了。');
    
    <!-- 警告通知 -->
    window.ToastSystem.warning('请注意', '此操作将删除所有关联产品。');
    
    <!-- 通知配置 -->
    window.ToastSystem.success('操作成功', '产品模板已成功创建！', {
        duration: 5000,     // 持续时间(毫秒)
        onClose: function() { // 关闭时回调
            console.log('Toast已关闭');
        }
    });
--}}

<script>
// 确保页面加载后再初始化
document.addEventListener('DOMContentLoaded', function() {
    initToastSystem();
});

// 支持Turbolinks
document.addEventListener('turbolinks:load', function() {
    initToastSystem();
});

// 初始化Toast系统
function initToastSystem() {
    // 避免重复初始化
    if (window.ToastSystem !== undefined) return;
    
    // 创建Toast系统对象
    window.ToastSystem = {
        // 存储当前显示的Toast
        toasts: [],
        
        // 默认配置
        defaultOptions: {
            duration: 4000,  // 默认持续时间
            progressBar: true, // 显示进度条
            onClose: null    // 关闭回调
        },
        
        // 创建Toast
        create: function(title, message, type, options) {
            // 合并选项
            const finalOptions = {...this.defaultOptions, ...options};
            
            // 获取模板和容器
            const template = document.getElementById('toast-template');
            const container = document.getElementById('toast-container');
            
            if (!template || !container) return null;
            
            // 克隆模板
            const toast = template.content.cloneNode(true).querySelector('.toast-message');
            
            // 设置标题和消息
            toast.querySelector('.toast-title').textContent = title;
            toast.querySelector('.toast-message-body').textContent = message;
            
            // 根据类型设置样式
            switch(type) {
                case 'success':
                    toast.classList.add('border-green-500');
                    toast.querySelector('.toast-progress-bar').classList.add('bg-green-500');
                    toast.querySelector('.toast-icon-container').innerHTML = this.getSuccessIcon();
                    break;
                case 'error':
                    toast.classList.add('border-red-500');
                    toast.querySelector('.toast-progress-bar').classList.add('bg-red-500');
                    toast.querySelector('.toast-icon-container').innerHTML = this.getErrorIcon();
                    break;
                case 'warning':
                    toast.classList.add('border-yellow-500');
                    toast.querySelector('.toast-progress-bar').classList.add('bg-yellow-500');
                    toast.querySelector('.toast-icon-container').innerHTML = this.getWarningIcon();
                    break;
                case 'info':
                default:
                    toast.classList.add('border-blue-500');
                    toast.querySelector('.toast-progress-bar').classList.add('bg-blue-500');
                    toast.querySelector('.toast-icon-container').innerHTML = this.getInfoIcon();
                    break;
            }
            
            // 添加到容器
            container.appendChild(toast);
            
            // 处理进度条
            let progressBar = null;
            if (finalOptions.progressBar) {
                progressBar = toast.querySelector('.toast-progress-bar');
                progressBar.style.transition = `width ${finalOptions.duration}ms linear`;
                
                // 给浏览器一点时间来处理初始化
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 50);
            } else {
                toast.querySelector('.toast-progress-container').style.display = 'none';
            }
            
            // 设置关闭事件
            toast.querySelector('.toast-close-btn').addEventListener('click', () => {
                this.closeToast(toast, finalOptions.onClose);
            });
            
            // 设置自动关闭
            const toastData = {
                element: toast,
                timeout: setTimeout(() => {
                    this.closeToast(toast, finalOptions.onClose);
                }, finalOptions.duration)
            };
            
            // 保存Toast引用
            this.toasts.push(toastData);
            
            return toastData;
        },
        
        // 关闭Toast
        closeToast: function(toast, callback) {
            // 查找Toast索引
            const index = this.toasts.findIndex(t => t.element === toast);
            
            if (index === -1) return;
            
            // 获取Toast数据
            const toastData = this.toasts[index];
            
            // 清除超时
            clearTimeout(toastData.timeout);
            
            // 添加关闭动画
            toast.classList.remove('animate-slide-in-right');
            toast.classList.add('animate-slide-out-right');
            
            // 动画完成后移除元素
            toast.addEventListener('animationend', () => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
                
                // 从数组中移除
                this.toasts.splice(index, 1);
                
                // 执行回调
                if (typeof callback === 'function') {
                    callback();
                }
            });
        },
        
        // 成功Toast
        success: function(title, message, options) {
            return this.create(title, message, 'success', options);
        },
        
        // 错误Toast
        error: function(title, message, options) {
            return this.create(title, message, 'error', options);
        },
        
        // 警告Toast
        warning: function(title, message, options) {
            return this.create(title, message, 'warning', options);
        },
        
        // 信息Toast
        info: function(title, message, options) {
            return this.create(title, message, 'info', options);
        },
        
        // 清除所有Toast
        clear: function() {
            this.toasts.forEach(toast => {
                if (toast.element.parentNode) {
                    toast.element.parentNode.removeChild(toast.element);
                }
                clearTimeout(toast.timeout);
            });
            this.toasts = [];
        },
        
        // 图标
        getSuccessIcon: function() {
            return `<div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>`;
        },
        
        getErrorIcon: function() {
            return `<div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>`;
        },
        
        getWarningIcon: function() {
            return `<div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>`;
        },
        
        getInfoIcon: function() {
            return `<div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>`;
        }
    };
}
</script> 