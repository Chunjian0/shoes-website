import { ProductTemplate } from './apiTypes';

/**
 * 横幅设置类型
 */
export interface BannerSettings {
  id?: number | string;
  title: string;
  subtitle: string;
  buttonText: string;
  buttonLink: string;
  imageUrl: string;
  is_active?: boolean;
  order?: number;
}

/**
 * 轮播项
 */
export interface CarouselItem {
  id?: number;
  title: string;
  subtitle: string;
  imageUrl: string;
  buttonText: string;
  buttonLink: string;
}

/**
 * 轮播设置类型
 */
export interface CarouselSettings {
  autoplay: boolean;
  delay: number;
  transition: string;
  showNavigation: boolean;
  showIndicators: boolean;
}

/**
 * 商品项
 */
export interface ProductItem {
  id: number;
  name: string;
  price: number;
  imageUrl: string;
  category: string;
  isFeatured?: boolean;
  isNewArrival?: boolean;
  isSale?: boolean;
  discount?: number;
  averageRating?: number;
  sku?: string;
  stockQuantity?: number;
  parameterGroup?: string;
  relationType?: string;
  description?: string;
  images?: any[];
  templateId?: number;
}

/**
 * 精选产品设置类型
 */
export interface FeaturedProductsSettings {
  title: string;
  subtitle: string;
  buttonText: string;
  buttonLink: string;
}

/**
 * 新品设置类型
 */
export interface NewProductsSettings {
  title: string;
  subtitle: string;
  buttonText: string;
  buttonLink: string;
}

/**
 * 特价产品设置类型
 */
export interface SaleProductsSettings {
  title: string;
  subtitle: string;
  buttonText: string;
  buttonLink: string;
}

/**
 * 优惠设置类型
 */
export interface OfferSettings {
  title: string;
  subtitle: string;
  buttonText: string;
  buttonLink: string;
  imageUrl: string;
  backgroundColor?: string;
  textColor?: string;
  offer_title?: string;
  offer_subtitle?: string;
  offer_button_text?: string;
  offer_button_link?: string;
  offer_image?: string;
  offer_image_url?: string;
  offer_background_color?: string;
  offer_text_color?: string;
}

/**
 * 全局设置
 */
export interface GlobalSettings {
  site_title: string;
  site_description: string;
  products_per_page: number;
  currency_symbol: string;
  currency_code: string;
  enable_reviews: boolean;
  show_stock_status: boolean;
}

/**
 * 布局模板类型
 */
export enum LayoutTemplate {
  CLASSIC = 'classic',
  MINIMAL = 'minimal',
  MODERN = 'modern',
  BOLD = 'bold'
}

/**
 * 响应式设置类型
 */
export interface ResponsiveSettings {
  mobile_layout: string;
  tablet_columns: number;
  desktop_columns: number;
  show_categories_on_mobile: boolean;
  enable_touch_gestures: boolean;
  breakpoints: {
    sm: number;
    md: number;
    lg: number;
    xl: number;
  };
}

/**
 * 首页设置类型
 */
export interface HomepageSettings {
  active_template: LayoutTemplate;
  layout?: LayoutTemplate;
  site_name: string;
  carousel: CarouselSettings;
  banners: BannerSettings[];
  featuredProducts: FeaturedProductsSettings;
  newProducts: NewProductsSettings;
  saleProducts: SaleProductsSettings;
  offer?: OfferSettings;
  featured_products_count: number;
  new_products_count: number;
  sale_products_count: number;
  showCategories?: boolean;
  show_promotion: boolean;
  show_brands: boolean;
  responsive?: ResponsiveSettings;
  templates?: {
    featured: ProductTemplate[];
    newArrival: ProductTemplate[];
    sale: ProductTemplate[];
  };
  sections?: {
    featured?: {
      title?: string;
      subtitle?: string;
      buttonText?: string;
      link?: string;
    };
    newArrival?: {
      title?: string;
      subtitle?: string;
      buttonText?: string;
      link?: string;
    };
    sale?: {
      title?: string;
      subtitle?: string;
      buttonText?: string;
      link?: string;
    };
  };
}

/**
 * 设置更新事件类型
 */
export enum SettingsUpdateEventType {
  BANNER_UPDATED = 'banner_updated',
  CAROUSEL_UPDATED = 'carousel_updated',
  FEATURED_UPDATED = 'featured_updated',
  NEW_ARRIVAL_UPDATED = 'new_arrival_updated',
  SALE_UPDATED = 'sale_updated',
  OFFER_UPDATED = 'offer_updated',
  ALL_UPDATED = 'all_updated'
}

/**
 * 设置更新事件类型
 */
export interface SettingsUpdateEvent {
  type: SettingsUpdateEventType;
  timestamp: number;
  data?: any;
} 