import { HomepageSettings } from "../../types/homepage";
import { Product } from "../../types/product";
import { Category } from "../../types/category";

/**
 * 所有模板组件的共享属性接口
 */
export interface TemplateProps {
  /**
   * 模板设置
   */
  settings: HomepageSettings;
  
  /**
   * 精选产品列表
   */
  featuredProducts: Product[];
  
  /**
   * 新上架产品列表
   */
  newProducts: Product[];
  
  /**
   * 特价产品列表
   */
  saleProducts: Product[];
  
  /**
   * 商品分类列表
   */
  categories: Category[];
  
  /**
   * 各部分加载状态
   */
  isLoading?: {
    featuredProducts?: boolean;
    newProducts?: boolean;
    saleProducts?: boolean;
    categories?: boolean;
  };
  
  /**
   * 各部分错误状态
   */
  errors?: {
    featuredProducts?: Error | null;
    newProducts?: Error | null;
    saleProducts?: Error | null;
    categories?: Error | null;
  };
}

/**
 * 动画设置接口
 */
export interface AnimationOptions {
  duration?: number;
  delay?: number;
  ease?: string;
  once?: boolean;
}

/**
 * 动画类型枚举
 */
export enum AnimationType {
  FADE_IN = 'fadeIn',
  SLIDE_UP = 'slideUp',
  SLIDE_DOWN = 'slideDown',
  SLIDE_LEFT = 'slideLeft',
  SLIDE_RIGHT = 'slideRight',
  ZOOM_IN = 'zoomIn',
  ZOOM_OUT = 'zoomOut',
  BOUNCE = 'bounce',
  PULSE = 'pulse',
  NONE = 'none'
}

/**
 * 所有模板需要实现的基础方法
 */
export interface TemplateInterface {
  // 渲染横幅区域
  renderHeroBanner(): JSX.Element;
  
  // 渲染分类区域
  renderCategories(): JSX.Element;
  
  // 渲染精选产品区域
  renderFeaturedProducts(): JSX.Element;
  
  // 渲染新品区域
  renderNewProducts(): JSX.Element;
  
  // 渲染特价产品区域
  renderSaleProducts(): JSX.Element;
  
  // 渲染促销优惠区域
  renderPromotionOffer(): JSX.Element;
  
  // 渲染USP(独特卖点)区域
  renderUSP(): JSX.Element;
} 