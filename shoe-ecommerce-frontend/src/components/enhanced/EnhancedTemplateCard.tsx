import React, { useState, useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { ProductTemplate, RelatedProduct } from '../../types/apiTypes';
import { motion, useAnimation, AnimatePresence } from 'framer-motion';

interface EnhancedTemplateCardProps {
  template: ProductTemplate;
  index?: number; // 用于错开动画
  highlightClass?: string;
  compact?: boolean;
  className?: string;
  mobileView?: boolean;
}

const EnhancedTemplateCard: React.FC<EnhancedTemplateCardProps> = ({
  template,
  index = 0,
  highlightClass = '',
  compact = false,
  className = '',
  mobileView = false
}) => {
  // 状态
  const [isHovered, setIsHovered] = useState(false);
  const [isVisible, setIsVisible] = useState(false);
  const [maxDiscount, setMaxDiscount] = useState<number | null>(null);
  const [minPrice, setMinPrice] = useState<number | null>(null);
  const [priceRange, setPriceRange] = useState<{min: number; max: number} | null>(null);
  const [hasMultipleImages, setHasMultipleImages] = useState(false);
  const [linkedProductsCount, setLinkedProductsCount] = useState(0);
  const [imageLoaded, setImageLoaded] = useState(false);

  // 引用
  const cardRef = useRef<HTMLDivElement>(null);
  const controls = useAnimation();

  // 计算模板折扣和价格范围
  useEffect(() => {
    if (!template) return;
    
    let highestDiscount = 0;
    let lowestPrice = Number.MAX_VALUE;
    let highestPrice = 0;
    let productCount = 0;
    
    // 检查链接产品
    if (template.linked_products && Array.isArray(template.linked_products)) {
      productCount = template.linked_products.length;
      template.linked_products.forEach(product => {
        // 更新折扣
        if (product.discount_percentage && product.discount_percentage > highestDiscount) {
          highestDiscount = product.discount_percentage;
        }
        
        // 更新价格范围
        if (typeof product.price === 'number') {
          if (product.price < lowestPrice) {
            lowestPrice = product.price;
          }
          if (product.price > highestPrice) {
            highestPrice = product.price;
          }
        }
      });
    } else if (template.related_products && Array.isArray(template.related_products)) {
      // 兼容处理：如果没有linked_products但有related_products
      productCount = template.related_products.length;
      template.related_products.forEach(product => {
        if (product.discount_percentage && product.discount_percentage > highestDiscount) {
          highestDiscount = product.discount_percentage;
        }
        
        if (typeof product.price === 'number') {
          if (product.price < lowestPrice) {
            lowestPrice = product.price;
          }
          if (product.price > highestPrice) {
            highestPrice = product.price;
          }
        }
      });
    }
    
    // 更新状态
    setMaxDiscount(highestDiscount > 0 ? highestDiscount : null);
    setMinPrice(lowestPrice !== Number.MAX_VALUE ? lowestPrice : null);
    setPriceRange(lowestPrice !== Number.MAX_VALUE ? { min: lowestPrice, max: highestPrice } : null);
    setLinkedProductsCount(productCount);
    
    // 检查图片
    const images = template.images || [];
    setHasMultipleImages(images.length > 1);
  }, [template]);

  // 检查元素可见性
  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
          controls.start("visible");
          observer.disconnect();
        }
      },
      { threshold: 0.1 }
    );
    
    if (cardRef.current) {
      observer.observe(cardRef.current);
    }
    
    return () => {
      if (cardRef.current) {
        observer.unobserve(cardRef.current);
      }
    };
  }, [controls]);

  // 价格闪烁动画
  useEffect(() => {
    if (template.is_sale && isVisible) {
      const interval = setInterval(() => {
        controls.start({
          scale: [1, 1.05, 1],
          transition: { duration: 1.5 }
        });
      }, 5000);
      
      return () => clearInterval(interval);
    }
  }, [template.is_sale, isVisible, controls]);

  // 处理图片加载
  const handleImageLoad = () => {
    setImageLoaded(true);
  };

  // 获取模板图片URL - Always get the first image (index 0)
  const getTemplateImage = (): string => { 
    if (!template) return '';
    
    // 如果模板有图片数组
    if (template.images && Array.isArray(template.images) && template.images.length > 0) {
      const image = template.images[0]; // Always try the first image
      
      if (typeof image === 'string') return image;
      if (image && typeof image === 'object' && image.url) {
        return image.url;
      }
    }
    
    // 如果模板有单个图片URL
    if (template.image_url) return template.image_url;
    
    // 尝试从链接产品获取图片
    if (template.linked_products && template.linked_products.length > 0) {
      const product = template.linked_products[0];
      const image = product.images?.[0];
      if (image && typeof image === 'string') return image;
      if (image && typeof image === 'object' && image.url) {
        return image.url;
      }
      if (product.image_url) return product.image_url;
    }
    
    // 默认图片
    return 'https://via.placeholder.com/300x300?text=No+Image';
  };

  // 格式化价格显示
  const formatPrice = (price: number | null): string => {
    if (price === null) return '';
    return price.toFixed(2);
  };

  // 获取价格显示文本
  const getPriceDisplay = (): string => {
    if (!priceRange) return '';
    
    if (priceRange.min === priceRange.max) {
      return `$${formatPrice(priceRange.min)}`;
    }
    
    return `From $${formatPrice(priceRange.min)}`;
  };

  // 卡片动画变体
  const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { 
        duration: 0.5,
        delay: index * 0.1,
        ease: "easeOut"
      }
    },
    hover: { 
      y: -5,
      boxShadow: "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
      transition: { duration: 0.3 }
    }
  };

  // 图片动画变体
  const imageVariants = {
    hover: { 
      scale: 1.05,
      transition: { duration: 0.3 }
    }
  };

  // 标签动画变体
  const badgeVariants = {
    hidden: { opacity: 0, scale: 0.8 },
    visible: { 
      opacity: 1, 
      scale: 1,
      transition: { delay: 0.2 }
    }
  };

  // 处理悬停状态
  const handleMouseEnter = () => {
    setIsHovered(true);
  };

  const handleMouseLeave = () => {
    setIsHovered(false);
  };

  // 获取标签类名
  const getStatusBadgeClass = () => {
    if (template.is_new_arrival) {
      return 'bg-green-100 text-green-800';
    }
    if (maxDiscount !== null) {
      return 'bg-red-100 text-red-800';
    }
    if (template.is_featured) {
      return 'bg-blue-100 text-blue-800';
    }
    return 'bg-gray-100 text-gray-800'; // Default/Fallback
  };

  // 获取标签文本
  const getStatusBadgeText = () => {
    if (template.is_new_arrival) {
      return 'New';
    }
    if (maxDiscount !== null) {
      return `${maxDiscount}% Off`;
    }
    if (template.is_featured) {
      return 'Featured';
    }
    return null; // No badge if none apply
  };
  
  const badgeText = getStatusBadgeText();

  return (
    <motion.div
      ref={cardRef}
      className={`relative group bg-white rounded-lg overflow-hidden border border-gray-200 transition-shadow duration-300 ease-out ${className} ${highlightClass}`}
      variants={cardVariants}
      initial="hidden"
      animate={controls}
      whileHover="hover"
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
    >
      <Link to={`/templates/${template.id}`} className="block">
        <div className="relative aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-100 min-h-64">
          {/* Image with fade transition */}
          <motion.img
            src={getTemplateImage()}
            alt={template.name || template.title || 'Product Template'}
            className={`w-full h-full object-center object-cover transition-opacity duration-500 ease-in-out ${imageLoaded ? 'opacity-100': 'opacity-0'}`}
            initial={{ opacity: 0 }}
            animate={{ opacity: imageLoaded ? 1 : 0 }}
            transition={{ opacity: { duration: 0.5 } }}
            onLoad={handleImageLoad}
          />
          {/* Loading Skeleton for Image */}
          {!imageLoaded && (
            <div className="absolute inset-0 bg-gray-200 animate-pulse"></div>
          )}

          {/* Status Badge */}
          {badgeText && (
            <motion.div
              className={`absolute top-3 right-3 px-2.5 py-1 rounded-full text-xs font-semibold ${getStatusBadgeClass()}`}
              variants={badgeVariants}
            >
              {badgeText}
            </motion.div>
          )}
        </div>

        <div className={`p-4 ${compact ? 'pt-3 pb-3' : 'pt-4 pb-5'}`}>
           {/* Category/Subtext */}
          {template.category?.name && (
            <p className="text-xs text-gray-500 mb-1 uppercase tracking-wider">{template.category.name}</p>
          )}

          <h3 className="text-sm md:text-base font-medium text-gray-900 truncate group-hover:text-indigo-600 transition-colors duration-200">
            {template.name || template.title || 'Untitled Template'}
          </h3>

           {/* Price Display */}
          <motion.div 
            className="mt-2 flex items-baseline justify-between"
            animate={controls} // Use controls for potential price animations
          >
            <p className="text-sm md:text-base font-semibold text-gray-800">
              {getPriceDisplay()}
            </p>
             {/* Show number of variants if multiple products */}
             {linkedProductsCount > 1 && (
                 <span className="text-xs text-gray-500">{linkedProductsCount} variants</span>
             )}
          </motion.div>
          
           {/* Description Snippet (Optional) */}
           {template.description && !compact && (
             <p className="mt-2 text-xs text-gray-600 line-clamp-2">
                {/* Basic sanitization - replace with a proper library if needed */}
                {template.description.replace(/<[^>]*>?/gm, '')}
             </p>
           )}
        </div>
      </Link>

       {/* View Details Button on Hover */}
       <AnimatePresence>
        {isHovered && (
           <motion.div 
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: 10 }}
              transition={{ duration: 0.2 }}
              className="absolute bottom-0 left-0 right-0 p-4 pt-0" // Position at bottom
            >
             <Link 
               to={`/templates/${template.id}`} 
               className="block w-full bg-indigo-600 text-white text-center py-2.5 rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:text-white hover:text-white"
             >
               View Details
             </Link>
           </motion.div>
         )}
       </AnimatePresence>
    </motion.div>
  );
};

export default EnhancedTemplateCard; 