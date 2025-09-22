import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
// 导入正确的类型
import { Product } from '../types/product'; // 从正确的文件导入 Product
import { ProductTemplate } from '../types/apiTypes'; // 假设类型在此文件中
import { motion } from 'framer-motion';


interface ProductCardProps {
  product: Product | ProductTemplate; // 恢复联合类型
  compact?: boolean;
  highlightClass?: string;
  onAddToCart?: (product: Product | ProductTemplate) => void; // 恢复类型
  className?: string;
}

const ProductCard: React.FC<ProductCardProps> = ({ 
  product, 
  compact = false,
  highlightClass = '',
  onAddToCart,
  className = ''
}) => {
  const [imageError, setImageError] = useState(false);
  const [lowestPrice, setLowestPrice] = useState<number | null>(null);
  const [totalStock, setTotalStock] = useState<number>(0);
  const [isTemplateProduct, setIsTemplateProduct] = useState<boolean>(false);
  
  useEffect(() => {
    const checkProductType = () => {
      if (!product || typeof product !== 'object') return;

      // 使用类型守卫或更明确的检查来区分 Product 和 ProductTemplate
      // 这里简化处理，假设存在 linked_products 或 related_products 即为模板
      const hasLinkedProducts = 'linked_products' in product && Array.isArray(product.linked_products);
      const hasRelatedProducts = 'related_products' in product && Array.isArray(product.related_products);

      if (hasLinkedProducts || hasRelatedProducts) {
        setIsTemplateProduct(true);
        const currentProduct = product as ProductTemplate; // 类型断言
        
        console.log(`[ProductCard] Calculating data for template #${currentProduct.id} (no cache)`);
        
        const linkedProducts: any[] = currentProduct.linked_products ?? []; 
        
        if (linkedProducts.length > 0) {
          const prices = linkedProducts
            .map(p => parseFloat(p?.price?.toString() || '0')) 
            .filter(price => price > 0);
          
          const calculatedLowestPrice = prices.length > 0 ? Math.min(...prices) : null;
          
          const calculatedTotalStock = linkedProducts.reduce((sum, p) => {
            const stockQuantity = parseInt(p?.stock_quantity?.toString() || p?.stock?.toString() || '0', 10);
            return sum + (isNaN(stockQuantity) ? 0 : stockQuantity);
          }, 0);
          
          setLowestPrice(calculatedLowestPrice);
          setTotalStock(calculatedTotalStock);
        } else {
          setLowestPrice(currentProduct.price ?? null);
          setTotalStock(0);
        }
      } else {
        // 认为是 Product 类型
        setIsTemplateProduct(false);
        const currentProduct = product as Product;
        setLowestPrice(currentProduct.price ?? null);
        const stockQty = parseInt(currentProduct.stock?.toString() || '0', 10);
        setTotalStock(isNaN(stockQty) ? 0 : stockQty);
      }
    };
    
    checkProductType();
  }, [product]);
  
  const handleAddToCart = () => {
    if (onAddToCart && product) {
      onAddToCart(product); // 类型已在 Props 中定义
    }
  };

  const handleImageError = () => {
    if (!imageError) {
      setImageError(true);
    }
  };

  const getImageUrl = () => {
    if (imageError || !product) return 'https://via.placeholder.com/300x300?text=No+Image';
    // 安全访问 images 属性
    const images = (product as any).images; // 暂时用 any 绕过 Product/ProductTemplate 差异
    if (Array.isArray(images) && images.length > 0) {
      const firstImage = images[0];
      if (typeof firstImage === 'string') return firstImage;
      if (typeof firstImage === 'object' && firstImage !== null && firstImage.url) return firstImage.url;
    }
    // 安全访问 main_image (假设仅 Product 有)
    if (!isTemplateProduct && typeof (product as Product).main_image === 'string') {
      return (product as Product).main_image;
    }
    // 安全访问 image (通用后备)
    if (typeof (product as any).image === 'string') return (product as any).image;
    return 'https://via.placeholder.com/300x300?text=No+Image';
  }
  const productImage = getImageUrl();

  const calculatePrices = () => {
    if (!product) return { displayPrice: 0, regularPrice: 0, hasDiscount: false, discountPercentage: 0 };

    let regPrice = 0;
    let salePriceNum = null;

    if (isTemplateProduct && lowestPrice !== null) {
        regPrice = lowestPrice;
    } else {
        const currentProduct = product as Product;
        regPrice = parseFloat(currentProduct.price?.toString() || '0'); // 使用 price 替代 selling_price
        const sp = parseFloat(currentProduct.sale_price?.toString() || 'NaN');
        if (!isNaN(sp)) {
            salePriceNum = sp;
        } 
    }

    const hasDiscount = salePriceNum !== null && salePriceNum < regPrice;
    const displayPrice = hasDiscount ? salePriceNum : regPrice;
    const discountPercentage = (hasDiscount && regPrice > 0) 
      ? Math.round(((regPrice - (salePriceNum ?? 0)) / regPrice) * 100)
    : 0;
      
    return {
        displayPrice: displayPrice ?? 0,
        regularPrice: regPrice,
        hasDiscount,
        discountPercentage
    }
  }
  const { displayPrice, regularPrice, hasDiscount, discountPercentage } = calculatePrices();

  const renderStars = () => {
    // 假设都有 rating 和 review_count (或为 undefined)
    const rating = (product as any).rating || 0;
    const reviewCount = (product as any).review_count;
    const stars = [];
    
    for (let i = 1; i <= 5; i++) {
      if (i <= rating) {
        stars.push(
          <svg key={i} className="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        );
      } else {
        stars.push(
          <svg key={i} className="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        );
      }
    }
    
    return (
      <div className="flex items-center">
        <div className="flex">{stars}</div>
        {(typeof reviewCount === 'number') && (
          <span className="text-xs text-gray-500 ml-1">({reviewCount})</span>
        )}
      </div>
    );
  };

  // 获取分类名称，假设都有 category.name 或 category_name
  const categoryName = (product as any).category?.name || (product as any).category_name || '';

  // 检查新品/促销，假设都有 is_new/is_new_arrival 和 is_sale/show_in_sale
  const isNew = (product as any).is_new || (product as any).is_new_arrival || false;
  const isSale = (product as any).is_sale || (product as any).show_in_sale || hasDiscount || false;

  const stockStatus: 'in_stock' | 'low_stock' | 'out_of_stock' = (() => { // 显式定义返回类型
    if (isTemplateProduct) {
      if (totalStock <= 0) return 'out_of_stock';
      if (totalStock < 10) return 'low_stock';
      return 'in_stock';
      } else {
          // 使用 product.stock
          const stockQty = parseInt((product as Product).stock?.toString() || '0', 10); 
          if (isNaN(stockQty) || stockQty <= 0) return 'out_of_stock';
    if (stockQty < 10) return 'low_stock';
    return 'in_stock';
      }
  })();

  // 提前计算是否缺货，避免在 JSX 中进行 TypeScript 认为冗余的比较
  const isOutOfStock = stockStatus === 'out_of_stock';

  // 假设都有 name 或 title
  const productName = (product as any).name || (product as any).title || 'Unnamed Product';
  const productLink = isTemplateProduct ? `/templates/${product.id}` : `/products/${product.id}`;

  if (!product) {
    return <div className={`border rounded-lg p-4 animate-pulse bg-gray-200 ${className}`}>Loading...</div>;
  }

  return (
    <motion.div
      className={`bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 group ${highlightClass} ${className}`}
      whileHover={{ y: -5 }}
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <Link to={productLink} className="block relative aspect-[3/4] overflow-hidden">
        {/* Badges */} 
        <div className="absolute top-2 left-2 z-10 flex flex-col space-y-1">
          {isSale && (
            <span className="inline-block bg-red-500 text-white text-xs font-semibold px-2 py-0.5 rounded">
              SALE{discountPercentage > 0 ? ` ${discountPercentage}%` : ''}
            </span>
          )}
          {isNew && (
            <span className="inline-block bg-blue-500 text-white text-xs font-semibold px-2 py-0.5 rounded">
              NEW
            </span>
          )}
           {stockStatus === 'low_stock' && (
            <span className="inline-block bg-yellow-500 text-white text-xs font-semibold px-2 py-0.5 rounded">
              Low Stock
            </span>
          )}
          {isOutOfStock && (
            <span className="inline-block bg-gray-500 text-white text-xs font-semibold px-2 py-0.5 rounded">
              Out of Stock
            </span>
          )}
        </div>
        
        <img
          src={productImage}
          alt={productName}
          className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          onError={handleImageError}
          loading="lazy"
        />
      </Link>

      <div className={`p-4 ${compact ? 'pt-2' : ''}`}>
        {categoryName && !compact && (
          <p className="text-xs text-gray-500 mb-1 uppercase tracking-wide">{categoryName}</p>
        )}
        <h3 className={`font-semibold ${compact ? 'text-sm' : 'text-base'} text-gray-800 mb-1 truncate`}>
          <Link to={productLink} className="hover:text-blue-600 transition-colors">
            {productName}
          </Link>
          </h3>
          
        {!compact && (
           <div className="h-5 mb-2">
            {renderStars()}
          </div>
        )}
          
        <div className="flex items-baseline justify-between mb-3">
            <div className="flex items-baseline space-x-1">
                <span className={`font-bold ${hasDiscount ? 'text-red-600' : 'text-gray-900'} ${compact ? 'text-base' : 'text-lg'}`}>
                    ${displayPrice?.toFixed(2)}
              </span>
                {hasDiscount && (
                <span className="text-sm text-gray-500 line-through">
                    ${regularPrice?.toFixed(2)}
              </span>
            )}
          </div>
            {isTemplateProduct && <span className="text-xs text-gray-500 italic">From</span>}
        </div>
      
        {onAddToCart && !isOutOfStock && (
        <button
          onClick={handleAddToCart}
            className={`w-full mt-2 py-2 px-4 text-sm font-medium rounded-md transition-colors duration-200 ${compact ? 'text-xs py-1.5' : ''} ${isOutOfStock ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'}`}
            disabled={isOutOfStock}
        >
            {isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
        </button>
      )}
      </div>
    </motion.div>
  );
};

export default ProductCard; 