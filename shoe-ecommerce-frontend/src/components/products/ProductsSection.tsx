import React, { useState, useEffect } from 'react';
import { ProductItem } from '../../types/homepage';
import { fetchProducts } from '../../api/homepage';
import AnimatedElement from '../animations/AnimatedElement';

// ProductCard组件 - 显示单个产品卡片
const ProductCard: React.FC<{
  product: ProductItem;
  layout?: 'grid' | 'list' | 'compact';
  className?: string;
}> = ({ product, layout = 'grid', className = '' }) => {
  // 使用 ProductItem 中存在的属性，并为可选属性提供默认值
  const {
    id,
    name = 'Unnamed Product',
    description = '',
    price = 0,
    discount = 0,
    imageUrl = '',
    category = 'Uncategorized',
    averageRating = 0,
    stockQuantity = 0
  } = product;

  // 根据 price 和 discount 计算价格
  const hasDiscount = discount > 0;
  const actualPrice = hasDiscount ? price * (1 - discount / 100) : price;
  const originalPrice = hasDiscount ? price : null;

  // 价格显示格式
  const formatPrice = (price: number) => `$${price.toFixed(2)}`;
  
  const layoutClasses = {
    grid: 'flex flex-col h-full',
    list: 'flex flex-row gap-4 h-full',
    compact: 'flex flex-col h-full'
  };
  
  const imageClasses = {
    grid: 'h-48 w-full object-cover rounded-t-lg',
    list: 'h-32 w-32 object-cover rounded-lg flex-shrink-0',
    compact: 'h-36 w-full object-cover rounded-t-lg'
  };
  
  const SaleTag = () => (
    <div className="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 text-xs font-bold rounded">
      SALE {discount > 0 ? `${discount}%` : ''} 
    </div>
  );
  
  const inStock = stockQuantity > 0;
  const StockStatus = () => (
    <div className={`text-xs ${inStock ? 'text-green-600' : 'text-red-500'}`}>
      {inStock ? 'In Stock' : 'Out of Stock'}
    </div>
  );
  
  const RatingStars = () => {
    const ratingValue = averageRating;
    return (
      <div className="flex items-center gap-1">
        {[1, 2, 3, 4, 5].map((star) => (
          <svg
            key={star}
            className={`w-4 h-4 ${
              star <= ratingValue
                ? 'text-yellow-400'
                : 'text-gray-300'
            }`}
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z" />
          </svg>
        ))}
      </div>
    );
  };

  return (
    <div className={`relative bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 ${layoutClasses[layout]} ${className}`}>
      <div className="relative">
        <img 
          src={imageUrl}
          alt={name} 
          className={imageClasses[layout]} 
          loading="lazy" 
        />
        {hasDiscount && <SaleTag />}
      </div>
      
      <div className={`p-4 flex flex-col ${layout === 'list' ? 'flex-1' : ''}`}>
        <div className="text-xs text-gray-500 uppercase mb-1">{category}</div>
        <h3 className="text-gray-800 font-semibold mb-1 line-clamp-1">{name}</h3>
        
        {layout !== 'compact' && (
          <p className="text-gray-600 text-sm mb-3 line-clamp-2">{description}</p>
        )}
        
        <div className="mt-auto">
          <div className="flex items-center justify-between mb-2">
            <div className="flex items-center gap-2">
              {hasDiscount && originalPrice ? (
                <>
                  <span className="text-red-600 font-bold">{formatPrice(actualPrice)}</span>
                  <span className="text-gray-400 line-through text-sm">{formatPrice(originalPrice)}</span>
                </>
              ) : (
                <span className="text-gray-800 font-bold">{formatPrice(actualPrice)}</span>
              )}
            </div>
            {layout !== 'compact' && <StockStatus />}
          </div>
          
          {layout !== 'compact' && <RatingStars />}
          
          <button 
            className={`mt-3 w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 ${!inStock ? 'opacity-50 cursor-not-allowed' : ''}`}
            disabled={!inStock}
          >
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  );
};

// ProductsSection组件接口
export interface ProductsSectionProps {
  title: string;
  subtitle?: string;
  products?: ProductItem[];
  productType?: 'featured' | 'new' | 'sale';
  count?: number;
  layout?: 'grid' | 'slider' | 'list';
  columns?: 2 | 3 | 4;
  maxItems?: number;
  highlighted?: boolean;
  showViewAll?: boolean;
  buttonText?: string;
  buttonLink?: string;
  className?: string;
  animated?: boolean;
  isLoading?: boolean;
  error?: Error | null;
}

