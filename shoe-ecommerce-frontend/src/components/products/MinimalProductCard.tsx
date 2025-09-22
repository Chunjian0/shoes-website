import React, { useState, useEffect, useCallback, memo } from 'react';
import { useNavigate } from 'react-router-dom';
import { ProductTemplate } from '../../types/apiTypes';
import { motion, AnimatePresence } from 'framer-motion';
import { toast } from 'react-toastify';
import { FiShoppingBag, FiHeart, FiEye, FiTag, FiChevronRight } from 'react-icons/fi';
// Import Google Fonts in your HTML head section:
// <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500&family=Didot&display=swap" rel="stylesheet">
// Or add @import url() in index.css
// Import LazyLoad
import LazyLoad from 'react-lazyload';
// Keep Spinner import for placeholder
import Spinner from '../ui/loading/Spinner';
// Import the hook
import useRipple from '../../hooks/useRipple';
// Import cart service
import cartService from '../../services/cartService';
// Import Redux hook
import { useDispatch } from 'react-redux';
import { fetchCart } from '../../store/slices/cartSlice'; // Import fetchCart action

interface MinimalProductCardProps {
  template: ProductTemplate;
  index?: number;
  className?: string;
}

// 为 linked_products 定义局部类型别名
type LinkedProductType = NonNullable<ProductTemplate['linked_products']>[number];

// Helper to find the linked product with the lowest price
const findLowestPriceProduct = (products: LinkedProductType[] | undefined): LinkedProductType | null => {
    if (!products || products.length === 0) return null;

    let lowestPrice = Infinity;
    let lowestPriceProduct: LinkedProductType | null = null;

    products.forEach(product => {
        // 确保 price 存在且为 number
        if (product && typeof product.price === 'number' && product.price < lowestPrice) {
            lowestPrice = product.price;
            lowestPriceProduct = product;
        }
    });

    return lowestPriceProduct;
};

