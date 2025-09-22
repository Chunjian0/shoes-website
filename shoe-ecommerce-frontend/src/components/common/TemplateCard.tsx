import React, { useMemo } from 'react';
import { ProductTemplate } from '../../types/apiTypes';
import { Link } from 'react-router-dom';

interface TemplateCardProps {
  template: ProductTemplate;
  aspectRatio?: string; // 控制卡片宽高比，默认为'1:1'
  showCategory?: boolean; // 是否显示分类
  showDiscount?: boolean; // 是否显示折扣
  className?: string;
}

/**
 * 产品模板卡片组件
 * 
 * 显示产品模板信息，包括最低价格和折扣信息
 */
const TemplateCard: React.FC<TemplateCardProps> = ({
  template,
  aspectRatio = '1:1',
  showCategory = true,
  showDiscount = true,
  className = '',
}) => {
  // 使用useMemo优化性能，避免重复计算
  const {
    imageUrl,
    lowestPriceProduct,
    hasDiscount,
    discountPercentage,
    formattedPrice,
    formattedOriginalPrice,
    isNew,
    isSale
  } = useMemo(() => {
    // 获取第一张图片
    let imageUrl: string = '/images/placeholder.jpg';
    
    if (template.images && template.images.length > 0) {
      const firstImage = template.images[0];
      // 处理不同类型的图片数据结构
      if (typeof firstImage === 'string') {
        imageUrl = firstImage;
      } else if (typeof firstImage === 'object' && firstImage !== null) {
        // 按优先级获取图片URL
        imageUrl = firstImage.url || firstImage.image_url || firstImage.thumbnail || imageUrl;
      }
    } else if (template.image_url) {
      imageUrl = template.image_url;
    } else if (template.image) {
      imageUrl = template.image;
    } else if (template.main_image) {
      imageUrl = template.main_image;
    }
    
    // 获取关联产品
    const linkedProducts = template.linked_products || [];
    
    // 扩展类型以包含original_price属性
    interface EnhancedProduct {
      id: number;
      name?: string;
      price?: number;
      original_price?: number;
      discount_percentage?: number;
      images?: Array<string | { url?: string; thumbnail?: string }>;
      image_url?: string;
      stock_quantity?: number;
      stock?: number;
      sku?: string;
    }
    
    // 过滤出有效价格的产品
    const productsWithPrice = linkedProducts.filter(
      product => product.price != null && product.price > 0
    ) as EnhancedProduct[];
    
    // 找出价格最低的产品
    const lowestPriceProduct = productsWithPrice.length > 0
      ? productsWithPrice.reduce((min, product) => {
          return (product.price || 0) < (min.price || 0) ? product : min;
        }, productsWithPrice[0])
      : null;
    
    // 检查是否有折扣
    const hasDiscount = lowestPriceProduct && 
      lowestPriceProduct.original_price !== undefined && 
      lowestPriceProduct.price !== undefined &&
      lowestPriceProduct.original_price > lowestPriceProduct.price;
    
    // 计算折扣百分比
    const discountPercentage = hasDiscount && lowestPriceProduct && 
        lowestPriceProduct.original_price !== undefined && 
        lowestPriceProduct.price !== undefined
      ? Math.round(((lowestPriceProduct.original_price - lowestPriceProduct.price) / lowestPriceProduct.original_price) * 100)
      : 0;
    
    // 格式化价格
    const formattedPrice = lowestPriceProduct && lowestPriceProduct.price !== undefined
      ? `¥${lowestPriceProduct.price.toFixed(2)}`
      : '待定';
    
    const formattedOriginalPrice = hasDiscount && lowestPriceProduct && 
        lowestPriceProduct.original_price !== undefined
      ? `¥${lowestPriceProduct.original_price.toFixed(2)}`
      : '';
    
    // 检查特殊标记
    const isNew = template.is_new_arrival || false;
    const isSale = template.is_sale || false;
    
    return {
      imageUrl,
      lowestPriceProduct,
      hasDiscount,
      discountPercentage,
      formattedPrice,
      formattedOriginalPrice,
      isNew,
      isSale
    };
  }, [template]);
  
  // 样式计算
  const cardStyle = useMemo(() => {
    // 从aspectRatio提取宽高比
    const [width, height] = aspectRatio.split(':').map(Number);
    const paddingTop = `${(height / width) * 100}%`;
    
    return {
      paddingTop,
    };
  }, [aspectRatio]);
  
  return (
    <Link 
      to={`/product-template/${template.id}`} 
      className={`template-card block overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow duration-200 ${className}`}
    >
      {/* 产品图片 */}
      <div className="relative w-full" style={{ paddingTop: cardStyle.paddingTop }}>
        <img 
          src={imageUrl} 
          alt={template.name}
          className="absolute top-0 left-0 w-full h-full object-cover"
        />
        
        {/* 促销标签 */}
        <div className="absolute top-2 left-2 flex flex-col gap-1">
          {isNew && (
            <span className="inline-block px-2 py-1 text-xs font-semibold text-white bg-green-500 rounded-md">
              新品
            </span>
          )}
          
          {isSale && (
            <span className="inline-block px-2 py-1 text-xs font-semibold text-white bg-orange-500 rounded-md">
              促销
            </span>
          )}
          
          {showDiscount && hasDiscount && (
            <span className="inline-block px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-md discount-badge">
              -{discountPercentage}%
            </span>
          )}
        </div>
      </div>
      
      {/* 产品信息 */}
      <div className="p-3">
        <h3 className="text-sm font-medium text-gray-800 truncate">
          {template.name}
        </h3>
        
        {showCategory && template.category && (
          <p className="text-xs text-gray-500 mb-1">
            {template.category.name}
          </p>
        )}
        
        {/* 价格信息 */}
        <div className="mt-1 flex items-center gap-2">
          <span className="text-base font-semibold text-red-600">
            {formattedPrice}
          </span>
          
          {hasDiscount && (
            <span className="text-xs text-gray-500 line-through">
              {formattedOriginalPrice}
            </span>
          )}
        </div>
      </div>
    </Link>
  );
};

export default TemplateCard; 