// ProductsSection组件 - 显示产品区域
const ProductsSection: React.FC<ProductsSectionProps> = ({
  title,
  subtitle,
  products: propProducts,
  productType,
  count = 8,
  layout = 'grid',
  columns = 4,
  maxItems = 8,
  highlighted = false,
  showViewAll = true,
  buttonText,
  buttonLink = '/products',
  className = '',
  animated = true,
  isLoading,
  error
}) => {
  const [products, setProducts] = useState<ProductItem[]>([]);
  const [loading, setLoading] = useState<boolean>(isLoading ?? true);
  const [errorState, setErrorState] = useState<string | null>(error ? error.message : null);

  // 如果直接传入了products，就不需要再加载
  useEffect(() => {
    if (propProducts) {
      setProducts(propProducts.slice(0, maxItems));
      setLoading(false);
      return;
    }

    // 否则从API加载产品数据
    if (productType) {
      const loadProducts = async () => {
        try {
          setLoading(true);
          const data = await fetchProducts(productType, count);
          setProducts(data.slice(0, maxItems));
          setLoading(false);
        } catch (err) {
          setErrorState(`Failed to load ${productType} products.`);
          setLoading(false);
          console.error(`Error loading ${productType} products:`, err);
        }
      };
  
      loadProducts();
    }
  }, [productType, count, propProducts, maxItems]);

  // 监听props更新loading和error状态
  useEffect(() => {
    if (isLoading !== undefined) {
      setLoading(isLoading);
    }
    if (error) {
      setErrorState(error.message);
    }
  }, [isLoading, error]);

  // 列数映射到类名
  const columnsClass = {
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'
  };

  // 加载中状态
  if (loading) {
    return (
      <div className={`py-8 ${className}`}>
        <div className="container mx-auto px-4">
          <div className="text-center mb-8">
            <div className="h-8 bg-gray-200 rounded w-1/3 mx-auto mb-2 animate-pulse"></div>
            <div className="h-4 bg-gray-100 rounded w-1/2 mx-auto animate-pulse"></div>
          </div>
          <div className={`grid gap-6 ${columnsClass[columns]}`}>
            {Array(count).fill(0).map((_, index) => (
              <div key={index} className="bg-white rounded-lg shadow-sm p-4 h-[320px]">
                <div className="h-48 bg-gray-200 rounded-lg mb-4 animate-pulse"></div>
                <div className="h-4 bg-gray-200 rounded w-3/4 mb-2 animate-pulse"></div>
                <div className="h-4 bg-gray-200 rounded w-1/2 mb-4 animate-pulse"></div>
                <div className="h-8 bg-gray-200 rounded w-full mt-auto animate-pulse"></div>
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  // 错误状态
  if (errorState) {
    return (
      <div className={`py-8 ${className}`}>
        <div className="container mx-auto px-4">
          <div className="text-center">
            <h2 className="text-2xl font-bold text-gray-800 mb-2">{title}</h2>
            {subtitle && <p className="text-gray-600 mb-8">{subtitle}</p>}
            <div className="bg-red-50 border border-red-200 rounded-lg p-4 max-w-md mx-auto">
              <p className="text-red-600">{errorState}</p>
              <button 
                className="mt-2 text-blue-500 hover:text-blue-700 text-sm font-medium"
                onClick={() => window.location.reload()}
              >
                Retry
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className={`py-8 ${highlighted ? 'bg-gray-50' : ''} ${className}`}>
      <div className="container mx-auto px-4">
        <AnimatedElement type="fade-in" options={{ delay: 0.1 }} disabled={!animated}>
          <div className="text-center mb-8">
            <h2 className="text-2xl font-bold text-gray-800 mb-2">{title}</h2>
            {subtitle && <p className="text-gray-600">{subtitle}</p>}
          </div>
        </AnimatedElement>
        
        <AnimatedElement type="slide-up" options={{ delay: 0.2 }} disabled={!animated}>
          <div className={`grid gap-6 ${columnsClass[columns]}`}>
            {products.map((product, index) => (
              <ProductCard 
                key={product.id} 
                product={product} 
                layout={layout === 'list' ? 'list' : 'grid'} 
              />
            ))}
          </div>
        </AnimatedElement>
        
        {showViewAll && buttonLink && (
          <AnimatedElement type="fade-in" options={{ delay: 0.3 }} disabled={!animated}>
            <div className="text-center mt-8">
              <a 
                href={buttonLink}
                className="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md transition-colors duration-200"
              >
                {buttonText || 'View All'}
              </a>
            </div>
          </AnimatedElement>
        )}
      </div>
    </div>
  );
};

export default ProductsSection; 