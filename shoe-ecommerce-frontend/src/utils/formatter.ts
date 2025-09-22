/**
 * 格式化金额为货币格式
 * @param amount 金额
 * @param locale 地区设置，默认为美式英语
 * @param currency 货币代码，默认为美元
 * @returns 格式化后的货币字符串
 */
export const formatCurrency = (
  amount: number | string,
  locale: string = 'en-US',
  currency: string = 'USD'
): string => {
  try {
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    
    // 处理无效输入
    if (isNaN(numAmount)) {
      return '0.00';
    }
    
    // 使用Intl.NumberFormat进行货币格式化
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(numAmount);
  } catch (error) {
    console.error('Currency formatting error:', error);
    // 如果出错，返回简单格式
    return `$${parseFloat(amount.toString()).toFixed(2)}`;
  }
};

/**
 * 格式化日期
 * @param date 日期对象或日期字符串
 * @param locale 地区设置，默认为美式英语
 * @param options 日期格式化选项
 * @returns 格式化后的日期字符串
 */
export const formatDate = (
  date: Date | string,
  locale: string = 'en-US',
  options: Intl.DateTimeFormatOptions = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  }
): string => {
  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    return new Intl.DateTimeFormat(locale, options).format(dateObj);
  } catch (error) {
    console.error('Date formatting error:', error);
    // 如果出错，返回原始字符串
    return date.toString();
  }
}; 