import { useState, useEffect, useCallback } from 'react';
import { Product } from '../types/product';
import { apiService as api } from '../services/api';

export const useNewProducts = () => {
  const [newProducts, setNewProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<Error | null>(null);

  const fetchNewProducts = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      console.log('[useNewProducts] 开始获取新品');
      const response: any = await api.products.getNewArrivals({});
      
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
          console.warn('[useNewProducts] API没有返回预期的数据格式', response);
      }

      setNewProducts(productsData);
      if (productsData.length > 0) {
          console.log('[useNewProducts] 成功获取新品', productsData.length);
      }

    } catch (err) {
      console.error('[useNewProducts] 获取新品失败', err);
      if (err instanceof Error) {
        setError(err);
      } else {
        setError(new Error('An unknown error occurred while fetching new products.'));
      }
      setNewProducts([]);
    } finally {
      setIsLoading(false);
    }
  }, [setIsLoading, setError]);

  useEffect(() => {
    fetchNewProducts();
  }, [fetchNewProducts]);

  return { newProducts, isLoading, error, refetch: fetchNewProducts };
}; 