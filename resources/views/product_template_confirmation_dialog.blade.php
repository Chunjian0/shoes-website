{{-- 确认对话框模板 --}}
<div id="confirmation-dialog" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- 背景遮罩 --}}
        <div id="confirmation-backdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        {{-- 使对话框垂直居中 --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- 对话框内容 --}}
        <div class="animate-fade-in inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="confirmation-icon-container" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        {{-- 图标将通过JS动态添加 --}}
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="confirmation-title">
                            {{-- 标题将通过JS动态添加 --}}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmation-message">
                                {{-- 内容将通过JS动态添加 --}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmation-confirm-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    {{-- 确认按钮文本将通过JS动态添加 --}}
                </button>
                <button type="button" id="confirmation-cancel-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initConfirmationDialog();
});

// 支持Turbolinks
document.addEventListener('turbolinks:load', function() {
    initConfirmationDialog();
});

function initConfirmationDialog() {
    // 避免重复初始化
    if (window.ConfirmationDialog !== undefined) return;
    
    // 创建确认对话框系统
    window.ConfirmationDialog = {
        // 对话框元素
        dialog: document.getElementById('confirmation-dialog'),
        backdrop: document.getElementById('confirmation-backdrop'),
        title: document.getElementById('confirmation-title'),
        message: document.getElementById('confirmation-message'),
        confirmBtn: document.getElementById('confirmation-confirm-btn'),
        cancelBtn: document.getElementById('confirmation-cancel-btn'),
        iconContainer: document.getElementById('confirmation-icon-container'),
        
        // 对话框类型
        types: {
            danger: {
                confirmBtnClass: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
                iconClass: 'bg-red-100',
                iconSvg: `<svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                          </svg>`
            },
            warning: {
                confirmBtnClass: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
                iconClass: 'bg-yellow-100',
                iconSvg: `<svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                          </svg>`
            },
            info: {
                confirmBtnClass: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
                iconClass: 'bg-blue-100',
                iconSvg: `<svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>`
            },
            success: {
                confirmBtnClass: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
                iconClass: 'bg-green-100',
                iconSvg: `<svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>`
            },
            default: {
                confirmBtnClass: 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
                iconClass: 'bg-indigo-100',
                iconSvg: `<svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>`
            }
        },
        
        // 默认选项
        defaultOptions: {
            type: 'default',
            confirmBtnText: 'Confirm',
            escapeKeyClose: true,
            backdropClose: true,
            focusConfirmBtn: true,
            onCancel: null
        },
        
        // 显示确认对话框
        show: function(title, message, onConfirm, options = {}) {
            // 合并选项
            const finalOptions = {...this.defaultOptions, ...options};
            const type = this.types[finalOptions.type] || this.types.default;
            
            // 设置内容
            this.title.textContent = title;
            this.message.textContent = message;
            this.confirmBtn.textContent = finalOptions.confirmBtnText;
            
            // 设置按钮样式
            this.confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm ' + type.confirmBtnClass;
            
            // 设置图标
            this.iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ' + type.iconClass;
            this.iconContainer.innerHTML = type.iconSvg;
            
            // 显示对话框
            this.dialog.classList.remove('hidden');
            
            // 如果需要，自动聚焦确认按钮
            if (finalOptions.focusConfirmBtn) {
                setTimeout(() => {
                    this.confirmBtn.focus();
                }, 100);
            }
            
            // 处理确认按钮点击
            const confirmHandler = () => {
                this.hide();
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            };
            
            // 处理取消按钮点击
            const cancelHandler = () => {
                this.hide();
                if (typeof finalOptions.onCancel === 'function') {
                    finalOptions.onCancel();
                }
            };
            
            // 处理Escape键关闭
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && finalOptions.escapeKeyClose) {
                    cancelHandler();
                }
            };
            
            // 处理背景点击关闭
            const backdropHandler = (e) => {
                if (e.target === this.backdrop && finalOptions.backdropClose) {
                    cancelHandler();
                }
            };
            
            // 绑定事件处理器
            this.confirmBtn.addEventListener('click', confirmHandler);
            this.cancelBtn.addEventListener('click', cancelHandler);
            document.addEventListener('keydown', escapeHandler);
            this.dialog.addEventListener('click', backdropHandler);
            
            // 存储清理函数
            this._cleanup = () => {
                this.confirmBtn.removeEventListener('click', confirmHandler);
                this.cancelBtn.removeEventListener('click', cancelHandler);
                document.removeEventListener('keydown', escapeHandler);
                this.dialog.removeEventListener('click', backdropHandler);
            };
        },
        
        // 隐藏对话框
        hide: function() {
            this.dialog.classList.add('hidden');
            
            // 清理事件监听器
            if (typeof this._cleanup === 'function') {
                this._cleanup();
                this._cleanup = null;
            }
        },
        
        // 便捷方法 - 危险确认
        danger: function(title, message, onConfirm, options = {}) {
            return this.show(title, message, onConfirm, {...options, type: 'danger'});
        },
        
        // 便捷方法 - 警告确认
        warning: function(title, message, onConfirm, options = {}) {
            return this.show(title, message, onConfirm, {...options, type: 'warning'});
        },
        
        // 便捷方法 - 信息确认
        info: function(title, message, onConfirm, options = {}) {
            return this.show(title, message, onConfirm, {...options, type: 'info'});
        },
        
        // 便捷方法 - 成功确认
        success: function(title, message, onConfirm, options = {}) {
            return this.show(title, message, onConfirm, {...options, type: 'success'});
        }
    };
}
</script>

{{-- 使用说明:
可以通过window.ConfirmationDialog全局对象使用此组件:

基本用法:
window.ConfirmationDialog.show(
    'Confirm Delete', 
    'Are you sure you want to delete this product template? This action cannot be undone.',
    function() {
        // 用户点击确认按钮时执行的操作
        document.getElementById('delete-form').submit();
    }
);

便捷方法:
window.ConfirmationDialog.danger(
    'Confirm Delete', 
    'Are you sure you want to delete this product template? This action cannot be undone.',
    function() {
        document.getElementById('delete-form').submit();
    }
);

高级选项:
window.ConfirmationDialog.danger(
    'Confirm Delete', 
    'Are you sure you want to delete this product template? This action cannot be undone.',
    function() {
        document.getElementById('delete-form').submit();
    },
    {
        confirmBtnText: 'Delete',             // 自定义确认按钮文本
        escapeKeyClose: true,                 // 是否允许按ESC键关闭对话框
        backdropClose: false,                 // 是否允许点击背景关闭对话框
        focusConfirmBtn: true,                // 是否自动聚焦确认按钮
        onCancel: function() {                // 取消时的回调
            console.log('User cancelled the operation');
        }
    }
);
--}} 