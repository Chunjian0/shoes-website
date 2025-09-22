export interface Category {
  id: number;
  name: string;
  slug: string;
  image_url?: string;
  product_count?: number;
  description?: string;
  created_at?: string;
  updated_at?: string;
}

export interface CategoryListResponse {
  data: Category[];
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