import { HomepageSettings, LayoutTemplate, OfferSettings, ResponsiveSettings } from '../types/homepage';
import { ProductTemplate } from '../types/apiTypes';

// 定义一个临时的 API 响应类型
interface ApiHomepageResponse {
  settings?: Record<string, any>; // settings 对象可能包含各种字段
  banners?: any[]; // banners 数组
  featured_templates?: ProductTemplate[];
  new_arrival_templates?: ProductTemplate[];
  sale_templates?: ProductTemplate[];
  // 可以根据 API 的实际响应添加更多字段
}

/**
 * 格式化API响应数据为HomepageSettings
 */
export const formatApiDataToSettings = (apiData: ApiHomepageResponse | null | undefined): HomepageSettings => {
  if (!apiData) {
    return getDefaultSettings();
  }
  
  // 如果有layout字段，则根据layout确定模板
  let template = LayoutTemplate.MODERN;
  if (apiData.settings?.layout) {
    const layout = apiData.settings.layout.toLowerCase();
    if (layout === 'classic') template = LayoutTemplate.CLASSIC;
    else if (layout === 'standard') template = LayoutTemplate.CLASSIC; // standard使用classic模板
    else if (layout === 'minimal') template = LayoutTemplate.MINIMAL;
    else if (layout === 'bold') template = LayoutTemplate.BOLD;
    else if (layout === 'modern') template = LayoutTemplate.MODERN;
  } else if (apiData.settings?.active_template) {
    // 否则根据active_template确定模板
    const activeTemplate = apiData.settings.active_template.toLowerCase();
    if (activeTemplate === 'classic') template = LayoutTemplate.CLASSIC;
    else if (activeTemplate === 'standard') template = LayoutTemplate.CLASSIC; // standard使用classic模板
    else if (activeTemplate === 'minimal') template = LayoutTemplate.MINIMAL;
    else if (activeTemplate === 'bold') template = LayoutTemplate.BOLD;
    else if (activeTemplate === 'modern') template = LayoutTemplate.MODERN;
  }
  
  // 获取响应式设置
  const responsive: ResponsiveSettings = {
    mobile_layout: apiData.settings?.mobile_layout || 'compact',
    tablet_columns: parseInt(apiData.settings?.tablet_columns) || 2,
    desktop_columns: parseInt(apiData.settings?.desktop_columns) || 4,
    show_categories_on_mobile: apiData.settings?.show_categories_on_mobile === '1' || 
                               apiData.settings?.show_categories_on_mobile === true || true,
    enable_touch_gestures: apiData.settings?.enable_touch_gestures === '1' || 
                           apiData.settings?.enable_touch_gestures === true || true,
    breakpoints: apiData.settings?.breakpoints || {
      sm: 640,
      md: 768,
      lg: 1024,
      xl: 1280
    }
  };
  
  // 构建格式化的设置
  return {
    active_template: template,
    layout: apiData.settings?.layout || template,
    site_name: apiData.settings?.site_title || apiData.settings?.site_name || 'YCE Shoes',
    carousel: {
      autoplay: apiData.settings?.autoplay === '1' || apiData.settings?.autoplay === true || true,
      delay: parseInt(apiData.settings?.delay) || 5000,
      transition: apiData.settings?.transition || 'slide',
      showNavigation: apiData.settings?.show_navigation === '1' || apiData.settings?.show_navigation === true || true,
      showIndicators: apiData.settings?.show_indicators === '1' || apiData.settings?.show_indicators === true || true
    },
    banners: Array.isArray(apiData.banners) ? apiData.banners.map((banner: any) => ({
      id: banner.id,
      title: banner.title || '',
      subtitle: banner.subtitle || '',
      buttonText: banner.button_text || 'Shop Now',
      buttonLink: banner.button_link || '/collections',
      imageUrl: banner.image_url || banner.image || '',
      isActive: banner.is_active,
      order: banner.order
    })) : [],
    offer: formatOfferSettings(apiData.settings),
    featuredProducts: {
      title: apiData.settings?.featured_products_title || apiData.settings?.featured_title || 'Featured Products',
      subtitle: apiData.settings?.featured_products_subtitle || apiData.settings?.featured_subtitle || 'Our handpicked selection',
      buttonText: apiData.settings?.featured_products_button_text || apiData.settings?.featured_button_text || 'View All',
      buttonLink: apiData.settings?.featured_products_button_link || apiData.settings?.featured_button_link || '/products?featured=true'
    },
    newProducts: {
      title: apiData.settings?.new_products_title || apiData.settings?.new_title || 'New Arrivals',
      subtitle: apiData.settings?.new_products_subtitle || apiData.settings?.new_subtitle || 'The latest styles',
      buttonText: apiData.settings?.new_products_button_text || apiData.settings?.new_button_text || 'View All New',
      buttonLink: apiData.settings?.new_products_button_link || apiData.settings?.new_button_link || '/products?new=true'
    },
    saleProducts: {
      title: apiData.settings?.sale_products_title || apiData.settings?.sale_title || 'On Sale',
      subtitle: apiData.settings?.sale_products_subtitle || apiData.settings?.sale_subtitle || 'Special offers and discounts',
      buttonText: apiData.settings?.sale_products_button_text || apiData.settings?.sale_button_text || 'View All Sales',
      buttonLink: apiData.settings?.sale_products_button_link || apiData.settings?.sale_button_link || '/products?sale=true'
    },
    featured_products_count: parseInt(apiData.settings?.featured_products_count) || 8,
    new_products_count: parseInt(apiData.settings?.new_products_count) || 8,
    sale_products_count: parseInt(apiData.settings?.sale_products_count) || 8,
    show_promotion: apiData.settings?.show_promotion === '1' || apiData.settings?.show_promotion === true || true,
    show_brands: apiData.settings?.show_brands === '1' || apiData.settings?.show_brands === true || true,
    responsive,
    templates: {
      featured: apiData.featured_templates || [],
      newArrival: apiData.new_arrival_templates || [],
      sale: apiData.sale_templates || []
    }
  };
};

