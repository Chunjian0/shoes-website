import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ProductTemplate } from '../../types/apiTypes';
import { motion, useAnimation, AnimatePresence } from 'framer-motion';


interface TemplateCardProps {
  template: ProductTemplate;
  compact?: boolean;
  highlightClass?: string;
  className?: string;
  index?: number; // For staggered animations
}

// 定义 linked_products / related_products 元素的类型别名
type LinkedProductType = NonNullable<ProductTemplate['linked_products' | 'related_products']>[number];

const TemplateCard: React.FC<TemplateCardProps> = ({ 
  template, 
  compact = false,
  highlightClass = '',
  className = '',
  index = 0
}) => {
  const navigate = useNavigate();
  const controls = useAnimation();
  const cardRef = useRef<HTMLDivElement>(null);
  
  const [imageError, setImageError] = useState(false);
  const [lowestPrice, setLowestPrice] = useState<number | null>(null);
  const [totalStock, setTotalStock] = useState<number>(0);
  const [linkedProductsCount, setLinkedProductsCount] = useState<number>(0);
  const [isHovered, setIsHovered] = useState(false);
  const [isVisible, setIsVisible] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  
  // 添加调试日志
  useEffect(() => {
    console.log('TemplateCard 渲染:', { 
      templateId: template?.id,
      templateName: template?.name || template?.title,
      linkedProducts: template?.linked_products?.length || 0
    });
  }, [template]);
  
  // Check if product is new or on sale (define these variables early)
  const isNew = template?.is_new_arrival || false;
  const isSale = template?.is_sale || false;
  
  // Watch for element entering viewport
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
  
  // Cycle through images (if multiple)
  useEffect(() => {
    if (!isHovered || !template?.images || !Array.isArray(template.images) || template.images.length <= 1) {
      return;
    }
    
    const interval = setInterval(() => {
      setCurrentImageIndex(prev => (prev + 1) % template.images!.length);
    }, 2000);
    
    return () => clearInterval(interval);
  }, [isHovered, template?.images]);
  
  // Price flash animation
  useEffect(() => {
    if (isSale && isVisible) {
      const interval = setInterval(() => {
        controls.start({
          scale: [1, 1.05, 1],
          transition: { duration: 1.5 }
        });
      }, 5000);
      
      return () => clearInterval(interval);
    }
  }, [isSale, isVisible, controls]);
  
  useEffect(() => {
    const loadTemplateData = () => {
      try {
        if (!template || !template.id) {
          console.warn('无效的模板数据');
          return;
        }
        
        console.log(`[TemplateCard] Calculating data for product #${template.id} (no cache)`);
        
        const linkedProducts: LinkedProductType[] = 
          (template.linked_products as LinkedProductType[] | undefined) || 
          (template.related_products as LinkedProductType[] | undefined) || 
          [];
        setLinkedProductsCount(linkedProducts.length);
        
        let calculatedLowestPrice: number | null = template.price ?? null;
        let calculatedTotalStock: number = 0;

        if (linkedProducts.length > 0) {
          const prices = linkedProducts
            .filter((p): p is LinkedProductType & { price: number } => 
              p != null && typeof p.price === 'number' && p.price > 0
            )
            .map(p => p.price);
          
          if (prices.length > 0) {
            calculatedLowestPrice = Math.min(...prices);
          }
          
          calculatedTotalStock = linkedProducts.reduce((sum, p) => {
            if (!p) return sum;
            const stockQuantity = p.stock_quantity ?? p.stock ?? 0;
            return sum + stockQuantity;
          }, 0);
        }

        setLowestPrice(calculatedLowestPrice);
        setTotalStock(calculatedTotalStock);
      } catch (error) {
        console.error('[TemplateCard] Error processing template data:', error);
      }
    };
    
    loadTemplateData();
  }, [template]);
  
  const handleImageError = () => {
    if (!imageError) {
      setImageError(true);
    }
  };
  
  const handleImageLoad = () => {
    setImageLoaded(true);
  };

  // Get product image
  const getTemplateImage = (index = 0) => {
    if (!template) return 'https://via.placeholder.com/300x300?text=No+Image';
    
    // 调试日志
    console.log('图片数据:', {
      hasImages: !!template.images,
      imageType: template.images ? typeof template.images : 'undefined',
      isArray: template.images && Array.isArray(template.images),
      length: template.images && Array.isArray(template.images) ? template.images.length : 0,
      firstImage: template.images && Array.isArray(template.images) && template.images.length > 0 
        ? (typeof template.images[0] === 'string' ? template.images[0] : JSON.stringify(template.images[0])) 
        : 'none'
    });
    
    // Handle different image data formats
    if (template.images) {
      if (Array.isArray(template.images)) {
        if (template.images.length > 0) {
          const imgIndex = Math.min(index, template.images.length - 1);
          if (typeof template.images[imgIndex] === 'string') {
            return template.images[imgIndex];
          } else if (template.images[imgIndex] && typeof template.images[imgIndex] === 'object') {
            // 访问可能存在的不同格式的url属性
            const img = template.images[imgIndex] as any;
            return img.url || img.thumbnail || img.image_url || '';
          }
        }
      } else if (template.images && typeof template.images === 'object') {
        // 安全地访问非数组images对象上的url属性
        return (template.images as any).url || '';
      }
    } else if (template.image_url) {
      return template.image_url;
    }
    
    // 如果没有找到图片，检查linked_products中的第一个产品是否有图片
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
  
  // Get product name
  const getTemplateName = () => {
    return template?.title || template?.name || 'Unnamed Product';
  };
  
  // Get product description
  const getTemplateDescription = () => {
    if (!template?.description) return '';
    
    // Handle HTML format description
    let description = template.description;
    
    // If description contains HTML, remove tags
    if (description.includes('<')) {
      description = description.replace(/<[^>]*>/g, '');
    }
    
    return description.length > 100 ? description.substring(0, 97) + '...' : description;
  };
  
  // Get category name
  const getCategoryName = () => {
    // Check for category in template
    return template?.category?.name || '';
  };
  
  // Get stock status (使用 totalStock state)
  const getStockStatus = (): 'in_stock' | 'low_stock' | 'out_of_stock' => {
    if (totalStock <= 0) return 'out_of_stock';
    if (totalStock < 10) return 'low_stock'; // Assuming less than 10 is low stock
    return 'in_stock';
  };
  const stockStatus = getStockStatus(); // 计算库存状态
  
  // Handle click to view product details
  const handleViewDetails = () => {
    navigate(`/products/${template.id}`);
  };
  
  // Format price display
  const formatPrice = (price: number) => {
    return price.toFixed(2);
  };
  
  // Card animations
  const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { 
        duration: 0.5,
        delay: index * 0.1, // Staggered animation
        ease: "easeOut"
      }
    },
    hover: { 
      y: -10,
      boxShadow: "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
      transition: { duration: 0.3 }
    }
  };
  
  // Image animation variants
  const imageVariants = {
    hover: { 
      scale: 1.05,
      transition: { duration: 0.3 }
    }
  };
  
  // If template is invalid
  if (!template || !template.id) {
    console.error('无效的模板数据', template);
    return null;
  }
  
  // 渲染loading骨架屏
  if (!template.id || !getTemplateName()) {
    return (
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        className={`${className}`}
      >
        <div className="rounded-lg bg-white shadow-md overflow-hidden h-80">
        <div className="animate-pulse">
          <div className="h-48 bg-gray-200"></div>
          <div className="p-4 space-y-3">
            <div className="h-4 bg-gray-200 rounded w-3/4"></div>
            <div className="h-4 bg-gray-200 rounded w-1/2"></div>
            <div className="h-8 bg-gray-200 rounded w-1/4 mt-2"></div>
            </div>
          </div>
        </div>
      </motion.div>
    );
  }

  return (
    <motion.div
      ref={cardRef}
      variants={cardVariants}
      initial="hidden"
      animate={controls}
      whileHover="hover"
      className={`relative group overflow-hidden rounded-lg shadow-md bg-white border border-transparent hover:border-indigo-300 transition-all duration-300 flex flex-col ${highlightClass} ${className}`}
      onHoverStart={() => setIsHovered(true)}
      onHoverEnd={() => { setIsHovered(false); setCurrentImageIndex(0); }}
      onClick={handleViewDetails}
    >
      <Link to={`/templates/${template.id}`} className="block relative overflow-hidden group">
        <div className="aspect-w-3 aspect-h-4 bg-gray-100 relative">
          <AnimatePresence initial={false}>
            <motion.img
              key={currentImageIndex}
              src={imageError ? 'https://via.placeholder.com/300x300?text=Error' : getTemplateImage(currentImageIndex)}
              alt={getTemplateName()}
              className={`w-full h-full object-cover transition-opacity duration-300 ease-in-out ${imageLoaded ? 'opacity-100' : 'opacity-0'}`}
              onError={handleImageError}
              onLoad={handleImageLoad}
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              transition={{ duration: 0.5 }}
            />
          </AnimatePresence>
          {!imageLoaded && !imageError && (
            <div className="absolute inset-0 flex items-center justify-center bg-gray-200 animate-pulse">
              {/* Optional: Placeholder Icon */}
            </div>
          )}
          <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
            <span className="text-white text-sm border border-white px-3 py-1 rounded-full">View Details</span>
          </div>
        </div>

        <div className="absolute top-2 left-2 z-10 flex flex-col space-y-1">
          {isSale && <span className="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded">SALE</span>}
          {isNew && <span className="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded">NEW</span>}
        </div>
      </Link>

      <div className={`p-4 ${compact ? 'p-3' : 'p-4'}`}>
        {getCategoryName() && !compact && (
          <p className="text-xs text-gray-500 mb-1 uppercase tracking-wider">{getCategoryName()}</p>
        )}
        <h3 className={`font-semibold text-gray-800 ${compact ? 'text-sm' : 'text-base'} mb-1 truncate group-hover:text-blue-600 transition-colors`}>
          <Link to={`/templates/${template.id}`}>
            {getTemplateName()}
          </Link>
        </h3>
        
        <div className="flex items-center justify-between mb-2">
          <p className={`font-bold ${compact ? 'text-base' : 'text-lg'} ${isSale ? 'text-red-600' : 'text-gray-900'}`}>
            {lowestPrice !== null ? `$${formatPrice(lowestPrice)}` : (template.price ? `$${formatPrice(template.price)}` : 'N/A')}
            {lowestPrice !== null && template.price && lowestPrice < template.price && (
              <span className="ml-1 text-xs line-through text-gray-400">${formatPrice(template.price)}</span>
            )}
          </p>
          {!compact && (
            <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${ 
              getStockStatus() === 'in_stock' ? 'bg-green-100 text-green-800' : 
              getStockStatus() === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 
              'bg-red-100 text-red-800' 
            }`}>
              {getStockStatus().replace('_', ' ').toUpperCase()}
            </span>
          )}
        </div>

        {!compact && (
          <p className="text-xs text-gray-600 h-8 overflow-hidden">{getTemplateDescription()}</p>
        )}
        
        {!compact && (
          <button 
            onClick={handleViewDetails}
            className="mt-3 w-full bg-blue-500 text-white text-sm py-2 px-4 rounded hover:bg-blue-600 transition-colors duration-200 opacity-0 group-hover:opacity-100 transition-opacity"
          >
            View Details
          </button>
        )}
      </div>
    </motion.div>
  );
};

export default TemplateCard; 