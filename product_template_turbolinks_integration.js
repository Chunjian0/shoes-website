/**
 * 产品模板系统的 Turbolinks 集成
 * 
 * 本文件实现了与 Turbolinks 兼容的 JavaScript 架构，
 * 确保所有组件在页面加载和切换过程中正确初始化和销毁。
 */

// 全局应用对象
window.ProductTemplateApp = {
    // 存储所有已初始化的组件实例
    components: new Map(),
    
    // 当前页面的组件初始化状态
    initialized: {
        variantSelectors: false,
        imageCarousel: false,
        dragDropSorting: false,
        animationSystem: false,
        // 其他组件...
    },
    
    /**
     * 初始化应用
     * 在 Turbolinks 页面加载后调用
     */
    init() {
        console.log('ProductTemplateApp 初始化中...');
        
        // 重置初始化状态
        Object.keys(this.initialized).forEach(key => {
            this.initialized[key] = false;
        });
        
        // 初始化所有页面组件
        this.initializeComponents();
        
        // 绑定全局事件
        this.bindGlobalEvents();
        
        console.log('ProductTemplateApp 初始化完成');
    },
    
    /**
     * 清理应用
     * 在 Turbolinks 页面卸载前调用
     */
    cleanup() {
        console.log('ProductTemplateApp 清理中...');
        
        // 销毁所有组件
        this.components.forEach((component, element) => {
            if (typeof component.destroy === 'function') {
                component.destroy();
            }
        });
        
        // 清空组件映射
        this.components.clear();
        
        console.log('ProductTemplateApp 清理完成');
    },
    
    /**
     * 初始化所有页面组件
     */
    initializeComponents() {
        // 根据当前页面内容选择性初始化组件
        this.initVariantSelectors();
        this.initImageCarousel();
        this.initDragDropSorting();
        this.initAnimationSystem();
        this.initToastNotifications();
        // 其他组件初始化...
    },
    
    /**
     * 初始化变体选择器
     */
    initVariantSelectors() {
        if (this.initialized.variantSelectors) return;
        
        const selectors = document.querySelectorAll('.variant-selector');
        if (selectors.length === 0) return;
        
        console.log(`Variant selectors initialized: ${selectors.length}`);
        
        selectors.forEach(selector => {
            // 避免重复初始化
            if (this.components.has(selector)) return;
            
            // 创建新的选择器实例
            const instance = new VariantSelector(selector);
            this.components.set(selector, instance);
        });
        
        this.initialized.variantSelectors = true;
    },
    
    /**
     * 初始化图片轮播
     */
    initImageCarousel() {
        if (this.initialized.imageCarousel) return;
        
        const carousels = document.querySelectorAll('.template-image-carousel');
        if (carousels.length === 0) return;
        
        console.log(`Image carousels initialized: ${carousels.length}`);
        
        carousels.forEach(carousel => {
            // 避免重复初始化
            if (this.components.has(carousel)) return;
            
            // 创建新的轮播实例
            const instance = new ImageCarousel(carousel);
            this.components.set(carousel, instance);
        });
        
        this.initialized.imageCarousel = true;
    },
    
    /**
     * 初始化拖放排序
     */
    initDragDropSorting() {
        if (this.initialized.dragDropSorting) return;
        
        const sortables = document.querySelectorAll('.sortable-container');
        if (sortables.length === 0) return;
        
        console.log(`Drag drop sorting containers initialized: ${sortables.length}`);
        
        sortables.forEach(container => {
            // 避免重复初始化
            if (this.components.has(container)) return;
            
            // 创建新的排序实例
            const instance = new SortableManager(container);
            this.components.set(container, instance);
        });
        
        this.initialized.dragDropSorting = true;
    },
    
    /**
     * 初始化动画系统
     */
    initAnimationSystem() {
        if (this.initialized.animationSystem) return;
        
        console.log('Animation system initialized');
        
        // 创建全局动画控制器
        window.AnimationController = new AnimationSystem();
        
        // 为带有动画属性的元素添加动画
        document.querySelectorAll('[data-animation]').forEach(element => {
            const animation = element.dataset.animation;
            const duration = parseInt(element.dataset.animationDuration) || undefined;
            const delay = parseInt(element.dataset.animationDelay) || undefined;
            
            if (window.AnimationController[animation]) {
                window.AnimationController[animation](element, { duration, delay });
            }
        });
        
        this.initialized.animationSystem = true;
    },
    
    /**
     * 初始化Toast通知系统
     */
    initToastNotifications() {
        if (this.initialized.toastNotifications) return;
        
        console.log('Toast notification system initialized');
        
        // 创建全局Toast控制器
        window.ToastController = new ToastSystem();
        
        this.initialized.toastNotifications = true;
    },
    
    /**
     * 绑定全局事件
     */
    bindGlobalEvents() {
        // 处理表单提交前验证
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });
        
        // 处理Ajax请求的提交按钮状态
        document.querySelectorAll('[data-ajax-submit]').forEach(button => {
            button.addEventListener('click', this.handleAjaxSubmit.bind(this));
        });
    },
    
    /**
     * 处理表单提交验证
     */
    handleFormSubmit(event) {
        const form = event.target;
        const isValid = this.validateForm(form);
        
        if (!isValid) {
            event.preventDefault();
            // 显示错误提示
            if (window.ToastController) {
                window.ToastController.error('表单验证失败，请检查输入内容');
            }
        }
    },
    
    /**
     * 处理Ajax提交
     */
    handleAjaxSubmit(event) {
        const button = event.currentTarget;
        button.disabled = true;
        
        // 添加加载动画
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="animate-pulse">处理中...</span>';
        
        // 模拟Ajax请求完成后恢复按钮状态
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        }, 2000);
    },
    
    /**
     * 验证表单
     */
    validateForm(form) {
        let isValid = true;
        
        // 检查所有必填字段
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                
                // 添加错误样式
                field.classList.add('border-red-500');
                field.classList.add('animate-shake');
                
                // 300ms后移除动画类
                setTimeout(() => {
                    field.classList.remove('animate-shake');
                }, 300);
                
                // 创建或更新错误消息
                let errorMsg = field.parentNode.querySelector('.error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'error-message text-sm text-red-500 mt-1 animate-fade-in';
                    field.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = '此字段为必填项';
            } else {
                // 移除错误样式
                field.classList.remove('border-red-500');
                
                // 移除错误消息
                const errorMsg = field.parentNode.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });
        
        return isValid;
    }
};

