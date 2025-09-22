import { useMemo } from 'react';
import { ProductTemplate } from '../types/apiTypes';
import { 
  getLowestPriceProduct, 
  getTemplateDiscountInfo, 
  isNewProduct, 
  isOnSale 
} from '../services/templateHelpers';

/**
 * 自定义Hook - 处理产品模板折扣数据
 * 
 * @param template 产品模板
 * @returns 模板折扣数据和标志
 */
export const useTemplateDiscount = (template: ProductTemplate) => {
  // 使用useMemo缓存计算结果
  return useMemo(() => {
    // 获取基本折扣信息
    const discountInfo = getTemplateDiscountInfo(template);
    
    // 获取最低价格产品
    const lowestPriceProduct = getLowestPriceProduct(template);
    
    // 检查是否新品
    const isNew = isNewProduct(template);
    
    // 检查是否促销
    const isSale = isOnSale(template);
    
    // 检查是否精选
    const isFeatured = template.is_featured || false;
    
    // 获取新品或促销截止日期 (使用 as any 安全访问)
    const newUntilDateString = (lowestPriceProduct as any)?.new_until_date;
    const newUntilDate = newUntilDateString ? new Date(newUntilDateString) : null;
      
    const saleUntilDateString = (lowestPriceProduct as any)?.sale_until_date;
    const saleUntilDate = saleUntilDateString ? new Date(saleUntilDateString) : null;
    
    // 检查折扣相关日期 (使用 as any 安全访问)
    const now = new Date();
    
    const discountStartDateString = (lowestPriceProduct as any)?.discount_start_date;
    const discountStartDate = discountStartDateString ? new Date(discountStartDateString) : null;
      
    const discountEndDateString = (lowestPriceProduct as any)?.discount_end_date;
    const discountEndDate = discountEndDateString ? new Date(discountEndDateString) : null;
    
    // 检查折扣是否在有效期内 (确保日期有效)
    const isDateValid = (date: Date | null): date is Date => date instanceof Date && !isNaN(date.getTime());

    const discountActive = 
      (!discountStartDate || (isDateValid(discountStartDate) && discountStartDate <= now)) && 
      (!discountEndDate || (isDateValid(discountEndDate) && discountEndDate >= now));
    
    // 计算剩余时间 (确保日期有效)
    const getDaysRemaining = (date: Date | null): number | null => {
      if (!isDateValid(date)) return null;
      // 断言 date 在这里是有效的 Date 对象
      const validDate = date as Date;
      const diff = validDate.getTime() - now.getTime();
      return Math.ceil(diff / (1000 * 60 * 60 * 24));
    };
    
    const daysUntilDiscountEnd = getDaysRemaining(discountEndDate);
    const daysUntilSaleEnd = getDaysRemaining(saleUntilDate);
    const daysUntilNewEnd = getDaysRemaining(newUntilDate);
    
    // 紧急标志
    const isDiscountUrgent = daysUntilDiscountEnd !== null && daysUntilDiscountEnd <= 3;
    
    // 计算图片URL (安全访问)
    const imageUrl = 
      template.images && 
      Array.isArray(template.images) && 
      template.images.length > 0 && 
      template.images[0] && 
      (typeof template.images[0] === 'string' ? template.images[0] : template.images[0].url)
      ? (typeof template.images[0] === 'string' ? template.images[0] : template.images[0].url)
      : '/images/placeholder.jpg'; // 提供默认图片
      
    // 返回所有数据
    return {
      ...discountInfo,
      lowestPriceProduct,
      imageUrl,
      isNew,
      isSale,
      isFeatured,
      newUntilDate,
      saleUntilDate,
      discountStartDate,
      discountEndDate,
      discountActive,
      daysUntilDiscountEnd,
      daysUntilSaleEnd,
      daysUntilNewEnd,
      isDiscountUrgent,
      
      // 折扣百分比CSS类 (提供默认值)
      discountClass: (discountInfo.discountPercentage ?? 0) >= 50 
        ? 'high-discount' 
        : ((discountInfo.discountPercentage ?? 0) >= 30 ? 'medium-discount' : '')
    };
  }, [template]);
};

export default useTemplateDiscount; 