import React, { useEffect } from 'react';
import { ProductTemplate } from '../../types/apiTypes';
import TemplateCard from './TemplateCard';
import { useResponsiveGrid, ColumnConfig } from '../../hooks/useResponsiveGrid';
import TemplateSkeleton from './TemplateSkeleton';

// 引入CSS
import './TemplateCard.css';

export interface TemplateCardListProps {
  templates: ProductTemplate[];
  title?: string;
  subtitle?: string;
  loading?: boolean;
  emptyMessage?: string;
  showCategory?: boolean;
  showDiscount?: boolean;
  aspectRatio?: '1:1' | '4:3' | '3:2' | '16:9';
  size?: 'sm' | 'md' | 'lg';
  columns?: Partial<ColumnConfig>;
  className?: string;
  skeletonCount?: number;
}

/**
 * 产品模板卡片列表组件
 * 
 * 用于显示产品模板卡片的网格布局，支持响应式设计
 */
export const TemplateCardList: React.FC<TemplateCardListProps> = ({
  templates,
  title,
  subtitle,
  loading = false,
  emptyMessage = '没有找到商品',
  showCategory = true,
  showDiscount = true,
  aspectRatio = '3:2',
  size = 'md',
  columns,
  className = '',
  skeletonCount = 8
}) => {
  // 使用自定义hook计算响应式网格类
  const { gridClass } = useResponsiveGrid(columns);
  
  // 初始化动画效果
  useEffect(() => {
    const initDiscountBadges = () => {
      const badges = document.querySelectorAll('.discount-badge');
      badges.forEach((badge, index) => {
        const delay = index * 0.1;
        (badge as HTMLElement).style.animationDelay = `${delay}s`;
      });
    };
    
    // 在非加载状态下初始化
    if (!loading) {
      setTimeout(initDiscountBadges, 100);
    }
  }, [loading, templates]);

  return (
    <div className={`template-card-list ${className}`}>
      {/* 标题区域 */}
      {(title || subtitle) && (
        <div className="mb-4">
          {title && (
            <h2 className="text-xl font-bold text-gray-900 mb-1">{title}</h2>
          )}
          {subtitle && (
            <p className="text-sm text-gray-600">{subtitle}</p>
          )}
        </div>
      )}

      {/* 内容区域 */}
      {loading ? (
        // 加载状态 - 显示骨架屏
        <div className={`grid ${gridClass} gap-4`}>
          {Array.from({ length: skeletonCount }).map((_, index) => (
            <TemplateSkeleton key={index} aspectRatio={aspectRatio} />
          ))}
        </div>
      ) : templates.length > 0 ? (
        // 有数据 - 显示模板卡片
        <div className={`grid ${gridClass} gap-4`}>
          {templates.map((template) => (
            <TemplateCard
              key={template.id}
              template={template}
              showCategory={showCategory}
              showDiscount={showDiscount}
              aspectRatio={aspectRatio}
            />
          ))}
        </div>
      ) : (
        // 无数据 - 显示空消息
        <div className="text-center py-8 text-gray-500">{emptyMessage}</div>
      )}
    </div>
  );
};

export default TemplateCardList; 