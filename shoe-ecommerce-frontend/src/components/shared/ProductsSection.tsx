import React from 'react';
import { Link } from 'react-router-dom';
import { Product } from '../../types/product';
import ProductCard from '../ProductCard';
import Spinner from '../common/Spinner';
import AnimatedElement from '../animations/AnimatedElement';

interface ProductsSectionProps {
  title: string;
  subtitle?: string;
  products: Product[];
  isLoading: boolean;
  error: any;
  buttonText?: string;
  buttonLink?: string;
  layout?: 'grid' | 'slider' | 'featured' | 'minimal';
  columns?: number;
  maxItems?: number;
  backgroundColor?: string;
  titleColor?: string;
  subtitleColor?: string;
  animated?: boolean;
  className?: string;
}

/**
 * 通用产品展示区组件 - 可用于展示各类产品列表
 */
const ProductsSection: React.FC<ProductsSectionProps> = ({
  title,
  subtitle,
  products,
  isLoading,
  error,
  buttonText = 'View All',
  buttonLink = '/products',
  layout = 'grid',
  columns = 4,
  maxItems = 8,
  backgroundColor = 'bg-white',
  titleColor = 'text-gray-900',
  subtitleColor = 'text-gray-600',
  animated = true,
  className = '',
}) => {
  // 如果正在加载，显示加载状态
  if (isLoading) {
    return (
      <div className={`py-16 ${backgroundColor} ${className}`}>
        <div className="container mx-auto px-4">
          <div className="text-center">
            <Spinner />
          </div>
        </div>
      </div>
    );
  }

  // 如果有错误，显示错误消息
  if (error) {
    return (
      <div className={`py-16 ${backgroundColor} ${className}`}>
        <div className="container mx-auto px-4">
          <div className="text-center">
            <p className="text-red-500">Failed to load products. Please try again later.</p>
          </div>
        </div>
      </div>
    );
  }

  // 如果没有产品，显示暂无产品消息
  if (!products || products.length === 0) {
    return (
      <div className={`py-16 ${backgroundColor} ${className}`}>
        <div className="container mx-auto px-4">
          <div className="text-center">
            <h2 className={`text-2xl font-bold mb-2 ${titleColor}`}>{title}</h2>
            {subtitle && <p className={`mb-8 ${subtitleColor}`}>{subtitle}</p>}
            <p className="text-gray-500">No products available at the moment.</p>
          </div>
        </div>
      </div>
    );
  }

  // 限制显示的产品数量
  const displayProducts = products.slice(0, maxItems);

  // 获取布局对应的样式类
  const getLayoutClass = () => {
    switch (layout) {
      case 'slider':
        return 'flex overflow-x-auto pb-4 space-x-4 snap-x snap-mandatory';
      case 'featured':
        return `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-${columns} gap-8`;
      case 'minimal':
        return `grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-${columns} gap-4`;
      case 'grid':
      default:
        return `grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-${columns} gap-6`;
    }
  };

  return (
    <div className={`py-16 ${backgroundColor} ${className}`}>
      <div className="container mx-auto px-4">
        <AnimatedElement
          type="slide-up"
          className="text-center mb-12"
        >
          <h2 className={`text-3xl font-bold mb-3 ${titleColor}`}>{title}</h2>
          {subtitle && <p className={`text-lg max-w-2xl mx-auto ${subtitleColor}`}>{subtitle}</p>}
        </AnimatedElement>

        <div className={getLayoutClass()}>
          {displayProducts.map((product, index) => (
            <AnimatedElement
              key={product.id}
              type="fade-in"
              options={{ delay: 0.1 + index * 0.05 }}
              className={layout === 'slider' ? 'min-w-[280px] snap-start' : ''}
            >
              <ProductCard product={product} />
            </AnimatedElement>
          ))}
        </div>

        {products.length > maxItems && (
          <AnimatedElement
            type="fade-in"
            className="text-center mt-10"
            options={{ delay: 0.3 }}
          >
            <Link 
              to={buttonLink} 
              className="inline-block px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors"
            >
              {buttonText}
            </Link>
          </AnimatedElement>
        )}
      </div>
    </div>
  );
};

export default ProductsSection; 