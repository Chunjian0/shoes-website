import { useState, useEffect, useCallback } from 'react';
import { Category } from '../types/category';
import { apiService as api } from '../services/api';

export const useCategories = () => {
  const [categories, setCategories] = useState<Category[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<Error | null>(null);

  const fetchCategories = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      console.log('[useCategories] 开始获取分类');
      const response: any = await api.categories.getAll();
      
      let categoriesData: Category[] = [];

      if (Array.isArray(response)) {
        categoriesData = response;
      } else if (response && typeof response === 'object' && Array.isArray(response.data)) {
        categoriesData = response.data;
      } else if (response && typeof response === 'object' && Array.isArray(response.categories)) {
        categoriesData = response.categories;
      } else if (response && typeof response === 'object' && response.status === 'success' && Array.isArray(response.data)) {
        categoriesData = response.data;
      } else {
        console.warn('[useCategories] API 返回数据格式不符或为空', response);
      }
      
      setCategories(categoriesData);
      if (categoriesData.length > 0) {
        console.log('[useCategories] 成功获取分类', categoriesData.length);
      }
    } catch (err) {
      console.error('[useCategories] 获取分类失败', err);
      if (err instanceof Error) {
        setError(err);
      } else {
        setError(new Error('An unknown error occurred while fetching categories.'));
      }
      setCategories([]);
    } finally {
      setIsLoading(false);
    }
  }, [setIsLoading, setError]);

  useEffect(() => {
    fetchCategories();
  }, [fetchCategories]);

  return { categories, isLoading, error, refetch: fetchCategories };
}; 