const MinimalProductCard: React.FC<MinimalProductCardProps> = memo(({
  template,
  index = 0,
  className = ''
}) => {
  const navigate = useNavigate();
  const dispatch = useDispatch(); // Get dispatch function
  
  const [isHovered, setIsHovered] = useState(false);
  const [inWishlist, setInWishlist] = useState(false);
  const [isAdding, setIsAdding] = useState(false); // State for add to cart loading
  // Use the ripple hook
  const { addRipple, RippleContainer } = useRipple();

  // Calculate price and discount based on the lowest priced linked product
  const lowestPriceProduct: LinkedProductType | null = findLowestPriceProduct(template.linked_products);
  const displayPrice = lowestPriceProduct?.price ?? null;
  // 假设 LinkedProductType 可能有 original_price, discount_percentage (根据 apiTypes.ts 的匿名类型)
  const originalPrice = (lowestPriceProduct as any)?.original_price ?? null; // 暂时用 any 避免深层类型问题
  const discountPercentage = lowestPriceProduct?.discount_percentage ?? 0;
  const hasDiscount = (originalPrice && displayPrice && originalPrice > displayPrice) || discountPercentage > 0;
  
  const calculatedOriginalPrice = hasDiscount && displayPrice && !originalPrice && discountPercentage > 0
    ? displayPrice / (1 - discountPercentage / 100)
    : originalPrice;

  // Helper function to parse parameter_group string (similar to TemplateDetailPage)
  const parseParameterGroup = (groupString: string | undefined | null): Record<string, string> => {
    if (!groupString) return {};
    const params: Record<string, string> = {};
    groupString.split(';').forEach(pair => {
      const parts = pair.split('=');
      if (parts.length === 2) {
        const key = parts[0].trim().toLowerCase();
        const value = parts[1].trim(); // Keep original case for display/cart
        if (key && value) {
          params[key] = value;
        }
      }
    });
    return params;
  };

  // Event Handlers
  const handleAddToCart = useCallback(async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsAdding(true);

    const firstProduct: LinkedProductType | undefined = template.linked_products?.[0];

    if (!firstProduct) {
      toast.error('No product variant available for this template.');
      setIsAdding(false);
      return;
    }

    // 假设 LinkedProductType 有 parameter_group
    const specs = parseParameterGroup((firstProduct as any).parameter_group); // 暂时用 any
    const size = specs['size'] || specs['Size'];
    const color = specs['color'] || specs['Color'];

    try {
      await cartService.addToCart({
        product_id: firstProduct.id,
        quantity: 1,
        size: size,
        color: color,
        template_id: template.id, 
      });
      
    toast.success(`${template?.title || template?.name || 'Product'} added to cart`, {
      position: "bottom-right",
      autoClose: 2000
    });
      (dispatch as any)(fetchCart()); // 使用类型断言 any
    } catch (error: any) {
        const errorMessage = error.response?.data?.message || error.message || 'Failed to add item to cart';
        toast.error(errorMessage);
        console.error("Add to cart error:", error);
    } finally {
      setIsAdding(false);
    }
  }, [template, dispatch]);
  
  const handleToggleWishlist = useCallback((e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    const newState = !inWishlist;
    setInWishlist(newState);
    const message = newState ? 'Added to wishlist' : 'Removed from wishlist';
    toast.info(message, {
      position: "bottom-right",
      autoClose: 2000
    });
  }, [inWishlist]);
  
  // Combine ripple effect with navigation
  const handleViewDetails = useCallback((event: React.MouseEvent<HTMLDivElement>) => {
    addRipple(event); // Trigger ripple
    // Use a small delay before navigating to allow ripple start visibility
    setTimeout(() => {
        navigate(`/products/${template.id}`);
    }, 100); // 100ms delay
  }, [navigate, template.id, addRipple]);

  // Get image URL (simplified, assuming template images are primary)
  const getTemplateImage = useCallback(() => {
    const imageUrls = [
        ...(Array.isArray(template.images) ? template.images.map(img => typeof img === 'string' ? img : (img as any)?.url || (img as any)?.thumbnail) : []),
        template.image_url,
        template.image,
        template.main_image,
        // 使用 lowestPriceProduct 的 images (假设类型兼容)
        (lowestPriceProduct?.images?.[0] as any)?.url,
        (lowestPriceProduct?.images?.[0] as any)?.thumbnail
    ];
    const firstValidImage = imageUrls.find(url => typeof url === 'string' && url);
    return firstValidImage || 'https://via.placeholder.com/300x300?text=No+Image';
  }, [template, lowestPriceProduct]);

  // Format price
  const formatPrice = useCallback((price: number | null) => {
    if (price === null || isNaN(price)) return 'N/A'; // Handle null or NaN
    return `$${price.toFixed(2)}`;
  }, []);
  
  // Get name
  const getTemplateName = () => {
    return template?.title || template?.name || 'Unnamed Product';
  };

  // Card hover animation
  const cardHoverVariants = {
      rest: { y: 0, boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)' },
      hover: { 
          y: -8,
          boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
          borderColor: '#4f46e5', // Indigo-600
          transition: { duration: 0.3, ease: [0.4, 0, 0.2, 1] } 
      }
  };

  // Button container animation
  const buttonContainerVariants = {
      rest: { opacity: 0, y: 10 },
      hover: { opacity: 1, y: 0, transition: { duration: 0.3, delay: 0.1 } }
  };

  return (
    <motion.div
      variants={cardHoverVariants}
      initial="rest"
      whileHover="hover"
      whileTap={{ scale: 0.98, transition: { duration: 0.1 } }}
      className={`group relative overflow-hidden rounded-lg bg-white border border-gray-200 cursor-pointer flex flex-col transition-colors duration-300 ${className}`}
      onHoverStart={() => setIsHovered(true)}
      onHoverEnd={() => setIsHovered(false)}
      onClick={handleViewDetails}
    >
      {/* Render Ripple Container inside the main div, relative to it */} 
      <RippleContainer />
      
      {/* Product Image Area */}
      <div className="relative overflow-hidden aspect-square flex-shrink-0">
        <LazyLoad 
          height="100%"
          offset={150}
          once
          placeholder={
            <div className="absolute inset-0 flex items-center justify-center bg-gray-100">
              <Spinner size="sm" />
            </div>
          }
        >
          <motion.img
            src={getTemplateImage()}
            alt={getTemplateName()}
            className="w-full h-full object-cover transition-transform duration-300 ease-in-out group-hover:scale-105"
          />
        </LazyLoad>
        
        {/* Discount Badge */}
        {hasDiscount && discountPercentage > 0 && (
           <div className="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-sm z-10">
               {discountPercentage}% OFF
           </div>
        )}

        {/* Wishlist Button (Top Right) */}
        <motion.button
          onClick={handleToggleWishlist}
          className={`absolute top-2 right-2 p-1.5 rounded-full z-10 transition-colors ${inWishlist ? 'bg-indigo-600 text-white' : 'bg-white/70 text-gray-700 hover:bg-white hover:text-indigo-600'}`}
          whileHover={{ scale: 1.1 }}
          whileTap={{ scale: 0.9 }}
          aria-label={inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'}
        >
          <FiHeart className={`w-4 h-4 ${inWishlist ? 'fill-current' : ''}`} /> 
        </motion.button>

        {/* Action Buttons Container (Bottom, appears on hover) */}
        <AnimatePresence>
          {isHovered && (
            <motion.div 
              variants={buttonContainerVariants}
              initial="rest" 
              animate="hover" 
              exit="rest"
              className="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/60 to-transparent flex justify-center space-x-2 z-10"
            >
                <motion.button
                    onClick={handleAddToCart}
                    className="flex-1 bg-black text-white text-xs font-medium py-2 px-3 rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center space-x-1 disabled:opacity-70 disabled:cursor-not-allowed"
                    whileHover={{ scale: 1.05, backgroundColor: '#4338ca' }}
                    whileTap={{ scale: 0.95 }}
                    disabled={isAdding} // Disable button while adding
                >
                  {isAdding ? (
                    <Spinner size="xs" /> 
                  ) : (
                    <>
                    <FiShoppingBag className="w-3.5 h-3.5 group-hover:text-indigo-300 transition-colors"/>
                    <span>Add to Cart</span>
                    </>
                  )}
                 </motion.button>
                 {/* Optional: Quick View Button */}
                 {/* <motion.button className="p-2 bg-white/80 rounded-md text-gray-800 hover:bg-white" whileHover={{ scale: 1.1 }} whileTap={{ scale: 0.9 }}>
                     <FiEye className="w-4 h-4"/>
                 </motion.button> */}
            </motion.div>
           )}
        </AnimatePresence>
      </div>

      {/* Product Info Area */}
      <div className="p-4 flex flex-col flex-grow justify-between">
        {/* Category/Brand (Optional) */}
        {/* <p className="text-xs text-gray-500 mb-1">{template.category?.name || 'Category'}</p> */}
        <h3 
          className="text-sm font-medium text-gray-800 truncate group-hover:text-indigo-600 transition-colors mb-1" 
          title={getTemplateName()}
        >
          {getTemplateName()}
        </h3>
        <div> {/* Price wrapper - aligned to bottom */}
            <p className="text-base font-semibold text-gray-900 mt-1">
              {formatPrice(displayPrice)}
              {/* Show original price if discounted */}
              {hasDiscount && calculatedOriginalPrice && (
                  <span className="ml-2 text-sm text-gray-400 line-through">{formatPrice(calculatedOriginalPrice)}</span>
              )}
            </p>
        </div>
        {/* Optional: View Details Link/Button */}
         {/* <button 
             onClick={handleViewDetails} 
             className="text-xs text-blue-600 hover:underline mt-2 flex items-center">
             View Details <FiChevronRight className="ml-0.5 w-3 h-3"/>
         </button> */}
      </div>
    </motion.div>
  );
});

// 自定义卡片组件的animation变体
const itemAnimations = {
  hidden: { opacity: 0, y: 20 },
  show: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1]
    }
  },
  hover: {
    y: -10,
    boxShadow: '0 20px 40px rgba(0, 0, 0, 0.15), 0 2px 10px rgba(0, 0, 0, 0.1)',
    transition: {
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1]
    }
  }
};

export default MinimalProductCard; 