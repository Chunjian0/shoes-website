import React, { useState, useEffect } from 'react';
import { Helmet } from 'react-helmet-async';
import MinimalHeader from '../components/layout/MinimalHeader';
import MinimalFooter from '../components/layout/MinimalFooter';
import FilteredProductsGrid from '../components/products/FilteredProductsGrid';
import LoadingSpinner from '../components/LoadingSpinner';

// 模拟分类数据
const mockCategories = [
  { id: 1, name: 'Running Shoes' },
  { id: 2, name: 'Casual Shoes' },
  { id: 3, name: 'Sports Shoes' },
  { id: 4, name: 'Hiking Boots' },
  { id: 5, name: 'Dress Shoes' }
];

// 模拟产品数据
const mockProducts = Array(24).fill(0).map((_, index) => ({
  id: index + 1,
  title: `Product ${index + 1}`,
  description: 'High quality footwear designed for comfort and style. Perfect for everyday use.',
  price: Math.floor(Math.random() * 150) + 50,
  discount_percentage: Math.random() > 0.7 ? Math.floor(Math.random() * 40) + 10 : 0,
  category: mockCategories[Math.floor(Math.random() * mockCategories.length)],
  image_url: `https://source.unsplash.com/random/300x300/?shoes&sig=${index}`,
  rating: (Math.random() * 3 + 2).toFixed(1),
  reviews_count: Math.floor(Math.random() * 100) + 1,
  is_new_arrival: Math.random() > 0.8,
  is_sale: Math.random() > 0.7,
  created_at: new Date(Date.now() - Math.floor(Math.random() * 90) * 24 * 60 * 60 * 1000).toISOString(),
  views: Math.floor(Math.random() * 1000)
}));

const ProductFilterDemo: React.FC = () => {
  const [products, setProducts] = useState<any[]>([]);
  const [categories, setCategories] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // 模拟数据加载
  useEffect(() => {
    const loadData = async () => {
      setLoading(true);
      
      try {
        // 在实际应用中，这里会从API获取数据
        // 这里我们使用模拟数据
        await new Promise(resolve => setTimeout(resolve, 1500)); // 模拟网络延迟
        
        setCategories(mockCategories);
        setProducts(mockProducts);
        setLoading(false);
      } catch (err: any) {
        console.error('Failed to load products:', err);
        setError(err.message || 'Failed to load products');
        setLoading(false);
      }
    };
    
    loadData();
  }, []);
  
  // 处理筛选器变更
  const handleFilterChange = (filters: any) => {
    console.log('Filters changed:', filters);
    // 在实际应用中，这里可能会触发API请求获取新的筛选数据
  };
  
  return (
    <>
      <Helmet>
        <title>Product Filters Demo | YCE Shoes</title>
        <meta name="description" content="Browse our collection of premium quality footwear with powerful filtering options." />
      </Helmet>
      
      <MinimalHeader categories={categories} />
      
      <main className="pt-20 pb-12">
        {/* 页面标题 */}
        <div className="bg-gray-50 border-b border-gray-100 py-12">
          <div className="container mx-auto px-4">
            <h1 className="text-3xl font-light text-gray-900 mb-2">Product Filters Demo</h1>
            <p className="text-gray-600 max-w-2xl">
              Browse our collection of premium quality footwear with our powerful filtering system.
              Sort, filter, and discover your perfect pair of shoes.
            </p>
          </div>
        </div>
        
        {/* 产品筛选网格 */}
        {loading ? (
          <div className="container mx-auto px-4 py-16 flex items-center justify-center">
            <LoadingSpinner size={60} />
          </div>
        ) : (
          <FilteredProductsGrid
            products={products}
            categories={categories}
            isLoading={loading}
            error={error}
            onFilterChange={handleFilterChange}
            initialFilters={{
              category: '',
              minPrice: '',
              maxPrice: '',
              sort: 'newest'
            }}
          />
        )}
      </main>
      
      <MinimalFooter />
    </>
  );
};

export default ProductFilterDemo; 