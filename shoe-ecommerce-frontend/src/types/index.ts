import { Category } from './category';
// Add other types as needed...

export interface Product {
    id: number;
    name: string;
    price: number;
    sale_price?: number;
    stock?: number;
    description: string;
    main_image?: string;
    images?: string[];
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
    category_id?: number;
    selling_price?: number;
    cost_price?: number;
    parameters?: Record<string, any>;
    is_active?: boolean;
    discount_percentage?: number;
    inventory_count?: number;
    min_stock?: number;
}

export interface ProductCategory {
    id: number;
    name: string;
    code: string;
    // other fields...
}

// You might want a more specific type for CartItem, Order, Customer etc. 