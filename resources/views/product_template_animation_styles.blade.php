<style>
/* 产品模板组件动画和样式 */

/* 基础卡片样式 */
.template-card {
    position: relative;
    border-radius: 0.5rem;
    overflow: hidden;
    background-color: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.template-card-image {
    position: relative;
    height: 160px;
    overflow: hidden;
}

.template-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.template-card:hover .template-card-image img {
    transform: scale(1.05);
}

.template-card-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background-color: rgba(79, 70, 229, 0.9);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.template-card-content {
    padding: 1rem;
}

.template-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.template-card-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

/* 空状态样式 */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    text-align: center;
}

.empty-state-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    background-color: #f3f4f6;
    border-radius: 9999px;
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.empty-state-description {
    font-size: 0.875rem;
    color: #6b7280;
    max-width: 24rem;
    margin-bottom: 1.5rem;
}

/* 变体选择器样式 */
.variant-selector {
    display: grid;
    gap: 1rem;
}

/* 变体选项样式 */
.variant-option {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.variant-option:hover {
    border-color: #a5b4fc;
    background-color: #f5f5ff;
}

.variant-option.selected {
    border-color: #4f46e5;
    background-color: #eef2ff;
}

/* 动画类 */
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out forwards;
}

.animate-pulse {
    animation: pulse 1s ease-in-out infinite;
}

.animate-slide-in-right {
    animation: slideInRight 0.3s ease-in-out forwards;
}

.animate-slide-in-left {
    animation: slideInLeft 0.3s ease-in-out forwards;
}

.animate-zoom-in {
    animation: zoomIn 0.3s ease-in-out forwards;
}

.animate-bounce {
    animation: bounce 1s ease-in-out infinite;
}

/* 动画关键帧 */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* 响应式样式 - 移动设备 */
@media (max-width: 640px) {
    .variant-selector {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .variant-option {
        padding: 0.5rem;
        font-size: 0.875rem;
        min-height: 44px; /* 触摸友好的高度 */
    }
    
    .template-card-image {
        height: 120px;
    }
}

/* 响应式样式 - 平板设备 */
@media (min-width: 641px) and (max-width: 1024px) {
    .variant-selector {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .variant-option {
        padding: 0.75rem;
        font-size: 1rem;
    }
}

/* 响应式样式 - 桌面设备 */
@media (min-width: 1025px) {
    .variant-selector {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .variant-option {
        padding: 1rem;
        font-size: 1rem;
    }
}
</style> 