/**
 * 首页管理系统 - 通知系统组件
 * 
 * 包含以下功能：
 * 1. Toast通知系统 - 替代alert
 * 2. 确认对话框 - 替代confirm
 * 3. 接收人选择器功能
 */

// 使用IIFE防止全局变量污染并防止重复初始化
(function() {
    // 避免重复初始化
    if (window.NotificationSystem) {
        return;
    }
    
    // 存储初始化状态
    let initialized = false;
    
    // 初始化函数
    function initializeNotificationSystem() {
        // 避免重复初始化
        if (initialized) {
            return;
        }
        initialized = true;
        
        console.log('初始化通知系统...');
        
        // Toast通知系统 - 使用已有的SweetAlert2
        const ToastSystem = {
            // 显示成功通知
            success: function(message, duration = 3000) {
                if (typeof window.Toast !== 'undefined') {
                    window.Toast.fire({
                        icon: 'success',
                        title: message,
                        timer: duration
                    });
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: message,
                        showConfirmButton: false,
                        timer: duration,
                        timerProgressBar: true
                    });
                } else {
                    // 备用方案，使用原生alert
                    alert(message);
                }
            },
            
            // 显示错误通知
            error: function(message, duration = 4000) {
                if (typeof window.Toast !== 'undefined') {
                    window.Toast.fire({
                        icon: 'error',
                        title: message,
                        timer: duration
                    });
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: message,
                        showConfirmButton: false,
                        timer: duration,
                        timerProgressBar: true
                    });
                } else {
                    // 备用方案，使用原生alert
                    alert('错误: ' + message);
                }
            },
            
            // 显示信息通知
            info: function(message, duration = 3000) {
                if (typeof window.Toast !== 'undefined') {
                    window.Toast.fire({
                        icon: 'info',
                        title: message,
                        timer: duration
                    });
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: message,
                        showConfirmButton: false,
                        timer: duration,
                        timerProgressBar: true
                    });
                } else {
                    // 备用方案，使用原生alert
                    alert(message);
                }
            },
            
            // 显示警告通知
            warning: function(message, duration = 3500) {
                if (typeof window.Toast !== 'undefined') {
                    window.Toast.fire({
                        icon: 'warning',
                        title: message,
                        timer: duration
                    });
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: message,
                        showConfirmButton: false,
                        timer: duration,
                        timerProgressBar: true
                    });
                } else {
                    // 备用方案，使用原生alert
                    alert('警告: ' + message);
                }
            }
        };
        
        // 确认对话框 - 使用SweetAlert2
        const ConfirmDialog = {
            // 显示确认对话框
            show: function(options = {}) {
                return new Promise((resolve) => {
                    const defaults = {
                        title: '确认操作',
                        message: '您确定要执行此操作吗？',
                        confirmText: '确认',
                        cancelText: '取消',
                        type: 'warning'
                    };
                    
                    const config = { ...defaults, ...options };
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: config.title,
                            text: config.message,
                            icon: config.type,
                            showCancelButton: true,
                            confirmButtonText: config.confirmText,
                            cancelButtonText: config.cancelText,
                            reverseButtons: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then((result) => {
                            resolve(result.isConfirmed);
                        });
                    } else {
                        // 备用方案，使用原生confirm
                        resolve(confirm(config.message));
                    }
                });
            }
        };
        
        // 接收人选择器功能初始化
        function initializeRecipientSelectors() {
            // 切换接收人选择器的显示/隐藏
            document.querySelectorAll('.toggle-recipients').forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.dataset.type;
                    const selector = document.getElementById('selector-' + type);
                    if (selector) {
                        selector.classList.toggle('hidden');
                        // 更新图标
                        const icon = this.querySelector('.toggle-icon');
                        if (icon) {
                            if (selector.classList.contains('hidden')) {
                                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>';
                            } else {
                                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>';
                            }
                        }
                    }
                });
            });
            
            // 接收人搜索功能
            document.querySelectorAll('.recipient-search').forEach(input => {
                input.addEventListener('input', function() {
                    const type = this.dataset.type;
                    const searchTerm = this.value.toLowerCase();
                    const userList = document.querySelector(`.user-list[data-type="${type}"]`);
                    
                    if (userList) {
                        userList.querySelectorAll('.user-item').forEach(item => {
                            const name = item.querySelector('.text-sm').textContent.toLowerCase();
                            const email = item.querySelector('.text-xs').textContent.toLowerCase();
                            
                            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                                item.style.display = '';
                            } else {
                                item.style.display = 'none';
                            }
                        });
                    }
                });
            });
            
            // 全选按钮
            document.querySelectorAll('.select-all-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.dataset.type;
                    const userList = document.querySelector(`.user-list[data-type="${type}"]`);
                    
                    if (userList) {
                        userList.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(checkbox => {
                            checkbox.checked = true;
                        });
                        updateSelectedRecipients(type);
                    }
                });
            });
            
            // 清空按钮
            document.querySelectorAll('.deselect-all-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.dataset.type;
                    const userList = document.querySelector(`.user-list[data-type="${type}"]`);
                    
                    if (userList) {
                        userList.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                            checkbox.checked = false;
                        });
                        updateSelectedRecipients(type);
                    }
                });
            });
            
            // 复选框变更事件
            document.querySelectorAll('.user-list input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const type = this.name.match(/\[(.*?)\]/)[1];
                    updateSelectedRecipients(type);
                });
            });
            
            // 更新已选接收人显示
            function updateSelectedRecipients(type) {
                const selectedContainer = document.getElementById('selected-' + type);
                const checkboxes = document.querySelectorAll(`input[name="receivers[${type}][]"]:checked`);
                
                if (selectedContainer) {
                    if (checkboxes.length === 0) {
                        selectedContainer.innerHTML = '<div class="text-sm text-gray-500 italic">未选择接收人</div>';
                    } else {
                        let html = '<div class="flex flex-wrap gap-2">';
                        checkboxes.forEach(checkbox => {
                            const label = checkbox.closest('.user-item');
                            const name = label.querySelector('.text-sm').textContent;
                            html += `
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ${name}
                                    <button type="button" class="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full bg-blue-200 hover:bg-blue-300 focus:outline-none" 
                                        onclick="this.parentNode.remove(); document.querySelector('input[value=\\'${checkbox.value}\\']').checked = false; updateSelectedRecipients('${type}');">
                                        <span class="sr-only">移除</span>
                                        <svg class="h-2 w-2 text-blue-700" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                            <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                        </svg>
                                    </button>
                                </span>
                            `;
                        });
                        html += '</div>';
                        selectedContainer.innerHTML = html;
                    }
                }
            }
            
            // 初始化所有接收人选择器的显示
            document.querySelectorAll('[id^="selected-"]').forEach(container => {
                const type = container.id.replace('selected-', '');
                updateSelectedRecipients(type);
            });
        }
        
        // 重写原生alert和confirm
        const originalAlert = window.alert;
        const originalConfirm = window.confirm;
        
        window.alert = function(message) {
            ToastSystem.info(message);
        };
        
        window.confirm = function(message) {
            return ConfirmDialog.show({ message: message });
        };
        
        // 将系统导出到全局
        window.NotificationSystem = {
            Toast: ToastSystem,
            ConfirmDialog: ConfirmDialog,
            initializeRecipientSelectors: initializeRecipientSelectors,
            // 恢复原生方法
            restoreNative: function() {
                window.alert = originalAlert;
                window.confirm = originalConfirm;
            }
        };
        
        // 尝试初始化接收人选择器
        try {
            initializeRecipientSelectors();
        } catch (error) {
            console.error('初始化接收人选择器时出错:', error);
        }
    }
    
    // 在多个事件上监听，确保在不同加载情况下都能正确初始化
    ['DOMContentLoaded', 'turbolinks:load', 'livewire:load'].forEach(event => {
        document.addEventListener(event, initializeNotificationSystem);
    });
    
    // 如果文档已经加载完成，立即初始化
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initializeNotificationSystem();
    }
})(); 