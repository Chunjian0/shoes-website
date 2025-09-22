import { HomepageSettings, ProductItem } from '../../types/homepage';
import { ProductTemplate } from '../../types/apiTypes';

// 模板组件公共属性接口
export interface TemplateProps {
  // 首页设置
  settings: HomepageSettings;
  
  // 产品数据 (可选，如果通过API加载)
  featuredProducts?: ProductItem[];
  newProducts?: ProductItem[];
  saleProducts?: ProductItem[];
  
  // 产品模板数据
  templates?: {
    featured: ProductTemplate[];
    newArrival: ProductTemplate[];
    sale: ProductTemplate[];
  };
  
  // 加载状态
  isLoading?: {
    featuredProducts?: boolean;
    newProducts?: boolean;
    saleProducts?: boolean;
    categories?: boolean;
  };
  
  // 错误状态
  errors?: {
    featuredProducts?: string | null;
    newProducts?: string | null;
    saleProducts?: string | null;
    categories?: string | null;
  };
  
  // 类别数据
  categories?: Array<{
    id: string | number;
    name: string;
    slug: string;
    image?: string;
    icon?: string;
    description?: string;
    product_count?: number;
  }>;
} 