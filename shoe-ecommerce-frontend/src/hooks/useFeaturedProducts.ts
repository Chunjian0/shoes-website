import { useState, useEffect, useCallback } from 'react';
import { Product } from '../types/product';
import { apiService as api } from '../services/api';

export const useFeaturedProducts = () => {
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<Error | null>(null);

  const fetchFeaturedProducts = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      console.log('[useFeaturedProducts] 开始获取精选产品');
      const response: any = await api.products.getFeatured({});
      
      let productsData: Product[] = [];

      if (Array.isArray(response)) {
          productsData = response;
      } else if (response && typeof response === 'object' && Array.isArray(response.data?.products)) {
          productsData = response.data.products;
      } else if (response && typeof response === 'object' && Array.isArray(response.products)) {
          productsData = response.products;
      } else if (response && typeof response === 'object' && response.status === 'success' && Array.isArray(response.data)) {
          productsData = response.data;
      } else {
          console.warn('[useFeaturedProducts] API没有返回预期的数据格式', response);
      }

      setFeaturedProducts(productsData);
      if (productsData.length > 0) {
          console.log('[useFeaturedProducts] 成功获取精选产品', productsData.length);
      }

    } catch (err) {
      console.error('[useFeaturedProducts] 获取精选产品失败', err);
      if (err instanceof Error) {
        setError(err);
      } else {
        setError(new Error('An unknown error occurred while fetching featured products.'));
      }
      setFeaturedProducts([]);
    } finally {
      setIsLoading(false);
    }
  }, [setIsLoading, setError]);

  useEffect(() => {
    fetchFeaturedProducts();
  }, [fetchFeaturedProducts]);

  return { featuredProducts, isLoading, error, refetch: fetchFeaturedProducts };
}; 