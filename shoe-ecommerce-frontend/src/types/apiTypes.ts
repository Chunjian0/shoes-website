/**
 * API接口类型定义
 */

// 产品模板关联商品
export interface RelatedProduct {
  id: number;
  sku: string;
  name: string;
  price: number;
  original_price?: number;
  discount_percentage?: number;
  stock_quantity?: number;
  stock?: number; // For backward compatibility
  variations?: Record<string, string>;
  relation_type: 'direct' | 'linked';
  parameter_group?: string;
  image_url?: string;
  images?: TemplateImage[];
  parameters?: Record<string, string>; // New field for product-specific parameters
}

// 模板参数
export interface TemplateParameter {
  name: string;
  values: string[];
}

// 模板图片
export interface TemplateImage {
  id: number;
  url: string;
  thumbnail: string;
}

// 产品分类
export interface ProductCategory {
  id: number;
  name: string;
}

/**
 * 产品模板接口
 */
export interface ProductTemplate {
  id: number;
  title?: string;
  name?: string;
  description?: string;
  price?: number;
  discount_percentage?: number;
  category?: {
    id: number;
    name: string;
  };
  images?: Array<string | { url?: string; thumbnail?: string; image_url?: string }>;
  image_url?: string;
  image?: string;
  main_image?: string;
  linked_products?: Array<{
    id: number;
    name?: string;
    price?: number;
    discount_percentage?: number;
    images?: Array<string | { url?: string; thumbnail?: string }>;
    image_url?: string;
    stock_quantity?: number;
    stock?: number;
    sku?: string;
  }>;
  related_products?: Array<{
    id: number;
    name?: string;
    price?: number;
    discount_percentage?: number;
    images?: Array<string | { url?: string; thumbnail?: string }>;
    image_url?: string;
    stock_quantity?: number;
    stock?: number;
    sku?: string;
  }>;
  views?: number;
  created_at?: string;
  parameters?: Array<{
    name: string;
    values: Array<string | number>;
  }>;
  sku?: string;
  is_sale?: boolean;
  is_new_arrival?: boolean;
  is_featured?: boolean;
  promo_page_url?: string | null;
}

/**
 * 产品参数接口
 */
export interface ProductParameter {
  name: string;
  values: string[];
}

// 横幅
export interface Banner {
  id: number;
  title: string;
  subtitle: string;
  button_text: string;
  button_link: string;
  image_url: string;
  order: number;
  is_active: boolean;
}

// 首页设置
export interface HomepageSettings {
  site_title: string;
  site_description: string;
  products_per_page: number;
  layout: string;
  show_promotion: boolean;
  show_brands: boolean;
  featured_products_title: string;
  featured_products_subtitle: string;
  featured_products_button_text: string;
  featured_products_button_link: string;
  featured_products_banner_title?: string;
  featured_products_banner_subtitle?: string;
  offer_title?: string;
  offer_subtitle?: string;
  offer_button_text?: string;
  offer_button_link?: string;
  new_products_title?: string;
  new_products_subtitle?: string;
  new_products_button_text?: string;
  new_products_button_link?: string;
  sale_products_title?: string;
  sale_products_subtitle?: string;
  sale_products_button_text?: string;
  sale_products_button_link?: string;
}

// 首页API响应
export interface HomepageApiResponse {
  success: boolean;
  data: {
    featured_templates: ProductTemplate[];
    new_arrival_templates: ProductTemplate[];
    sale_templates: ProductTemplate[];
    banners: Banner[];
    settings: HomepageSettings;
  };
}

// 模板API分页响应
export interface TemplateApiResponse {
  success: boolean;
  data: {
    templates: ProductTemplate[];
    pagination: {
      total: number;
      per_page: number;
      current_page: number;
      last_page: number;
      from: number;
      to: number;
    };
  };
}

export interface OfferSettings {
  title?: string;
  subtitle?: string;
  buttonText?: string;
  buttonLink?: string;
  imageUrl?: string;
  // 保留后端API使用的snake_case属性以确保兼容性
  offer_title?: string;
  offer_subtitle?: string;
  offer_button_text?: string;
  offer_button_link?: string;
  offer_image?: string;
} 