/**
 * 变体选择器类
 */
class VariantSelector {
    constructor(element) {
        this.element = element;
        this.options = element.querySelectorAll('.variant-option');
        this.productId = element.dataset.productId;
        this.previewContainer = document.querySelector(element.dataset.previewTarget || '#variant-preview');
        
        // 绑定事件
        this.bindEvents();
        
        // 初始化动画
        this.initAnimations();
        
        console.log(`Variant selector initialized: ${this.productId}`);
    }
    
    bindEvents() {
        this.options.forEach(option => {
            option.addEventListener('click', this.handleOptionSelect.bind(this));
        });
    }
    
    handleOptionSelect(event) {
        const option = event.currentTarget;
        const optionId = option.dataset.optionId;
        const optionValue = option.dataset.optionValue;
        
        // 移除当前组中的所有选中状态
        const group = option.closest('.variant-option-group');
        if (group) {
            group.querySelectorAll('.variant-option').forEach(opt => {
                opt.classList.remove('selected');
                opt.classList.remove('animate-selected');
            });
        }
        
        // 添加选中样式和动画
        option.classList.add('selected');
        option.classList.add('animate-selected');
        
        // 更新隐藏输入字段的值
        const inputField = document.querySelector(`input[name="variant_options[${optionId}]"]`);
        if (inputField) {
            inputField.value = optionValue;
        }
        
        // 更新预览（如果存在）
        this.updatePreview();
        
        // 检查是否所有必填选项都已选择
        this.checkAllOptionsSelected();
    }
    
