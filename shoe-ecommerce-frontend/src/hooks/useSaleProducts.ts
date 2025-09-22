import { useState, useEffect, useCallback } from 'react';
import { Product } from '../types/product';
import { apiService as api } from '../services/api';

export const useSaleProducts = () => {
  const [saleProducts, setSaleProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<Error | null>(null);

  const fetchSaleProducts = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      console.log('[useSaleProducts] 开始获取促销产品');
      const response: any = await api.products.getSale({});
      
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
          console.warn('[useSaleProducts] API没有返回预期的数据格式', response);
      }

      setSaleProducts(productsData);
      if (productsData.length > 0) {
          console.log('[useSaleProducts] 成功获取促销产品', productsData.length);
      }

    } catch (err) {
      console.error('[useSaleProducts] 获取促销产品失败', err);
      if (err instanceof Error) {
        setError(err);
      } else {
        setError(new Error('An unknown error occurred while fetching sale products.'));
      }
      setSaleProducts([]);
    } finally {
      setIsLoading(false);
    }
  }, [setIsLoading, setError]);

  useEffect(() => {
    fetchSaleProducts();
  }, [fetchSaleProducts]);

  return { saleProducts, isLoading, error, refetch: fetchSaleProducts };
}; 