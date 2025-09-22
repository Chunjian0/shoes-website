/**
 * 购物车功能增强脚本
 * 提供动画、交互效果和多设备支持
 */

(function() {
    'use strict';
    
    // 存储全局变量和设置
    const cartSettings = {
        animationEnabled: true,
        touchDevice: ('ontouchstart' in window) || navigator.maxTouchPoints > 0,
        cartIconSelector: '.cart-icon',
        debounceDelay: 500,
        loadingOverlayDelay: 300
    };
    
    /**
     * 初始化
     */
    function init() {
        // 确保DOM已加载
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', onDOMReady);
        } else {
            onDOMReady();
        }
        
        // 为Livewire支持添加Turbolinks/LivewireEvents监听
        document.addEventListener('turbolinks:load', onDOMReady);
        document.addEventListener('livewire:load', onDOMReady);
    }
    
    /**
     * DOM加载完成后执行
     */
    function onDOMReady() {
        // 初始化购物车项事件
        initCartItems();
        
        // 初始化添加到购物车按钮
        initAddToCartButtons();
        
        // 初始化自定义对话框
        initCustomDialogs();
        
        // 初始化清空购物车按钮
        initClearCartButton();
        
        // 初始化结账按钮动画
        initCheckoutButton();
        
        // 初始化空购物车动画
        initEmptyCartAnimation();
    }
    
    /**
     * 初始化购物车项
     */
    function initCartItems() {
        // 获取所有购物车项
        const cartItems = document.querySelectorAll('.cart-item');
        
        cartItems.forEach(item => {
            const itemId = item.dataset.id;
            const quantityInput = item.querySelector('.quantity-input');
            const decreaseBtn = item.querySelector('.minus-btn');
            const increaseBtn = item.querySelector('.plus-btn');
            const removeBtn = item.querySelector('.remove-item');
            
            // 数量减少按钮
            if (decreaseBtn) {
                decreaseBtn.addEventListener('click', () => {
                    if (quantityInput.value > 1) {
                        const newQuantity = parseInt(quantityInput.value) - 1;
                        updateCartItemWithAnimation(itemId, newQuantity, quantityInput, item);
                    }
                });
            }
            
            // 数量增加按钮
            if (increaseBtn) {
                increaseBtn.addEventListener('click', () => {
                    const newQuantity = parseInt(quantityInput.value) + 1;
                    updateCartItemWithAnimation(itemId, newQuantity, quantityInput, item);
                });
            }
            
            // 数量输入框变更
            if (quantityInput) {
                quantityInput.addEventListener('change', debounce(() => {
                    let quantity = parseInt(quantityInput.value);
                    if (isNaN(quantity) || quantity < 1) {
                        quantity = 1;
                        quantityInput.value = 1;
                    }
                    updateCartItemWithAnimation(itemId, quantity, quantityInput, item);
                }, cartSettings.debounceDelay));
            }
            
            // 移除按钮
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    confirmRemoveItem(itemId, item);
                });
            }
        });
    }
    
    /**
     * 使用动画更新购物车项
     */
    function updateCartItemWithAnimation(itemId, quantity, inputEl, itemEl) {
        // 显示加载指示器
        const loadingIndicator = itemEl.querySelector('.loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }
        
        // 添加数量变更动画
        inputEl.classList.add('quantity-change');
        setTimeout(() => {
            inputEl.classList.remove('quantity-change');
        }, 500);
        
        // 调用API更新购物车
        updateCartItem(itemId, quantity)
            .then(data => {
                if (data.success) {
                    // 更新显示
                    const totalEl = itemEl.querySelector('.item-total');
                    if (totalEl) {
                        totalEl.textContent = formatCurrency(data.item_total);
                    }
                    
                    // 更新购物车总计
                    updateCartTotal(data.cart_total);
                    
                    // 更新购物车数量
                    updateCartCount(data.cart_count);
                    
                    // 高亮动画
                    itemEl.classList.add('updated');
                    setTimeout(() => {
                        itemEl.classList.remove('updated');
                    }, 1000);
                    
                    // 显示成功通知
                    showToast('Cart updated successfully', 'success');
                }
            })
            .catch(error => {
                console.error('Failed to update cart:', error);
                showToast('Failed to update cart', 'error');
                
                // 恢复原始值
                inputEl.value = inputEl.defaultValue;
            })
            .finally(() => {
                // 隐藏加载指示器
                if (loadingIndicator) {
                    setTimeout(() => {
                        loadingIndicator.classList.add('hidden');
                    }, 300);
                }
            });
    }
    
    /**
     * 确认移除项目
     */
    function confirmRemoveItem(itemId, itemEl) {
        // 使用自定义对话框替代confirm
        if (window.showDialog) {
            window.showDialog('confirm-remove-dialog', () => {
                removeCartItemWithAnimation(itemId, itemEl);
            });
        } else {
            // 回退到原生confirm
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeCartItemWithAnimation(itemId, itemEl);
            }
        }
    }
    
    /**
     * 带动画地移除购物车项
     */
    function removeCartItemWithAnimation(itemId, itemEl) {
        // 添加移除动画
        itemEl.classList.add('removing');
        
        // 延迟执行API调用，让动画有时间执行
        setTimeout(() => {
            removeCartItem(itemId)
                .then(data => {
                    if (data.success) {
                        // 动画完成后移除元素
                        setTimeout(() => {
                            itemEl.remove();
                        }, 300);
                        
                        // 更新购物车总计
                        updateCartTotal(data.cart_total);
                        
                        // 更新购物车数量
                        updateCartCount(data.cart_count);
                        
                        // 显示成功通知
                        showToast('Item removed from cart', 'success');
                        
                        // 如果购物车为空，重新加载页面
                        if (data.cart_count === 0) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    }
                })
                .catch(error => {
                    console.error('Failed to remove item:', error);
                    showToast('Failed to remove item', 'error');
                    
                    // 恢复元素
                    itemEl.classList.remove('removing');
                });
        }, 300);
    }
    
    /**
     * 初始化清空购物车按钮
     */
    function initClearCartButton() {
        const clearCartBtn = document.getElementById('clear-cart');
        
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', () => {
                // 使用自定义对话框替代confirm
                if (window.showDialog) {
                    window.showDialog('confirm-clear-dialog', () => {
                        clearCart();
                    });
                } else {
                    // 回退到原生confirm
                    if (confirm('Are you sure you want to clear your entire cart?')) {
                        clearCart();
                    }
                }
            });
        }
    }
    
    /**
     * 初始化结账按钮动画
     */
    function initCheckoutButton() {
        const checkoutBtn = document.querySelector('a[href*="checkout"]');
        
        if (checkoutBtn) {
            checkoutBtn.classList.add('checkout-button');
        }
    }
    
    /**
     * 初始化添加到购物车按钮
     */
    function initAddToCartButtons() {
        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
        
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.closest('.add-to-cart-form');
                if (!form) return;
                
                const productId = form.dataset.productId;
                const quantityInput = form.querySelector('.quantity-input');
                const quantity = parseInt(quantityInput.value);
                
                // 收集规格
                const specifications = {};
                form.querySelectorAll('.specification-select').forEach(select => {
                    if (select.value) {
                        specifications[select.dataset.name] = select.value;
                    }
                });
                
                // 添加飞入购物车动画
                const buttonRect = this.getBoundingClientRect();
                const cartIconEl = document.querySelector(cartSettings.cartIconSelector);
                
                if (cartIconEl && cartSettings.animationEnabled) {
                    // 创建飞入元素
                    const flyEl = document.createElement('div');
                    flyEl.className = 'add-to-cart-fly';
                    flyEl.style.width = '30px';
                    flyEl.style.height = '30px';
                    flyEl.innerHTML = '<span>+1</span>';
                    flyEl.style.left = `${buttonRect.left + buttonRect.width / 2 - 15}px`;
                    flyEl.style.top = `${buttonRect.top + buttonRect.height / 2 - 15}px`;
                    
                    // 计算飞行终点
                    const cartRect = cartIconEl.getBoundingClientRect();
                    const flyX = cartRect.left - buttonRect.left + cartRect.width / 2 - buttonRect.width / 2;
                    const flyY = cartRect.top - buttonRect.top + cartRect.height / 2 - buttonRect.height / 2;
                    
                    flyEl.style.setProperty('--fly-x', `${flyX}px`);
                    flyEl.style.setProperty('--fly-y', `${flyY}px`);
                    
                    document.body.appendChild(flyEl);
                    
                    // 动画完成后删除元素和执行购物车图标动画
                    setTimeout(() => {
                        flyEl.remove();
                        cartIconEl.classList.add('bounce');
                        setTimeout(() => {
                            cartIconEl.classList.remove('bounce');
                        }, 600);
                    }, 1000);
                }
                
                // 调用API添加到购物车
                addToCart(productId, quantity, specifications);
            });
        });
    }
    
    /**
     * 初始化自定义对话框
     */
    function initCustomDialogs() {
        // 检查对话框是否已存在
        if (!document.getElementById('confirm-remove-dialog')) {
            // 创建移除确认对话框
            const removeDialog = createCustomDialog(
                'confirm-remove-dialog',
                'Remove Item',
                'Are you sure you want to remove this item from your cart?',
                'warning'
            );
            document.body.appendChild(removeDialog);
        }
        
        if (!document.getElementById('confirm-clear-dialog')) {
            // 创建清空购物车确认对话框
            const clearDialog = createCustomDialog(
                'confirm-clear-dialog',
                'Clear Cart',
                'Are you sure you want to remove all items from your cart? This action cannot be undone.',
                'warning'
            );
            document.body.appendChild(clearDialog);
        }
    }
    
    /**
     * 创建自定义对话框元素
     */
    function createCustomDialog(id, title, message, type = 'info') {
        // 创建对话框容器
        const dialog = document.createElement('div');
        dialog.id = id;
        dialog.className = 'custom-dialog fixed inset-0 z-50 hidden overflow-y-auto';
        dialog.setAttribute('aria-labelledby', 'modal-title');
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        
        // 对话框内容
        let iconHtml = '';
        if (type === 'warning') {
            iconHtml = `<svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>`;
        } else {
            iconHtml = `<svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`;
        }
        
        dialog.innerHTML = `
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="dialog-content inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full opacity-0 translate-y-4 scale-95">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full ${type === 'warning' ? 'bg-yellow-100' : 'bg-blue-100'} sm:mx-0 sm:h-10 sm:w-10">
                                ${iconHtml}
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    ${title}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">${message}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="dialog-confirm w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 ${type === 'warning' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 ${type === 'warning' ? 'focus:ring-red-500' : 'focus:ring-blue-500'} sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Confirm
                        </button>
                        <button type="button" class="dialog-cancel mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return dialog;
    }
    
    /**
     * 初始化空购物车动画
     */
    function initEmptyCartAnimation() {
        const emptyCartContainer = document.querySelector('.text-center.py-12');
        
        if (emptyCartContainer) {
            emptyCartContainer.classList.add('empty-cart-container');
            
            const cartIcon = emptyCartContainer.querySelector('svg');
            if (cartIcon) {
                cartIcon.classList.add('empty-cart-animation');
            }
        }
    }
    
    /**
     * 更新购物车总计
     */
    function updateCartTotal(total) {
        const cartTotalEl = document.getElementById('cart-total');
        if (cartTotalEl) {
            cartTotalEl.textContent = formatCurrency(total);
        }
    }
    
    /**
     * 更新购物车数量
     */
    function updateCartCount(count) {
        const cartCountEl = document.getElementById('cart-count');
        if (cartCountEl) {
            cartCountEl.textContent = count;
            
            if (count === 0) {
                cartCountEl.classList.add('hidden');
            } else {
                cartCountEl.classList.remove('hidden');
            }
        }
    }
    
    /**
     * 显示Toast通知
     */
    function showToast(message, type = 'success') {
        // 检查是否有自定义Toast系统
        if (window.showToast) {
            window.showToast(message, type);
            return;
        }
        
        // 检查toast容器是否存在，如果不存在则创建
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col space-y-4';
            document.body.appendChild(toastContainer);
        }
        
        // 创建toast元素
        const toast = document.createElement('div');
        toast.className = `px-4 py-3 rounded shadow-lg flex items-center toast-enter ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
        
        // 添加图标
        const icon = document.createElement('span');
        if (type === 'success') {
            icon.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>`;
        } else {
            icon.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>`;
        }
        
        // 添加消息
        const messageEl = document.createElement('span');
        messageEl.textContent = message;
        
        // 组装toast
        toast.appendChild(icon);
        toast.appendChild(messageEl);
        
        // 添加到容器
        toastContainer.appendChild(toast);
        
        // 3秒后移除
        setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            
            setTimeout(() => {
                toast.remove();
                
                // 如果容器为空则移除
                if (toastContainer.children.length === 0) {
                    toastContainer.remove();
                }
            }, 300);
        }, 3000);
    }
    
    /**
     * API函数：更新购物车项
     */
    function updateCartItem(itemId, quantity) {
        return fetch(`/cart/update/${itemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken()
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => response.json())
        .catch(error => {
            console.error('API Error:', error);
            throw error;
        });
    }
    
    /**
     * API函数：移除购物车项
     */
    function removeCartItem(itemId) {
        return fetch(`/cart/remove/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCSRFToken()
            }
        })
        .then(response => response.json())
        .catch(error => {
            console.error('API Error:', error);
            throw error;
        });
    }
    
    /**
     * API函数：清空购物车
     */
    function clearCart() {
        return fetch('/cart/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCSRFToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Cart cleared successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('API Error:', error);
            showToast('Failed to clear cart', 'error');
        });
    }
    
    /**
     * API函数：添加到购物车
     */
    function addToCart(productId, quantity, specifications = {}) {
        return fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken()
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
                specifications: specifications
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 更新购物车数量
                updateCartCount(data.cart_count);
                
                // 显示成功通知
                showToast('Product added to cart', 'success');
            }
        })
        .catch(error => {
            console.error('API Error:', error);
            showToast('Failed to add product to cart', 'error');
        });
    }
    
    /**
     * 获取CSRF Token
     */
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }
    
    /**
     * 格式化货币
     */
    function formatCurrency(value) {
        return parseFloat(value).toFixed(2);
    }
    
    /**
     * 防抖函数
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // 初始化
    init();
})(); 