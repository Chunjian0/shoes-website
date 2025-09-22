@props(['id', 'title' => 'Confirmation'])

<div id="{{ $id }}" class="custom-dialog fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- 背景遮罩 --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
        
        {{-- 使模态框居中的技巧 --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        {{-- 对话框面板 --}}
        <div class="dialog-content inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full opacity-0 translate-y-4 scale-95">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        {{-- 默认图标 - 可以通过slot覆盖 --}}
                        @if(isset($icon))
                            {{ $icon }}
                        @else
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ $title }}
                        </h3>
                        <div class="mt-2">
                            <div class="text-sm text-gray-500">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                {{-- 默认按钮 - 可以通过slot覆盖 --}}
                @if(isset($footer))
                    {{ $footer }}
                @else
                    <button type="button" class="dialog-confirm w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Confirm
                    </button>
                    <button type="button" class="dialog-cancel mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        // 当DOM内容加载完成后初始化对话框功能
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化所有对话框
            document.querySelectorAll('.custom-dialog').forEach(dialog => {
                initDialog(dialog);
            });
            
            // 初始化对话框
            function initDialog(dialog) {
                const dialogContent = dialog.querySelector('.dialog-content');
                const cancelBtn = dialog.querySelector('.dialog-cancel');
                const confirmBtn = dialog.querySelector('.dialog-confirm');
                
                // 关闭对话框
                function closeDialog() {
                    dialog.classList.add('closing');
                    
                    // 动画效果
                    dialogContent.classList.remove('opacity-100', 'translate-y-0', 'scale-100');
                    dialogContent.classList.add('opacity-0', 'translate-y-4', 'scale-95');
                    
                    // 动画完成后隐藏对话框
                    setTimeout(() => {
                        dialog.classList.remove('flex');
                        dialog.classList.add('hidden');
                        dialog.classList.remove('closing');
                    }, 200);
                }
                
                // 打开对话框
                function openDialog() {
                    dialog.classList.remove('hidden');
                    dialog.classList.add('flex');
                    
                    // 强制重绘以启用过渡动画
                    void dialogContent.offsetWidth;
                    
                    // 动画效果
                    dialogContent.classList.remove('opacity-0', 'translate-y-4', 'scale-95');
                    dialogContent.classList.add('opacity-100', 'translate-y-0', 'scale-100');
                }
                
                // 设置取消按钮事件
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', closeDialog);
                }
                
                // 点击背景遮罩关闭对话框
                dialog.addEventListener('click', function(e) {
                    if (e.target === dialog && !dialog.classList.contains('closing')) {
                        closeDialog();
                    }
                });
                
                // 按ESC键关闭对话框
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !dialog.classList.contains('hidden') && !dialog.classList.contains('closing')) {
                        closeDialog();
                    }
                });
                
                // 增加打开对话框方法
                dialog.open = function(onConfirm, onCancel) {
                    // 打开对话框
                    openDialog();
                    
                    // 设置确认按钮事件
                    if (confirmBtn) {
                        // 移除之前的事件监听器
                        const newConfirmBtn = confirmBtn.cloneNode(true);
                        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                        
                        // 添加新的事件监听器
                        newConfirmBtn.addEventListener('click', function() {
                            if (typeof onConfirm === 'function') {
                                onConfirm();
                            }
                            closeDialog();
                        });
                    }
                    
                    // 设置取消按钮事件
                    if (cancelBtn) {
                        // 移除之前的事件监听器
                        const newCancelBtn = cancelBtn.cloneNode(true);
                        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
                        
                        // 添加新的事件监听器
                        newCancelBtn.addEventListener('click', function() {
                            if (typeof onCancel === 'function') {
                                onCancel();
                            }
                            closeDialog();
                        });
                    }
                };
                
                // 增加关闭对话框方法
                dialog.close = closeDialog;
            }
            
            // 全局对话框函数
            window.showDialog = function(id, onConfirm, onCancel) {
                const dialog = document.getElementById(id);
                if (dialog && dialog.open) {
                    dialog.open(onConfirm, onCancel);
                    return true;
                }
                return false;
            };
        });
    </script>
    @endpush
@endonce 