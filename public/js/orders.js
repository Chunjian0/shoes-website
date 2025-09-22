// 订单页面的JavaScript功能
document.addEventListener('DOMContentLoaded', function() {
    // 订单状态更新
    const updateStatusForm = document.getElementById('update-status-form');
    if (updateStatusForm) {
        updateStatusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const status = document.getElementById('status').value;
            if (!status) {
                showToast('Please select a status', 'error');
                return;
            }
            
            const formData = new FormData(updateStatusForm);
            const url = updateStatusForm.getAttribute('action');
            
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Order status updated successfully', 'success');
                    // 更新页面上的状态显示
                    document.getElementById('order-status').textContent = data.status;
                    document.getElementById('order-status').className = getStatusClass(data.status);
                } else {
                    showToast(data.message || 'Failed to update status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating status', 'error');
            });
        });
    }
    
    // 支付状态更新
    const updatePaymentForm = document.getElementById('update-payment-form');
    if (updatePaymentForm) {
        updatePaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const paymentStatus = document.getElementById('payment_status').value;
            if (!paymentStatus) {
                showToast('Please select a payment status', 'error');
                return;
            }
            
            const formData = new FormData(updatePaymentForm);
            const url = updatePaymentForm.getAttribute('action');
            
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Payment status updated successfully', 'success');
                    // 更新页面上的支付状态显示
                    document.getElementById('payment-status').textContent = data.payment_status;
                    document.getElementById('payment-status').className = getPaymentStatusClass(data.payment_status);
                    
                    // 如果有新的支付记录，刷新页面以显示
                    if (data.refresh) {
                        window.location.reload();
                    }
                } else {
                    showToast(data.message || 'Failed to update payment status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating payment status', 'error');
            });
        });
    }
    
    // 订单取消
    const cancelOrderForm = document.getElementById('cancel-order-form');
    if (cancelOrderForm) {
        cancelOrderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                return;
            }
            
            const url = cancelOrderForm.getAttribute('action');
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Order cancelled successfully', 'success');
                    // 更新页面上的状态显示
                    document.getElementById('order-status').textContent = 'Cancelled';
                    document.getElementById('order-status').className = getStatusClass('Cancelled');
                    
                    // 禁用取消按钮
                    document.getElementById('cancel-order-btn').disabled = true;
                    document.getElementById('cancel-order-btn').classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    showToast(data.message || 'Failed to cancel order', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while cancelling the order', 'error');
            });
        });
    }
    
    // 日期范围筛选
    const dateRangeForm = document.getElementById('date-range-form');
    if (dateRangeForm) {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        
        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                endDate.min = startDate.value;
                if (endDate.value && new Date(endDate.value) < new Date(startDate.value)) {
                    endDate.value = startDate.value;
                }
            });
            
            endDate.addEventListener('change', function() {
                startDate.max = endDate.value;
            });
        }
    }
});

// 显示Toast通知
function showToast(message, type = 'info') {
    // 检查是否已存在Toast容器
    let toastContainer = document.getElementById('toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50';
        document.body.appendChild(toastContainer);
    }
    
    // 创建新的Toast
    const toast = document.createElement('div');
    toast.className = 'mb-3 p-4 rounded-md shadow-md transform transition-all duration-300 ease-in-out';
    
    // 根据类型设置样式
    switch (type) {
        case 'success':
            toast.className += ' bg-green-100 border-l-4 border-green-500 text-green-700';
            break;
        case 'error':
            toast.className += ' bg-red-100 border-l-4 border-red-500 text-red-700';
            break;
        case 'warning':
            toast.className += ' bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700';
            break;
        default:
            toast.className += ' bg-blue-100 border-l-4 border-blue-500 text-blue-700';
    }
    
    // 设置内容
    toast.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="text-sm font-medium">${message}</div>
            </div>
            <button class="text-gray-400 hover:text-gray-500 focus:outline-none">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;
    
    // 添加到容器
    toastContainer.appendChild(toast);
    
    // 添加关闭按钮事件
    const closeButton = toast.querySelector('button');
    closeButton.addEventListener('click', function() {
        removeToast(toast);
    });
    
    // 自动关闭
    setTimeout(() => {
        removeToast(toast);
    }, 5000);
}

// 移除Toast
function removeToast(toast) {
    toast.classList.add('opacity-0', 'translate-x-full');
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

// 获取订单状态对应的CSS类
function getStatusClass(status) {
    switch (status.toLowerCase()) {
        case 'completed':
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
        case 'processing':
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
        case 'pending':
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
        default:
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
    }
}

// 获取支付状态对应的CSS类
function getPaymentStatusClass(status) {
    switch (status.toLowerCase()) {
        case 'paid':
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
        case 'partially_paid':
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
        case 'unpaid':
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
        default:
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
    }
} 