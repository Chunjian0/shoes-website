/**
 * 购物车触摸手势支持
 * 为触摸设备提供增强的交互体验
 */

(function() {
    'use strict';
    
    // 配置
    const config = {
        // 触发删除操作的最小滑动距离
        deleteThreshold: 100,
        // 最小滑动距离，用于区分点击和滑动
        minSwipeDistance: 30,
        // 拖动动画持续时间
        animationDuration: 300,
        // 是否使用3D变换以提高性能
        use3D: true,
        // 是否启用振动反馈 (如果设备支持)
        enableVibration: true,
        // 震动持续时间(毫秒)
        vibrationDuration: 50
    };
    
    // 存储触摸状态
    let touchState = {
        startX: 0,
        startY: 0,
        currentX: 0,
        currentY: 0,
        startTime: 0,
        isSwiping: false,
        activeElement: null,
        originalPosition: 0
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
        
        // 为动态加载支持添加事件监听
        document.addEventListener('turbolinks:load', onDOMReady);
        document.addEventListener('livewire:load', onDOMReady);
    }
    
    /**
     * DOM加载完成后执行
     */
    function onDOMReady() {
        // 只在触摸设备上初始化
        if (isTouchDevice()) {
            initTouchEvents();
            addTouchStyles();
        }
    }
    
    /**
     * 检测是否为触摸设备
     */
    function isTouchDevice() {
        return ('ontouchstart' in window) || 
               (navigator.maxTouchPoints > 0) || 
               (navigator.msMaxTouchPoints > 0);
    }
    
    /**
     * 初始化触摸事件
     */
    function initTouchEvents() {
        const cartItemsContainer = document.querySelector('.cart-items');
        if (!cartItemsContainer) return;
        
        // 防止在滑动时触发页面滚动
        cartItemsContainer.addEventListener('touchmove', function(e) {
            if (touchState.isSwiping) {
                e.preventDefault();
            }
        }, { passive: false });
        
        // 查找所有购物车项并附加触摸事件
        const cartItems = document.querySelectorAll('.cart-item');
        cartItems.forEach(item => {
            // 触摸开始
            item.addEventListener('touchstart', handleTouchStart);
            // 触摸移动
            item.addEventListener('touchmove', handleTouchMove);
            // 触摸结束
            item.addEventListener('touchend', handleTouchEnd);
            // 触摸取消
            item.addEventListener('touchcancel', handleTouchCancel);
            
            // 添加视觉提示，指示可滑动
            addSwipeHint(item);
        });
    }
    
    /**
     * 处理触摸开始事件
     */
    function handleTouchStart(e) {
        const touch = e.touches[0];
        
        // 重置滑动状态
        touchState.startX = touch.clientX;
        touchState.startY = touch.clientY;
        touchState.currentX = touch.clientX;
        touchState.currentY = touch.clientY;
        touchState.startTime = Date.now();
        touchState.isSwiping = false;
        touchState.activeElement = this;
        touchState.originalPosition = 0;
        
        // 为滑动动画准备元素
        this.style.transition = '';
    }
    
    /**
     * 处理触摸移动事件
     */
    function handleTouchMove(e) {
        if (!touchState.activeElement) return;
        
        const touch = e.touches[0];
        const currentX = touch.clientX;
        const currentY = touch.clientY;
        
        // 计算水平和垂直移动距离
        const deltaX = currentX - touchState.startX;
        const deltaY = currentY - touchState.startY;
        
        // 确定是水平滑动还是垂直滑动
        if (!touchState.isSwiping) {
            // 如果水平移动大于垂直移动，且超过最小识别距离，认为是滑动
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > config.minSwipeDistance) {
                touchState.isSwiping = true;
                
                // 添加活动类
                touchState.activeElement.classList.add('swiping');
                
                // 暂时禁用其他交互
                const actionButtons = touchState.activeElement.querySelectorAll('button');
                actionButtons.forEach(btn => {
                    btn.dataset.originalPointerEvents = btn.style.pointerEvents;
                    btn.style.pointerEvents = 'none';
                });
            }
        }
        
        // 如果是滑动，则移动元素
        if (touchState.isSwiping) {
            // 仅处理从右往左滑动（删除动作）
            if (deltaX < 0) {
                // 应用变换, 使用3D变换以提高性能
                if (config.use3D) {
                    touchState.activeElement.style.transform = `translate3d(${deltaX}px, 0, 0)`;
                } else {
                    touchState.activeElement.style.transform = `translateX(${deltaX}px)`;
                }
                
                // 更新当前位置
                touchState.currentX = currentX;
                
                // 根据滑动距离改变背景颜色，显示删除提示
                const deleteProgress = Math.min(Math.abs(deltaX) / config.deleteThreshold, 1);
                touchState.activeElement.style.backgroundColor = `rgba(254, 202, 202, ${deleteProgress.toFixed(2)})`;
                
                // 如果达到删除阈值，显示更强的视觉提示
                if (Math.abs(deltaX) >= config.deleteThreshold) {
                    touchState.activeElement.classList.add('delete-ready');
                } else {
                    touchState.activeElement.classList.remove('delete-ready');
                }
            }
        }
    }
    
    /**
     * 处理触摸结束事件
     */
    function handleTouchEnd(e) {
        if (!touchState.activeElement || !touchState.isSwiping) return;
        
        // 计算水平滑动距离
        const deltaX = touchState.currentX - touchState.startX;
        
        // 判断是否需要执行删除操作
        if (deltaX < 0 && Math.abs(deltaX) >= config.deleteThreshold) {
            // 达到删除阈值，执行删除
            executeDelete(touchState.activeElement);
        } else {
            // 未达到阈值，恢复原位
            resetElementPosition(touchState.activeElement);
        }
        
        // 恢复按钮交互
        const actionButtons = touchState.activeElement.querySelectorAll('button');
        actionButtons.forEach(btn => {
            btn.style.pointerEvents = btn.dataset.originalPointerEvents || '';
            delete btn.dataset.originalPointerEvents;
        });
        
        // 重置样式
        touchState.activeElement.classList.remove('swiping', 'delete-ready');
        touchState.activeElement.style.backgroundColor = '';
        
        // 清除触摸状态
        touchState.activeElement = null;
        touchState.isSwiping = false;
    }
    
    /**
     * 处理触摸取消事件
     */
    function handleTouchCancel(e) {
        if (touchState.activeElement) {
            resetElementPosition(touchState.activeElement);
            touchState.activeElement.classList.remove('swiping', 'delete-ready');
            touchState.activeElement = null;
            touchState.isSwiping = false;
        }
    }
    
    /**
     * 重置元素位置
     */
    function resetElementPosition(element) {
        element.style.transition = `transform ${config.animationDuration}ms ease`;
        if (config.use3D) {
            element.style.transform = 'translate3d(0, 0, 0)';
        } else {
            element.style.transform = 'translateX(0)';
        }
    }
    
    /**
     * 执行删除操作
     */
    function executeDelete(element) {
        // 获取物品ID
        const itemId = element.dataset.id;
        
        // 如果支持振动API，提供触觉反馈
        if (config.enableVibration && navigator.vibrate) {
            navigator.vibrate(config.vibrationDuration);
        }
        
        // 显示删除动画
        element.style.transition = `transform ${config.animationDuration}ms ease, opacity ${config.animationDuration}ms ease`;
        element.style.transform = 'translate3d(100%, 0, 0)';
        element.style.opacity = '0';
        
        // 使用标准confirm对话框API
        if (window.showDialog) {
            window.showDialog('confirm-remove-dialog', () => {
                removeCartItem(itemId, element);
            }, () => {
                // 取消时恢复元素位置
                resetElementPosition(element);
                element.style.opacity = '1';
            });
        } else {
            // 回退到原生confirm
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeCartItem(itemId, element);
            } else {
                resetElementPosition(element);
                element.style.opacity = '1';
            }
        }
    }
    
    /**
     * 添加滑动提示
     */
    function addSwipeHint(element) {
        // 创建滑动提示元素
        const hintElement = document.createElement('div');
        hintElement.className = 'swipe-hint';
        hintElement.innerHTML = `
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Swipe left to delete</span>
        `;
        
        // 添加到购物车项
        element.appendChild(hintElement);
        
        // 淡入提示，然后在几秒后淡出
        setTimeout(() => {
            hintElement.style.opacity = '1';
            
            setTimeout(() => {
                hintElement.style.opacity = '0';
                
                // 完全消失后移除元素
                setTimeout(() => {
                    hintElement.remove();
                }, 500);
            }, 3000);
        }, 1000);
    }
    
    /**
     * 添加触摸相关样式
     */
    function addTouchStyles() {
        // 如果样式已存在，则不重复添加
        if (document.getElementById('cart-touch-styles')) return;
        
        // 创建样式元素
        const styleElement = document.createElement('style');
        styleElement.id = 'cart-touch-styles';
        styleElement.textContent = `
            .cart-item {
                touch-action: pan-y;
                position: relative;
                overflow: hidden;
            }
            
            .cart-item.swiping {
                cursor: grabbing;
                z-index: 10;
            }
            
            .cart-item.delete-ready {
                background-color: rgba(254, 202, 202, 1) !important;
            }
            
            .cart-item .swipe-hint {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                display: flex;
                align-items: center;
                color: #6B7280;
                font-size: 0.75rem;
                opacity: 0;
                transition: opacity 0.5s ease;
                pointer-events: none;
            }
            
            .cart-item .swipe-hint svg {
                margin-right: 4px;
                animation: swipe-left 1.5s ease infinite;
            }
            
            @keyframes swipe-left {
                0%, 100% { transform: translateX(0); }
                50% { transform: translateX(-5px); }
            }
            
            /* 增大触摸目标区域 */
            .touch-target {
                min-height: 44px;
                min-width: 44px;
            }
            
            /* 删除状态提示 */
            .cart-item::after {
                content: 'Delete';
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                color: #DC2626;
                font-weight: bold;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .cart-item.delete-ready::after {
                opacity: 1;
            }
        `;
        
        // 添加到文档头部
        document.head.appendChild(styleElement);
    }
    
    /**
     * 从购物车中移除项目的API调用
     */
    function removeCartItem(itemId, element) {
        // 获取CSRF令牌
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // 发送API请求
        fetch(`/cart/remove/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 移除DOM元素
                setTimeout(() => {
                    element.remove();
                    
                    // 更新购物车总计
                    updateCartTotal(data.cart_total);
                    
                    // 更新购物车数量
                    updateCartCount(data.cart_count);
                    
                    // 如果购物车为空，重新加载页面
                    if (data.cart_count === 0) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                    
                    // 显示成功通知
                    if (window.showToast) {
                        window.showToast('Item removed from cart', 'success');
                    }
                }, config.animationDuration);
            }
        })
        .catch(error => {
            console.error('Failed to remove item:', error);
            
            // 恢复元素位置
            resetElementPosition(element);
            element.style.opacity = '1';
            
            // 显示错误通知
            if (window.showToast) {
                window.showToast('Failed to remove item', 'error');
            }
        });
    }
    
    /**
     * 更新购物车总计
     */
    function updateCartTotal(total) {
        const cartTotalEl = document.getElementById('cart-total');
        if (cartTotalEl) {
            cartTotalEl.textContent = parseFloat(total).toFixed(2);
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
    
    // 初始化模块
    init();
})(); 