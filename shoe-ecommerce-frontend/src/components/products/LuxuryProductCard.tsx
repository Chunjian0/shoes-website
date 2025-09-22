import React, { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { ProductTemplate } from '../../types/apiTypes';
import { motion } from 'framer-motion';
import { toast } from 'react-toastify';
import { FiHeart, FiShoppingBag, FiEye } from 'react-icons/fi';
import styled from 'styled-components';

// 样式化组件
const CardContainer = styled(motion.div)`
  background-color: white;
  border: 1px solid #DAA520;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  overflow: hidden;
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
`;

const ImageContainer = styled.div`
  position: relative;
  overflow: hidden;
  padding-bottom: 125%; /* 4:5 比例 */
  background-color: #f8f8f8;
`;

const ProductImage = styled(motion.img)`
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
`;

const HoverOverlay = styled(motion.div)`
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px;
  background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 100%);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
`;

const ActionButton = styled(motion.button)`
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background-color: white;
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  
  &:hover {
    background-color: #DAA520;
    color: white;
  }
`;

const ProductInfo = styled.div`
  padding: 24px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  justify-content: space-between;
  background: linear-gradient(to bottom, #f8f8f8, #e8e8e8);
`;

const ProductName = styled.h3`
  font-family: 'YourTitleFont', sans-serif;
  font-size: 18px;
  font-weight: 500;
  letter-spacing: -0.02em;
  color: #333;
  margin: 0 0 8px 0;
  line-height: 1.4;
`;

const ProductCategory = styled.div`
  font-family: 'YourAccentFont', cursive;
  font-size: 14px;
  color: #DAA520;
  margin-bottom: 16px;
  font-style: italic;
`;

const PriceRow = styled.div`
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-top: 10px;
`;

const Price = styled.div`
  font-family: 'YourNumericFont', monospace;
  font-size: 20px;
  font-weight: 500;
  color: #333;
`;

const DiscountBadge = styled.div`
  position: absolute;
  top: 16px;
  left: 16px;
  background-color: #DAA520;
  color: white;
  font-family: 'YourBodyFont', sans-serif;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 10px;
  border-radius: 2px;
  z-index: 1;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
`;

interface LuxuryProductCardProps {
  template: ProductTemplate;
  index?: number;
  className?: string;
}

const LuxuryProductCard: React.FC<LuxuryProductCardProps> = ({
  template,
  index = 0,
  className = ''
}) => {
  const navigate = useNavigate();
  
  // 状态管理
  const [isHovered, setIsHovered] = useState(false);
  const [inWishlist, setInWishlist] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);
  
  // 获取产品图片
  const getTemplateImage = useCallback(() => {
    if (!template) return 'https://via.placeholder.com/300x400?text=No+Image';
    
    if (template.images) {
      if (Array.isArray(template.images) && template.images.length > 0) {
        if (typeof template.images[0] === 'string') {
          return template.images[0];
        } else if (template.images[0] && typeof template.images[0] === 'object') {
          const img = template.images[0] as any;
          return img.url || img.thumbnail || img.image_url || '';
        }
      }
    } else if (template.image_url) {
      return template.image_url;
    }
    
    if (template.linked_products && template.linked_products.length > 0) {
      const firstProduct = template.linked_products[0];
      if (firstProduct && firstProduct.images && Array.isArray(firstProduct.images) && firstProduct.images.length > 0) {
        const img = firstProduct.images[0];
        return typeof img === 'string' ? img : (img as any).url || (img as any).thumbnail || '';
      } else if (firstProduct && firstProduct.image_url) {
        return firstProduct.image_url;
      }
    }
    
    return template.image || template.main_image || 'https://via.placeholder.com/300x400?text=No+Image';
  }, [template]);

  // 获取产品名称
  const getTemplateName = () => {
    return template?.title || template?.name || 'Luxury Product';
  };
  
  // 获取产品类别
  const getTemplateCategory = () => {
    return template?.category?.name || 'Shoes';
  };
  
  // 获取产品价格
  const getTemplatePrice = () => {
    if (template.price) return template.price;
    
    if (template.related_products && template.related_products.length > 0) {
      const prices = template.related_products
        .filter((p): p is { price: number } & typeof p => 
           p != null && typeof p.price === 'number'
        )
        .map(p => p.price);
      
      if (prices.length > 0) {
        return Math.min(...prices);
      }
    }
    
    return 0;
  };
  
  // 获取折扣百分比
  const getDiscountPercentage = () => {
    return template.discount_percentage || 0;
  };
  
  // 格式化价格
  const formatPrice = (price: number) => {
    return price.toFixed(2);
  };
  
  // 处理添加到购物车
  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    toast.success(`${getTemplateName()} added to cart`, {
      position: "bottom-right",
      autoClose: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true
    });
  };
  
  // 处理添加到收藏
  const handleToggleWishlist = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    setInWishlist(prev => !prev);
    
    const message = inWishlist 
      ? `${getTemplateName()} removed from wishlist` 
      : `${getTemplateName()} added to wishlist`;
    
    toast.info(message, {
      position: "bottom-right",
      autoClose: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true
    });
  };
  
  // 查看产品详情
  const handleViewDetails = () => {
    navigate(`/products/${template.id}`);
  };
  
  // 计算价格
  const price = getTemplatePrice();
  const discountPercentage = getDiscountPercentage();
  const hasDiscount = discountPercentage > 0;
  const discountedPrice = hasDiscount ? price * (1 - discountPercentage / 100) : price;
  
  const cardVariants = {
    hover: {
      y: -8,
      transition: {
        duration: 0.3,
        ease: [0.4, 0, 0.2, 1]
      }
    }
  };
  
  const imageVariants = {
    hover: {
      scale: 1.05,
      transition: {
        duration: 0.6,
        ease: [0.4, 0, 0.2, 1]
      }
    }
  };
  
  const overlayVariants = {
    initial: { opacity: 0, y: 20 },
    hover: { 
      opacity: 1, 
      y: 0,
      transition: {
        duration: 0.3,
        ease: [0.4, 0, 0.2, 1]
      }
    }
  };
  
  const buttonVariants = {
    hover: {
      scale: 1.1,
      transition: {
        duration: 0.2,
        ease: [0.4, 0, 0.2, 1]
      }
    },
    tap: {
      scale: 0.95
    }
  };

  return (
    <CardContainer 
      className={className}
      onClick={handleViewDetails}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
      variants={cardVariants}
      whileHover="hover"
      initial="initial"
    >
      <ImageContainer>
        {hasDiscount && (
          <DiscountBadge>-{discountPercentage}%</DiscountBadge>
        )}
        
        <ProductImage 
          src={getTemplateImage()} 
          alt={getTemplateName()}
          onLoad={() => setImageLoaded(true)}
          variants={imageVariants}
        />
        
        <HoverOverlay 
          variants={overlayVariants}
          initial="initial"
          animate={isHovered ? "hover" : "initial"}
        >
          <ActionButton 
            onClick={handleToggleWishlist}
            variants={buttonVariants}
            whileHover="hover"
            whileTap="tap"
          >
            <FiHeart color={inWishlist ? "#D4AF37" : "currentColor"} fill={inWishlist ? "#D4AF37" : "none"} size={18} />
          </ActionButton>
          
          <ActionButton 
            onClick={handleAddToCart}
            variants={buttonVariants}
            whileHover="hover"
            whileTap="tap"
          >
            <FiShoppingBag size={18} />
          </ActionButton>
          
          <ActionButton 
            onClick={(e) => {
              e.stopPropagation();
              handleViewDetails();
            }}
            variants={buttonVariants}
            whileHover="hover"
            whileTap="tap"
          >
            <FiEye size={18} />
          </ActionButton>
        </HoverOverlay>
      </ImageContainer>
      
      <ProductInfo>
        <div>
          <ProductCategory>{getTemplateCategory()}</ProductCategory>
          <ProductName>{getTemplateName()}</ProductName>
        </div>
        
        <PriceRow>
          <Price>
            {hasDiscount && (
              <span style={{ 
                textDecoration: 'line-through', 
                fontSize: '16px', 
                color: '#777', 
                marginRight: '8px' 
              }}>
                ${formatPrice(price)}
              </span>
            )}
            <span style={{ color: hasDiscount ? 'var(--color-burgundy)' : 'inherit' }}>
              ${formatPrice(discountedPrice)}
            </span>
          </Price>
        </PriceRow>
      </ProductInfo>
    </CardContainer>
  );
};

export default LuxuryProductCard; 