    updatePreview() {
        if (!this.previewContainer) return;
        
        // 获取当前所有选中的选项
        const selectedOptions = {};
        this.element.querySelectorAll('.variant-option.selected').forEach(option => {
            const optionId = option.dataset.optionId;
            const optionValue = option.dataset.optionValue;
            selectedOptions[optionId] = optionValue;
        });
        
        // 使用选中的选项更新预览
        // 这里应该调用API获取匹配的产品信息
        // 为了示例，我们使用模拟数据
        
        // 添加加载动画
        this.previewContainer.innerHTML = '<div class="animate-pulse text-center p-4">加载中...</div>';
        
        // 模拟API请求延迟
        setTimeout(() => {
            // 假设我们已经获取到了产品信息
            const mockProduct = {
                name: '运动鞋 XYZ',
                image: '/images/products/sample.jpg',
                price: 299.99,
                stock: 15
            };
            
            // 更新预览内容
            this.previewContainer.innerHTML = `
                <div class="animate-fade-in">
                    <img src="${mockProduct.image}" alt="${mockProduct.name}" class="w-full h-auto rounded-lg">
                    <h3 class="text-lg font-medium mt-2">${mockProduct.name}</h3>
                    <div class="flex justify-between items-center mt-1">
                        <span class="text-xl font-bold text-green-600">¥${mockProduct.price}</span>
                        <span class="text-sm text-gray-600">库存: ${mockProduct.stock}</span>
                    </div>
                    <div class="mt-2 text-sm text-gray-700">
                        已选: ${Object.values(selectedOptions).join(', ')}
                    </div>
                </div>
            `;
        }, 500);
    }
    
    checkAllOptionsSelected() {
        // 获取所有必填选项组
        const requiredGroups = this.element.querySelectorAll('.variant-option-group[data-required="true"]');
        let allSelected = true;
        
        requiredGroups.forEach(group => {
            const hasSelected = group.querySelector('.variant-option.selected') !== null;
            if (!hasSelected) {
                allSelected = false;
            }
        });
        
        // 更新提交按钮状态
        const submitButton = document.querySelector(this.element.dataset.submitButton || '#add-to-cart-button');
        if (submitButton) {
            submitButton.disabled = !allSelected;
            
            if (allSelected) {
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                submitButton.classList.add('hover:bg-indigo-700');
            } else {
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                submitButton.classList.remove('hover:bg-indigo-700');
            }
        }
    }
    
    initAnimations() {
        // 为选择器添加进入动画
        this.element.classList.add('animate-fade-in');
        
        // 为选项添加延迟进入动画
        this.options.forEach((option, index) => {
            option.style.animationDelay = `${index * 50}ms`;
            option.classList.add('animate-fade-in');
        });
    }
    
    destroy() {
        // 解除事件绑定
        this.options.forEach(option => {
            option.removeEventListener('click', this.handleOptionSelect.bind(this));
        });
        
        console.log(`Variant selector destroyed: ${this.productId}`);
    }
}

/**
 * 动画系统类
 */
class AnimationSystem {
    constructor() {
        this.observers = new Map();
        this.setupIntersectionObserver();
        
        console.log('Animation system initialized');
    }
    
