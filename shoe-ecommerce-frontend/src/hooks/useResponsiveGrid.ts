import { useMemo } from 'react';

/**
 * 列配置类型
 */
export type ColumnConfig = {
  xs?: number; // 超小屏幕 (<=576px)
  sm?: number; // 小屏幕 (>576px)
  md?: number; // 中等屏幕 (>768px)
  lg?: number; // 大屏幕 (>992px)
  xl?: number; // 超大屏幕 (>1200px)
  xxl?: number; // 超大屏幕 (>1400px)
};

/**
 * 默认列配置
 */
const DEFAULT_COLUMNS: ColumnConfig = {
  xs: 1,
  sm: 2,
  md: 3,
  lg: 4,
  xl: 4,
  xxl: 5
};

/**
 * 自定义Hook - 计算响应式网格类
 * 
 * @param columns 列配置
 * @returns 网格CSS类名
 */
export const useResponsiveGrid = (columns?: Partial<ColumnConfig>) => {
  return useMemo(() => {
    // 合并默认配置和传入的配置
    const config = { ...DEFAULT_COLUMNS, ...columns };
    
    // 生成适用于Tailwind CSS的响应式网格类
    const gridClass = [
      `grid-cols-${config.xs}`,
      `sm:grid-cols-${config.sm}`,
      `md:grid-cols-${config.md}`,
      `lg:grid-cols-${config.lg}`,
      `xl:grid-cols-${config.xl}`,
      `2xl:grid-cols-${config.xxl}`
    ].join(' ');
    
    return {
      gridClass,
      config
    };
  }, [columns]);
};

export default useResponsiveGrid; 