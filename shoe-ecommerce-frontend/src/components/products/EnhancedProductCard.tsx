import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ProductTemplate, RelatedProduct } from '../../types/apiTypes';
import { motion, useAnimation, AnimatePresence } from 'framer-motion';
import { toast } from 'react-toastify';
import { FiShoppingBag, FiHeart, FiEye, FiTag } from 'react-icons/fi';

interface EnhancedProductCardProps {
  template: ProductTemplate;
  index?: number; // 用于错开动画
  highlightClass?: string;
  compact?: boolean;
  className?: string;
}

const EnhancedProductCard: React.FC<EnhancedProductCardProps> = ({
  template,
  index = 0,
  highlightClass = '',
  compact = false,
  className = ''
}) => {
  const navigate = useNavigate();
  const controls = useAnimation();
  const cardRef = useRef<HTMLDivElement>(null);
  
  // 状态管理
  const [isHovered, setIsHovered] = useState(false);
  const [isVisible, setIsVisible] = useState(false);
  const [inWishlist, setInWishlist] = useState(false);
  const [discountPercentage, setDiscountPercentage] = useState<number | null>(null);
  const [lowestPrice, setLowestPrice] = useState<number | null>(null);
  const [originalPrice, setOriginalPrice] = useState<number | null>(null);
  const [totalStock, setTotalStock] = useState<number>(0);
  const [imageLoaded, setImageLoaded] = useState(false);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  
  // 元素可见性监测
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
  
  // 计算折扣和价格
  useEffect(() => {
    if (!template) return;
    
    let maxDiscount = 0;
    let minPrice = Number.MAX_VALUE;
    let originalPriceValue = 0;
    let stockSum = 0;
    
    const products = template.linked_products || template.related_products || [];
    
    if (products && products.length > 0) {
      products.forEach((product) => {
        if (product.price && typeof product.price === 'number') {
          if (product.price < minPrice) {
            minPrice = product.price;
          }
        }
        
        if (product.discount_percentage && product.discount_percentage > maxDiscount) {
          maxDiscount = product.discount_percentage;
        }
        
        const stockQuantity = product.stock_quantity || product.stock || 0;
        stockSum += stockQuantity;
      });
      
      // 计算原始价格（如果有折扣）
      if (maxDiscount > 0 && minPrice !== Number.MAX_VALUE) {
        originalPriceValue = minPrice / (1 - maxDiscount / 100);
      }
    } else if (template.price) {
      minPrice = template.price;
      
      if (template.discount_percentage) {
        maxDiscount = template.discount_percentage;
        originalPriceValue = minPrice / (1 - maxDiscount / 100);
      }
    }
    
    setDiscountPercentage(maxDiscount > 0 ? maxDiscount : null);
    setLowestPrice(minPrice !== Number.MAX_VALUE ? minPrice : null);
    setOriginalPrice(originalPriceValue > 0 ? originalPriceValue : null);
    setTotalStock(stockSum);
  }, [template]);
  
  // 图片轮播
  useEffect(() => {
    if (!isHovered || !template?.images || !Array.isArray(template.images) || template.images.length <= 1) {
      return;
    }
    
    const interval = setInterval(() => {
      setCurrentImageIndex(prev => (prev + 1) % template.images!.length);
    }, 2000);
    
    return () => clearInterval(interval);
  }, [isHovered, template?.images]);
  
  // 折扣闪烁动画
  useEffect(() => {
    if (discountPercentage && isVisible) {
      const interval = setInterval(() => {
        controls.start({
          scale: [1, 1.1, 1],
          transition: { duration: 0.8 }
        });
      }, 4000);
      
      return () => clearInterval(interval);
    }
  }, [discountPercentage, isVisible, controls]);
  
  // 获取商品图片
  const getTemplateImage = (index = 0) => {
    if (!template) return 'https://via.placeholder.com/300x300?text=No+Image';
    
    if (template.images) {
      if (Array.isArray(template.images) && template.images.length > 0) {
        const imgIndex = Math.min(index, template.images.length - 1);
        if (typeof template.images[imgIndex] === 'string') {
          return template.images[imgIndex];
        } else if (template.images[imgIndex] && typeof template.images[imgIndex] === 'object') {
          const img = template.images[imgIndex] as any;
          return img.url || img.thumbnail || img.image_url || '';
        }
      }
    } else if (template.image_url) {
      return template.image_url;
    }
    
    // 从linked_products获取图片
    if (template.linked_products && template.linked_products.length > 0) {
      const firstProduct = template.linked_products[0];
      if (firstProduct && firstProduct.images && Array.isArray(firstProduct.images) && firstProduct.images.length > 0) {
        const img = firstProduct.images[0];
        return typeof img === 'string' ? img : (img as any).url || (img as any).thumbnail || '';
      } else if (firstProduct && firstProduct.image_url) {
        return firstProduct.image_url;
      }
    }
    
    return template.image || template.main_image || 'https://via.placeholder.com/300x300?text=No+Image';
  };
  
  // 获取商品名称
  const getTemplateName = () => {
    return template?.title || template?.name || 'Unnamed Product';
  };
  
  // 格式化价格
  const formatPrice = (price: number) => {
    return price.toFixed(2);
  };
  
  // 添加到购物车
  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    // 这里应该调用购物车服务
    toast.success(`${getTemplateName()} 已添加到购物车`, {
      position: "bottom-right",
      autoClose: 3000
    });
  };
  
  // 添加到愿望清单
  const handleToggleWishlist = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    setInWishlist(prev => !prev);
    
    const message = inWishlist 
      ? `${getTemplateName()} 已从愿望清单中移除` 
      : `${getTemplateName()} 已添加到愿望清单`;
    
    toast.info(message, {
      position: "bottom-right",
      autoClose: 3000
    });
  };
  
  // 查看商品详情
  const handleViewDetails = () => {
    navigate(`/products/${template.id}`);
  };
  
  // 图片加载完成
  const handleImageLoad = () => {
    setImageLoaded(true);
  };
  
  // 卡片动画
  const cardVariants = {
    hidden: { opacity: 0, y: 30 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { 
        duration: 0.5,
        delay: index * 0.1, // 错开动画
        ease: "easeOut"
      }
    },
    hover: { 
      y: -12,
      transition: { duration: 0.3 }
    }
  };
  
  // 图片动画
  const imageVariants = {
    hover: { 
      scale: 1.08,
      transition: { duration: 0.4 }
    }
  };
  
  // 按钮容器动画
  const buttonContainerVariants = {
    hidden: { opacity: 0, y: 20 },
    hover: { 
      opacity: 1, 
      y: 0,
      transition: {
        duration: 0.3,
        staggerChildren: 0.1
      }
    }
  };
  
  // 按钮动画
  const buttonVariants = {
    hidden: { opacity: 0, y: 10 },
    hover: { 
      opacity: 1, 
      y: 0,
      transition: { duration: 0.2 }
    }
  };
  
  // 折扣标签动画
  const discountBadgeVariants = {
    hidden: { opacity: 0, scale: 0.8 },
    visible: { 
      opacity: 1, 
      scale: 1,
      transition: { 
        delay: 0.2 + index * 0.1,
        type: "spring",
        stiffness: 300,
        damping: 10
      }
    }
  };

  return (
    <motion.div
      ref={cardRef}
      variants={cardVariants}
      initial="hidden"
      animate={controls}
      whileHover="hover"
      className={`${className} cursor-pointer`}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
      onClick={handleViewDetails}
    >
      <div className={`rounded-xl overflow-hidden bg-white shadow-md hover:shadow-xl transition-shadow duration-300 ${highlightClass}`}>
        {/* 商品图片区域 */}
        <div className="relative overflow-hidden aspect-square">
          {/* 加载指示器 */}
          {!imageLoaded && (
            <div className="absolute inset-0 flex items-center justify-center bg-gray-100">
              <div className="w-10 h-10 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
          )}
          
          {/* 商品图片 */}
          <motion.div
            variants={imageVariants}
            className="w-full h-full"
          >
            <img
              src={getTemplateImage(currentImageIndex)}
              alt={getTemplateName()}
              className={`w-full h-full object-cover transition-opacity duration-300 ${imageLoaded ? 'opacity-100' : 'opacity-0'}`}
              onLoad={handleImageLoad}
            />
          </motion.div>
          
          {/* 折扣标签 */}
          {discountPercentage && (
            <motion.div
              variants={discountBadgeVariants}
              initial="hidden"
              animate="visible"
              className="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-medium"
            >
              <span className="flex items-center">
                <FiTag className="mr-1" /> {discountPercentage}% OFF
              </span>
            </motion.div>
          )}
          
          {/* 按钮容器 */}
          <motion.div
            variants={buttonContainerVariants}
            initial="hidden"
            animate={isHovered ? "hover" : "hidden"}
            className="absolute inset-x-0 bottom-0 p-4 flex justify-center space-x-3"
          >
            {/* 添加到购物车按钮 */}
            <motion.button
              variants={buttonVariants}
              className="w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center hover:bg-blue-500 hover:text-white transition-colors duration-200"
              onClick={handleAddToCart}
              aria-label="Add to cart"
            >
              <FiShoppingBag size={18} />
            </motion.button>
            
            {/* 查看详情按钮 */}
            <motion.button
              variants={buttonVariants}
              className="w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center hover:bg-blue-500 hover:text-white transition-colors duration-200"
              onClick={handleViewDetails}
              aria-label="View details"
            >
              <FiEye size={18} />
            </motion.button>
            
            {/* 添加到愿望清单按钮 */}
            <motion.button
              variants={buttonVariants}
              className={`w-10 h-10 rounded-full shadow-md flex items-center justify-center transition-colors duration-200 ${
                inWishlist 
                  ? 'bg-red-500 text-white hover:bg-red-600' 
                  : 'bg-white hover:bg-red-500 hover:text-white'
              }`}
              onClick={handleToggleWishlist}
              aria-label="Add to wishlist"
            >
              <FiHeart size={18} />
            </motion.button>
          </motion.div>
        </div>
        
        {/* 商品信息区域 */}
        <div className="p-4">
          {/* 商品类别 */}
          {template.category && template.category.name && (
            <p className="text-xs text-gray-500 mb-1">{template.category.name}</p>
          )}
          
          {/* 商品名称 */}
          <h3 className="text-base font-medium text-gray-900 mb-2 line-clamp-2 h-12">
            {getTemplateName()}
          </h3>
          
          {/* 价格区域 */}
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              {/* 当前价格 */}
              {lowestPrice && (
                <motion.span
                  animate={controls}
                  className={`text-lg font-bold ${discountPercentage ? 'text-red-600' : 'text-gray-900'}`}
                >
                  ${formatPrice(lowestPrice)}
                </motion.span>
              )}
              
              {/* 原价 */}
              {originalPrice && originalPrice !== lowestPrice && (
                <span className="ml-2 text-sm text-gray-500 line-through">
                  ${formatPrice(originalPrice)}
                </span>
              )}
            </div>
            
            {/* 库存状态 */}
            {!compact && (
              <span 
                className={`text-xs px-2 py-1 rounded-full ${
                  totalStock > 0 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-red-100 text-red-800'
                }`}
              >
                {totalStock > 0 ? 'In Stock' : 'Out of Stock'}
              </span>
            )}
          </div>
        </div>
      </div>
    </motion.div>
  );
};

export default EnhancedProductCard; 