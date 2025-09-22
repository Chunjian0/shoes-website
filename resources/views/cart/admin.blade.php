<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('购物车管理') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- 数据分析组件 --}}
            <x-cart-analytics :stats="$stats" :chart_data="$chartData" :period="$period" />
            
            {{-- 过滤器组件 --}}
            <x-cart-filter :customers="$customers" :current-filters="$filters" />
            
            {{-- 批量操作工具栏 --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex-grow">
                        <div class="flex flex-wrap gap-2 items-center">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="select-all" class="text-sm text-gray-700 ml-1">全选</label>
                            
                            <span class="text-gray-500 text-sm" id="selected-count">(已选择 0 项)</span>
                            
                            <div class="relative inline-block text-left ml-3" id="bulk-actions">
                                <button type="button" class="bulk-action-btn inline-flex justify-center items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" disabled>
                                    批量操作
                                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div class="bulk-action-menu hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="bulk-action-menu-button" tabindex="-1">
                                    <div class="py-1" role="none">
                                        <button type="button" class="bulk-action-item text-gray-700 block px-4 py-2 text-sm w-full text-left hover:bg-gray-100 hover:text-gray-900" data-action="export">
                                            导出选中项
                                        </button>
                                        <button type="button" class="bulk-action-item text-gray-700 block px-4 py-2 text-sm w-full text-left hover:bg-gray-100 hover:text-gray-900" data-action="email">
                                            发送邮件提醒
                                        </button>
                                    </div>
                                    <div class="py-1" role="none">
                                        <button type="button" class="bulk-action-item text-gray-700 block px-4 py-2 text-sm w-full text-left hover:bg-gray-100 hover:text-gray-900" data-action="status-active">
                                            标记为活跃
                                        </button>
                                        <button type="button" class="bulk-action-item text-gray-700 block px-4 py-2 text-sm w-full text-left hover:bg-gray-100 hover:text-gray-900" data-action="status-abandoned">
                                            标记为废弃
                                        </button>
                                    </div>
                                    <div class="py-1" role="none">
                                        <button type="button" class="bulk-action-item text-red-700 block px-4 py-2 text-sm w-full text-left hover:bg-red-50 hover:text-red-900" data-action="delete">
                                            删除选中项
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('cart.export') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            导出全部
                        </a>
                        
                        <a href="{{ route('cart.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            创建购物车
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- 购物车列表 --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-0 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                        <span class="sr-only">选择</span>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        客户
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        状态
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        商品数
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        总价
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        最后更新
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($carts as $cart)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" class="cart-select rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="{{ $cart->id }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-blue-800 font-medium">{{ strtoupper(substr($cart->customer->name ?? 'G', 0, 1)) }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $cart->customer->name ?? 'Guest' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $cart->customer->email ?? 'No email' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cart->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($cart->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cart->items_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($cart->total, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cart->updated_at->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('cart.show', $cart->id) }}" class="text-blue-600 hover:text-blue-900">
                                                    查看
                                                </a>
                                                <a href="{{ route('cart.edit', $cart->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    编辑
                                                </a>
                                                <button type="button" class="text-red-600 hover:text-red-900 delete-cart" data-id="{{ $cart->id }}">
                                                    删除
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            没有找到符合条件的购物车
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- 分页 --}}
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $carts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 删除确认对话框 --}}
    <x-custom-dialog id="delete-cart-dialog" title="删除购物车">
        <p>您确定要删除此购物车吗？此操作无法撤销。</p>
        
        <x-slot name="icon">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </x-slot>
        
        <x-slot name="footer">
            <button type="button" class="dialog-confirm w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                删除
            </button>
            <button type="button" class="dialog-cancel mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                取消
            </button>
        </x-slot>
    </x-custom-dialog>
    
    {{-- 批量删除确认对话框 --}}
    <x-custom-dialog id="bulk-delete-dialog" title="批量删除购物车">
        <p>您确定要删除所有选中的购物车吗？此操作无法撤销。</p>
        
        <x-slot name="icon">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </x-slot>
        
        <x-slot name="footer">
            <button type="button" class="dialog-confirm w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                删除
            </button>
            <button type="button" class="dialog-cancel mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                取消
            </button>
        </x-slot>
    </x-custom-dialog>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 单个删除处理
            const deleteButtons = document.querySelectorAll('.delete-cart');
            let cartIdToDelete = null;
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    cartIdToDelete = this.dataset.id;
                    
                    if(window.showDialog) {
                        window.showDialog('delete-cart-dialog', () => {
                            deleteCart(cartIdToDelete);
                        });
                    } else {
                        if(confirm('您确定要删除此购物车吗？此操作无法撤销。')) {
                            deleteCart(cartIdToDelete);
                        }
                    }
                });
            });
            
            // 实际删除购物车的函数
            function deleteCart(id) {
                fetch(`/admin/cart/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // 页面上删除行
                        const row = document.querySelector(`input.cart-select[value="${id}"]`).closest('tr');
                        row.remove();
                        
                        // 显示成功消息
                        if(window.showToast) {
                            window.showToast('购物车已成功删除', 'success');
                        }
                    } else {
                        throw new Error(data.message || '删除失败');
                    }
                })
                .catch(error => {
                    console.error('删除失败:', error);
                    
                    if(window.showToast) {
                        window.showToast('删除购物车失败: ' + error.message, 'error');
                    } else {
                        alert('删除购物车失败: ' + error.message);
                    }
                });
            }
            
            // 全选功能
            const selectAllCheckbox = document.getElementById('select-all');
            const cartCheckboxes = document.querySelectorAll('.cart-select');
            const selectedCountEl = document.getElementById('selected-count');
            const bulkActionBtn = document.querySelector('.bulk-action-btn');
            
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                
                cartCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                
                updateSelectedCount();
            });
            
            cartCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
            
            function updateSelectedCount() {
                const selectedCount = document.querySelectorAll('.cart-select:checked').length;
                selectedCountEl.textContent = `(已选择 ${selectedCount} 项)`;
                
                if(selectedCount > 0) {
                    bulkActionBtn.removeAttribute('disabled');
                } else {
                    bulkActionBtn.setAttribute('disabled', 'disabled');
                }
            }
            
            // 批量操作菜单
            const bulkActionMenu = document.querySelector('.bulk-action-menu');
            
            document.addEventListener('click', function(e) {
                if(!e.target.closest('#bulk-actions') && bulkActionMenu.classList.contains('block')) {
                    bulkActionMenu.classList.remove('block');
                    bulkActionMenu.classList.add('hidden');
                }
            });
            
            bulkActionBtn.addEventListener('click', function() {
                bulkActionMenu.classList.toggle('hidden');
                bulkActionMenu.classList.toggle('block');
            });
            
            // 批量操作事件处理
            const bulkActionItems = document.querySelectorAll('.bulk-action-item');
            
            bulkActionItems.forEach(item => {
                item.addEventListener('click', function() {
                    const action = this.dataset.action;
                    const selectedIds = Array.from(document.querySelectorAll('.cart-select:checked')).map(el => el.value);
                    
                    if(selectedIds.length === 0) return;
                    
                    // 隐藏菜单
                    bulkActionMenu.classList.add('hidden');
                    bulkActionMenu.classList.remove('block');
                    
                    switch(action) {
                        case 'export':
                            exportCarts(selectedIds);
                            break;
                        case 'email':
                            sendEmailReminders(selectedIds);
                            break;
                        case 'status-active':
                        case 'status-abandoned':
                            updateCartsStatus(selectedIds, action.replace('status-', ''));
                            break;
                        case 'delete':
                            confirmBulkDelete(selectedIds);
                            break;
                    }
                });
            });
            
            // 确认批量删除
            function confirmBulkDelete(ids) {
                if(window.showDialog) {
                    window.showDialog('bulk-delete-dialog', () => {
                        bulkDeleteCarts(ids);
                    });
                } else {
                    if(confirm('您确定要删除所有选中的购物车吗？此操作无法撤销。')) {
                        bulkDeleteCarts(ids);
                    }
                }
            }
            
            // 执行批量删除
            function bulkDeleteCarts(ids) {
                fetch('/admin/cart/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // 页面上删除行
                        ids.forEach(id => {
                            const row = document.querySelector(`input.cart-select[value="${id}"]`).closest('tr');
                            row.remove();
                        });
                        
                        // 重置选中状态
                        selectAllCheckbox.checked = false;
                        updateSelectedCount();
                        
                        // 显示成功消息
                        if(window.showToast) {
                            window.showToast(`成功删除 ${ids.length} 个购物车`, 'success');
                        }
                    } else {
                        throw new Error(data.message || '批量删除失败');
                    }
                })
                .catch(error => {
                    console.error('批量删除失败:', error);
                    
                    if(window.showToast) {
                        window.showToast('批量删除失败: ' + error.message, 'error');
                    } else {
                        alert('批量删除失败: ' + error.message);
                    }
                });
            }
            
            // 导出购物车
            function exportCarts(ids) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/cart/export';
                
                // CSRF 令牌
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfInput);
                
                // 选中的 ID
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
            
            // 发送邮件提醒
            function sendEmailReminders(ids) {
                fetch('/admin/cart/send-reminders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        if(window.showToast) {
                            window.showToast(`已成功发送 ${data.sent} 封提醒邮件`, 'success');
                        }
                    } else {
                        throw new Error(data.message || '发送邮件失败');
                    }
                })
                .catch(error => {
                    console.error('发送邮件失败:', error);
                    
                    if(window.showToast) {
                        window.showToast('发送邮件失败: ' + error.message, 'error');
                    } else {
                        alert('发送邮件失败: ' + error.message);
                    }
                });
            }
            
            // 更新购物车状态
            function updateCartsStatus(ids, status) {
                fetch('/admin/cart/update-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: ids, status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // 更新页面上的状态标签
                        ids.forEach(id => {
                            const row = document.querySelector(`input.cart-select[value="${id}"]`).closest('tr');
                            const statusCell = row.querySelector('td:nth-child(3) span');
                            
                            statusCell.classList.remove('bg-green-100', 'text-green-800', 'bg-yellow-100', 'text-yellow-800');
                            
                            if(status === 'active') {
                                statusCell.classList.add('bg-green-100', 'text-green-800');
                                statusCell.textContent = 'Active';
                            } else {
                                statusCell.classList.add('bg-yellow-100', 'text-yellow-800');
                                statusCell.textContent = 'Abandoned';
                            }
                        });
                        
                        // 显示成功消息
                        if(window.showToast) {
                            window.showToast(`已成功更新 ${ids.length} 个购物车状态`, 'success');
                        }
                    } else {
                        throw new Error(data.message || '更新状态失败');
                    }
                })
                .catch(error => {
                    console.error('更新状态失败:', error);
                    
                    if(window.showToast) {
                        window.showToast('更新状态失败: ' + error.message, 'error');
                    } else {
                        alert('更新状态失败: ' + error.message);
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout> 