    setupIntersectionObserver() {
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const animation = this.observers.get(element);
                    if (animation) {
                        this.playAnimation(element, animation);
                        // 如果是一次性动画，移除观察
                        if (!animation.loop) {
                            this.observer.unobserve(element);
                            this.observers.delete(element);
                        }
                    }
                }
            });
        }, options);
    }
    
    observe(element, animation) {
        this.observers.set(element, animation);
        this.observer.observe(element);
    }
    
    playAnimation(element, animation) {
        element.classList.add(animation.className);
        
        if (animation.duration) {
            element.style.animationDuration = `${animation.duration}ms`;
        }
        
        if (animation.delay) {
            element.style.animationDelay = `${animation.delay}ms`;
        }
        
        if (animation.callback) {
            element.addEventListener('animationend', animation.callback, { once: !animation.loop });
        }
    }
    
    // 预定义动画
    fadeIn(element, options = {}) {
        this.observe(element, {
            className: 'animate-fade-in',
            duration: options.duration || 300,
            delay: options.delay || 0,
            callback: options.callback,
            loop: false
        });
    }
    
    fadeOut(element, options = {}) {
        this.observe(element, {
            className: 'animate-fade-out',
            duration: options.duration || 300,
            delay: options.delay || 0,
            callback: options.callback,
            loop: false
        });
    }
    
    pulse(element, options = {}) {
        this.observe(element, {
            className: 'animate-pulse',
            duration: options.duration || 1000,
            delay: options.delay || 0,
            callback: options.callback,
            loop: true
        });
    }
    
    shake(element, options = {}) {
        this.observe(element, {
            className: 'animate-shake',
            duration: options.duration || 500,
            delay: options.delay || 0,
            callback: options.callback,
            loop: false
        });
    }
    
    slideIn(element, options = {}) {
        const direction = options.direction || 'right';
        let className = 'animate-slide-in-right';
        
        if (direction === 'left') {
            className = 'animate-slide-in-left';
        } else if (direction === 'up') {
            className = 'animate-slide-in-up';
        } else if (direction === 'down') {
            className = 'animate-slide-in-down';
        }
        
        this.observe(element, {
            className: className,
            duration: options.duration || 500,
            delay: options.delay || 0,
            callback: options.callback,
            loop: false
        });
    }
}

/**
 * Toast通知系统类
 */
class ToastSystem {
    constructor() {
        this.container = null;
        this.createContainer();
        
        console.log('Toast notification system initialized');
    }
    
    createContainer() {
        // 检查是否已存在容器
        let container = document.getElementById('toast-container');
        
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-0 right-0 p-4 z-50 flex flex-col items-end';
            document.body.appendChild(container);
        }
        
        this.container = container;
    }
    
    show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `mb-3 p-4 rounded-lg shadow-lg flex items-center animate-slide-in-right max-w-xs`;
        
        // 根据类型设置样式
        switch (type) {
            case 'success':
                toast.classList.add('bg-green-500', 'text-white');
                break;
            case 'error':
                toast.classList.add('bg-red-500', 'text-white');
                break;
            case 'warning':
                toast.classList.add('bg-yellow-500', 'text-white');
                break;
            default:
                toast.classList.add('bg-blue-500', 'text-white');
        }
        
        // 创建图标
        const iconSvg = document.createElement('span');
        iconSvg.className = 'mr-2 flex-shrink-0';
        
        switch (type) {
            case 'success':
                iconSvg.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                break;
            case 'error':
                iconSvg.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                break;
            case 'warning':
                iconSvg.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
                break;
            default:
                iconSvg.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
        }
        
        // 创建消息文本
        const messageEl = document.createElement('span');
        messageEl.textContent = message;
        messageEl.className = 'text-sm';
        
        // 添加到Toast
        toast.appendChild(iconSvg);
        toast.appendChild(messageEl);
        
        // 添加到容器
        this.container.appendChild(toast);
        
        // 自动移除
        setTimeout(() => {
            toast.classList.remove('animate-slide-in-right');
            toast.classList.add('animate-slide-out-right');
            
            // 动画结束后移除元素
            toast.addEventListener('animationend', () => {
                toast.remove();
            });
        }, duration);
    }
    
    success(message, duration) {
        this.show(message, 'success', duration);
    }
    
    error(message, duration) {
        this.show(message, 'error', duration);
    }
    
    warning(message, duration) {
        this.show(message, 'warning', duration);
    }
    
    info(message, duration) {
        this.show(message, 'info', duration);
    }
}

// Turbolinks 事件监听器
document.addEventListener('turbolinks:load', () => {
    console.log('Turbolinks 页面加载');
    
    // 初始化应用
    window.ProductTemplateApp.init();
    
    // 示例: 显示页面加载完成的通知
    if (window.ToastController) {
        window.ToastController.info('页面加载完成');
    }
});

document.addEventListener('turbolinks:before-cache', () => {
    console.log('Turbolinks 准备缓存页面');
    
    // 清理应用
    window.ProductTemplateApp.cleanup();
});

// 如果不使用 Turbolinks，则使用标准 DOMContentLoaded
if (typeof Turbolinks === 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM 加载完成');
        
        // 初始化应用
        window.ProductTemplateApp.init();
    });
} 