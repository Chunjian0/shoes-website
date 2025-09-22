import React from 'react';
import { Link } from 'react-router-dom';
import { ProductTemplate } from '../../types/apiTypes';
import { motion } from 'framer-motion';
import EnhancedProductCard from './EnhancedProductCard';
import { FiChevronRight } from 'react-icons/fi';

interface EnhancedProductSectionProps {
  title: string;
  subtitle?: string;
  products: ProductTemplate[];
  viewMoreLink?: string;
  isLoading?: boolean;
  bgColor?: string;
  sectionType?: 'featured' | 'new' | 'sale' | 'default';
}

const EnhancedProductSection: React.FC<EnhancedProductSectionProps> = ({
  title,
  subtitle,
  products,
  viewMoreLink,
  isLoading = false,
  bgColor = 'bg-white',
  sectionType = 'default'
}) => {
  // 根据区域类型获取颜色方案
  const getColorScheme = () => {
    switch (sectionType) {
      case 'featured':
        return {
          badge: 'bg-blue-100 text-blue-800',
          button: 'bg-blue-600 hover:bg-blue-700',
          highlight: 'border-blue-100 hover:border-blue-200'
        };
      case 'new':
        return {
          badge: 'bg-green-100 text-green-800',
          button: 'bg-green-600 hover:bg-green-700',
          highlight: 'border-green-100 hover:border-green-200'
        };
      case 'sale':
        return {
          badge: 'bg-red-100 text-red-800',
          button: 'bg-red-600 hover:bg-red-700',
          highlight: 'border-red-100 hover:border-red-200'
        };
      default:
        return {
          badge: 'bg-gray-100 text-gray-800',
          button: 'bg-gray-800 hover:bg-gray-900',
          highlight: 'border-gray-100 hover:border-gray-200'
        };
    }
  };
  
  const colors = getColorScheme();
  
  // 容器动画
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: { 
      opacity: 1,
      transition: { 
        staggerChildren: 0.1
      }
    }
  };
  
  // 产品卡片动画
  const productVariants = {
    hidden: { opacity: 0, y: 30 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { duration: 0.5 }
    }
  };
  
  // 加载状态的骨架屏
  const ProductSkeleton = () => (
    <div className="animate-pulse">
      <div className="bg-gray-200 aspect-square rounded-xl mb-4"></div>
      <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
      <div className="h-4 bg-gray-200 rounded w-1/2"></div>
    </div>
  );

  return (
    <section className={`py-16 ${bgColor}`}>
      <div className="container mx-auto px-4">
        {/* 标题区域 */}
        <div className="text-center mb-12">
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5 }}
          >
            <span className={`inline-block px-3 py-1 ${colors.badge} text-xs font-semibold rounded-full mb-2`}>
              {sectionType === 'featured' ? 'FEATURED' : 
               sectionType === 'new' ? 'NEW ARRIVALS' : 
               sectionType === 'sale' ? 'SPECIAL OFFERS' : 'PRODUCTS'}
            </span>
          </motion.div>
          
          <motion.h2 
            className="text-3xl md:text-4xl font-bold text-gray-900 mb-4"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
          >
            {title}
          </motion.h2>
          
          {subtitle && (
            <motion.p 
              className="text-gray-600 max-w-2xl mx-auto"
              initial={{ opacity: 0 }}
              whileInView={{ opacity: 1 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: 0.2 }}
            >
              {subtitle}
            </motion.p>
          )}
        </div>
        
        {/* 产品展示区域 */}
        {isLoading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {[...Array(4)].map((_, i) => (
              <ProductSkeleton key={i} />
            ))}
          </div>
        ) : (
          <>
            {products && products.length > 0 ? (
              <motion.div 
                className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                variants={containerVariants}
                initial="hidden"
                whileInView="visible"
                viewport={{ once: true, margin: "-100px" }}
              >
                {products.map((product, index) => (
                  <motion.div
                    key={product.id}
                    variants={productVariants}
                    custom={index}
                  >
                    <EnhancedProductCard 
                      template={product}
                      index={index}
                      highlightClass={colors.highlight}
                    />
                  </motion.div>
                ))}
              </motion.div>
            ) : (
              <div className="text-center text-gray-500 py-10">
                <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p>No products available.</p>
              </div>
            )}
            
            {/* 查看更多按钮 */}
            {viewMoreLink && products && products.length > 0 && (
              <motion.div 
                className="text-center mt-12"
                initial={{ opacity: 0, y: 10 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: 0.5 }}
              >
                <Link 
                  to={viewMoreLink} 
                  className={`inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white ${colors.button} transition-colors duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1`}
                >
                  View All {sectionType === 'featured' ? 'Featured' : 
                           sectionType === 'new' ? 'New' : 
                           sectionType === 'sale' ? 'Sale' : ''} Products
                  <FiChevronRight className="ml-2" />
                </Link>
              </motion.div>
            )}
          </>
        )}
      </div>
    </section>
  );
};

export default EnhancedProductSection; 