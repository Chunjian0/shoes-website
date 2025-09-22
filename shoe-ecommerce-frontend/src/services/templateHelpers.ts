import { ProductTemplate } from '../types/apiTypes';

/**
 * 获取模板关联的最低价格产品
 * @param template 产品模板
 * @returns 价格最低的产品或null
 */
export const getLowestPriceProduct = (template: ProductTemplate) => {
  // 获取关联产品
  const linkedProducts = template.linked_products || [];
  
  // 过滤出有有效价格的产品
  const productsWithPrice = linkedProducts.filter(
    product => product.price != null && product.price > 0
  );
  
  if (productsWithPrice.length === 0) {
    return null;
  }
  
  // 找出价格最低的产品
  return productsWithPrice.reduce((min, product) => {
    // Ensure both prices are numbers before comparing
    const minPrice = min.price ?? Infinity;
    const productPrice = product.price ?? Infinity;
    return productPrice < minPrice ? product : min;
  }, productsWithPrice[0]);
};

/**
 * 获取模板的折扣信息
 * @param template 产品模板
 * @returns 包含折扣信息的对象
 */
export const getTemplateDiscountInfo = (template: ProductTemplate) => {
  const lowestPriceProduct = getLowestPriceProduct(template);
  
  if (!lowestPriceProduct) {
    return {
      hasDiscount: false,
      discountPercentage: 0,
      originalPrice: null,
      price: null,
      formattedPrice: '待定',
      formattedOriginalPrice: '',
    };
  }
  
  // 检查是否有折扣
  const hasDiscountPercentage = 
    lowestPriceProduct.discount_percentage != null && // Check for null/undefined
    lowestPriceProduct.discount_percentage > 0;
  
  // 综合判断是否有折扣 - Now relies only on discount_percentage
  const hasDiscount = hasDiscountPercentage;
  
  // 获取价格
  const price = lowestPriceProduct.price ?? null; // Use nullish coalescing for safety
  
  // 获取原价 - Removed logic relying on original_price
  const originalPrice = hasDiscountPercentage && price !== null && typeof lowestPriceProduct.discount_percentage === 'number'
    ? calculateOriginalPrice(price, lowestPriceProduct.discount_percentage) 
    : null;
  
  // 获取折扣百分比
  const discountPercentage = hasDiscountPercentage 
    ? lowestPriceProduct.discount_percentage 
    : 0; // If no discount percentage, it's 0
  
  // 格式化价格
  const formattedPrice = formatCurrency(price); // Pass potentially null price
  const formattedOriginalPrice = hasDiscount && originalPrice !== null
    ? formatCurrency(originalPrice) // Pass potentially null originalPrice
    : '';
  
  return {
    hasDiscount,
    discountPercentage,
    originalPrice,
    price,
    formattedPrice,
    formattedOriginalPrice,
    product: lowestPriceProduct,
  };
};

/**
 * 根据折扣价和折扣百分比计算原价
 * @param discountPrice 折扣价
 * @param discountPercentage 折扣百分比
 * @returns 原价
 */
export const calculateOriginalPrice = (
  discountPrice: number, 
  discountPercentage: number
): number => {
  if (!discountPrice || !discountPercentage) return discountPrice;
  return discountPrice / (1 - discountPercentage / 100);
};

/**
 * 根据原价和折扣价计算折扣百分比
 * @param originalPrice 原价
 * @param discountPrice 折扣价
 * @returns 折扣百分比
 */
export const calculateDiscountPercentage = (
  originalPrice: number, 
  discountPrice: number
): number => {
  if (!originalPrice || !discountPrice || originalPrice <= discountPrice) return 0;
  return Math.round((1 - discountPrice / originalPrice) * 100);
};

/**
 * 格式化货币显示
 * @param amount 金额
 * @param currency 货币符号，默认为人民币
 * @returns 格式化后的价格字符串
 */
export const formatCurrency = (
  amount: number | null | undefined, 
  currency: string = '¥'
): string => {
  // Handle null or undefined amount
  if (amount === null || amount === undefined || isNaN(amount)) { 
    return '待定';
  }
  
  return `${currency}${amount.toFixed(2)}`;
};

/**
 * 检查产品是否为新品
 * @param template 产品模板
 * @returns 是否为新品
 */
export const isNewProduct = (template: ProductTemplate): boolean => {
  // 检查是否标记为新品
  if (template.is_new_arrival) {
    return true;
  }
  
  // 检查创建时间
  const createdAt = template.created_at ? new Date(template.created_at) : null;
  if (!createdAt) return false;
  
  // 计算创建时间是否在30天内
  const now = new Date();
  const thirtyDaysAgo = new Date();
  thirtyDaysAgo.setDate(now.getDate() - 30);
  
  return createdAt >= thirtyDaysAgo;
};

/**
 * 检查产品是否在促销中
 * @param template 产品模板
 * @returns 是否在促销
 */
export const isOnSale = (template: ProductTemplate): boolean => {
  // 检查是否标记为促销
  if (template.is_sale) {
    return true;
  }
  
  // 检查是否有折扣
  const discountInfo = getTemplateDiscountInfo(template);
  return discountInfo.hasDiscount;
};

/**
 * 获取标签CSS类名
 * @param percentage 折扣百分比
 * @returns CSS类名
 */
export const getDiscountBadgeClass = (percentage: number): string => {
  if (percentage >= 50) {
    return 'high-discount';
  } else if (percentage >= 30) {
    return 'medium-discount';
  } else {
    return '';
  }
}; 