{{-- 
    产品模板 Toast 通知组件
    使用 SweetAlert2 实现全局通知功能
--}}

{{-- 使用说明:
    可以通过window.ToastSystem全局对象使用此组件:
    
    <!-- 成功通知 -->
    window.ToastSystem.success('产品模板已成功创建！');
    
    <!-- 错误通知 -->
    window.ToastSystem.error('无法删除产品模板，请稍后再试。');
    
    <!-- 信息通知 -->
    window.ToastSystem.info('产品模板现在可以使用了。');
    
    <!-- 警告通知 -->
    window.ToastSystem.warning('此操作将删除所有关联产品。');
    
    <!-- 通知配置 -->
    window.ToastSystem.success('产品模板已成功创建！', {
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
    if (window.ToastSystem) {
        return;
    }
    
    // 如果页面中没有Swal对象，我们加载SweetAlert2库
    if (typeof Swal === 'undefined') {
        // 动态加载SweetAlert2
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        script.async = true;
        document.head.appendChild(script);
        
        script.onload = function() {
            // 创建Toast系统
            createToastSystem();
        };
    } else {
        // SweetAlert2已存在，直接创建Toast系统
        createToastSystem();
    }
}

// 创建Toast系统
function createToastSystem() {
    // 创建基础Toast配置
    const baseToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    // 创建全局ToastSystem对象
    window.ToastSystem = {
        // 显示成功通知
        success: function(message, options = {}) {
            this.show(message, 'success', options);
        },
        
        // 显示错误通知
        error: function(message, options = {}) {
            this.show(message, 'error', options);
        },
        
        // 显示信息通知
        info: function(message, options = {}) {
            this.show(message, 'info', options);
        },
        
        // 显示警告通知
        warning: function(message, options = {}) {
            this.show(message, 'warning', options);
        },
        
        // 显示通知
        show: function(message, type, options = {}) {
            const duration = options.duration || 3000;
            const onClose = options.onClose || null;
            
            baseToast.fire({
                icon: type,
                title: message,
                timer: duration,
                didClose: onClose
            });
        }
    };
    
    // 为兼容性提供window.Toast简单接口
    window.Toast = {
        success: function(message) {
            window.ToastSystem.success(message);
        },
        
        error: function(message) {
            window.ToastSystem.error(message);
        },
        
        info: function(message) {
            window.ToastSystem.info(message);
        },
        
        warning: function(message) {
            window.ToastSystem.warning(message);
        },
        
        fire: function(options) {
            Swal.fire(options);
        }
    };
    
    console.log('产品模板Toast通知系统已初始化');
}
</script>

{{-- 原生备用Toast元素 --}}
<div id="native-toast-container" class="fixed top-4 right-4 z-50" style="display: none;">
    <div id="native-toast" class="bg-white rounded-lg shadow-lg max-w-sm overflow-hidden border-l-4 transform transition-all duration-300 ease-in-out">
        <div class="p-4 flex items-start">
            <div id="native-toast-icon" class="flex-shrink-0 mr-3">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"></svg>
            </div>
            <div class="w-full">
                <p id="native-toast-message" class="text-sm text-gray-800"></p>
            </div>
            <div class="ml-3 flex-shrink-0">
                <button onclick="document.getElementById('native-toast-container').style.display = 'none';" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div> 