/**
 * 格式化offer设置
 */
export const formatOfferSettings = (settings: Partial<OfferSettings> | undefined): OfferSettings => {
  if (!settings) {
    return getDefaultOfferSettings();
  }
  
  // 使用 Partial<OfferSettings> 类型后，需要确保返回完整的 OfferSettings 对象
  // getDefaultOfferSettings() 返回的是完整的 OfferSettings
  const defaultOffer = getDefaultOfferSettings();

  return {
    title: settings.offer_title || settings.title || defaultOffer.title,
    subtitle: settings.offer_subtitle || settings.subtitle || defaultOffer.subtitle,
    buttonText: settings.offer_button_text || settings.buttonText || defaultOffer.buttonText,
    buttonLink: settings.offer_button_link || settings.buttonLink || defaultOffer.buttonLink,
    imageUrl: settings.offer_image || settings.offer_image_url || settings.imageUrl || defaultOffer.imageUrl,
    backgroundColor: settings.offer_background_color || settings.backgroundColor || defaultOffer.backgroundColor,
    textColor: settings.offer_text_color || settings.textColor || defaultOffer.textColor
  };
};

/**
 * 获取默认首页设置
 */
export const getDefaultSettings = (): HomepageSettings => {
  return {
    active_template: LayoutTemplate.MODERN,
    site_name: 'YCE Shoes',
    carousel: {
      autoplay: true,
      delay: 5000,
      transition: 'slide',
      showNavigation: true,
      showIndicators: true
    },
    banners: [],
    offer: getDefaultOfferSettings(),
    featuredProducts: {
      title: 'Featured Products',
      subtitle: 'Our handpicked selection',
      buttonText: 'View All',
      buttonLink: '/products?featured=true'
    },
    newProducts: {
      title: 'New Arrivals',
      subtitle: 'The latest styles',
      buttonText: 'View All New',
      buttonLink: '/products?new=true'
    },
    saleProducts: {
      title: 'On Sale',
      subtitle: 'Special offers and discounts',
      buttonText: 'View All Sales',
      buttonLink: '/products?sale=true'
    },
    featured_products_count: 8,
    new_products_count: 8,
    sale_products_count: 8,
    show_promotion: true,
    show_brands: true,
    responsive: {
      mobile_layout: 'compact',
      tablet_columns: 2,
      desktop_columns: 4,
      show_categories_on_mobile: true,
      enable_touch_gestures: true,
      breakpoints: {
        sm: 640,
        md: 768,
        lg: 1024,
        xl: 1280
      }
    },
    templates: {
      featured: [],
      newArrival: [],
      sale: []
    }
  };
};

/**
 * 获取默认优惠设置
 */
export const getDefaultOfferSettings = (): OfferSettings => {
  return {
    title: 'Special Offer',
    subtitle: 'Limited time special offers on select styles',
    buttonText: 'Shop Now',
    buttonLink: '/sale',
    imageUrl: 'https://images.unsplash.com/photo-1556906781-9a412961c28c?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1287&q=80',
    backgroundColor: '#EF4444',
    textColor: 'text-white'
  };
}; 