import { Category } from './category';

export interface Product {
  id: number;
  name: string;
  price: number;
  sale_price?: number;
  stock: number;
  description: string;
  main_image: string;
  images: string[];
  category_name?: string;
  brand?: string;
  rating?: number;
  review_count?: number;
  is_featured?: boolean;
  is_new?: boolean;
  is_sale?: boolean;
  category?: Category;
  stock_status?: 'in_stock' | 'low_stock' | 'out_of_stock';
  sku?: string;
  created_at?: string;
  updated_at?: string;
  sizes?: string[];
  colors?: string[];
  short_description?: string;
  tags?: string[];
  promo_page_url?: string;
  specifications?: { name: string; value: string }[];
}

export interface ProductListResponse {
  data: Product[];
  meta?: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links?: {
      url: string | null;
      label: string;
      active: boolean;
    }[];
  };
}

export interface ProductReview {
  id: number;
  product_id: number;
  user_id: number;
  rating: number;
  review: string;
  user_name: string;
  created_at: string;
  updated_at: string;
}

export interface ProductAttribute {
  id: number;
  name: string;
  value: string;
  product_id: number;
}

export interface ProductVariant {
  id: number;
  product_id: number;
  sku: string;
  price: number;
  sale_price?: number;
  stock: number;
  attributes: ProductAttribute[];
} 