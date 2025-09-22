import React, { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import TemplateCardList from '../components/common/TemplateCardList';
import FilterSidebar from '../components/filters/FilterSidebar';
import { apiService } from '../services/apiService';
import { ProductTemplate } from '../types/apiTypes';
import Pagination from '../components/common/Pagination';
import SortSelect from '../components/filters/SortSelect';

/**
 * 商店页面
 * 
 * 展示所有产品模板，支持过滤和排序
 */
const ShopPage: React.FC = () => {
  const [templates, setTemplates] = useState<ProductTemplate[]>([]);
  const [loading, setLoading] = useState(true);
  const [totalPages, setTotalPages] = useState(1);
  const [currentPage, setCurrentPage] = useState(1);
  
  const location = useLocation();
  const navigate = useNavigate();
  const searchParams = new URLSearchParams(location.search);
  
  // 获取URL参数
  const category = searchParams.get('category');
  const featured = searchParams.get('featured');
  const newParam = searchParams.get('new');
  const sale = searchParams.get('sale');
  const sort = searchParams.get('sort') || 'newest';
  const page = parseInt(searchParams.get('page') || '1', 10);
  
  // 根据筛选条件生成页面标题
  const generatePageTitle = () => {
    if (category) return `${category}`;
    if (featured === 'true') return '精选商品';
    if (newParam === 'true') return '新品上市';
    if (sale === 'true') return '促销商品';
    return '全部商品';
  };
  
  const pageTitle = generatePageTitle();
  
  // 加载商品模板数据
  useEffect(() => {
    const fetchTemplates = async () => {
      setLoading(true);
      
      try {
        // 构建查询参数
        const params: any = {
          page,
          sort
        };
        
        if (category) params.category = category;
        if (featured === 'true') params.featured = true;
        if (newParam === 'true') params.new = true;
        if (sale === 'true') params.sale = true;
        
        // 调用API获取商品模板
        const response = await apiService.templates.getAll(params);
        
        // 从 response.data 中提取 templates 和 pagination
        setTemplates(response.data?.templates || []); 
        setTotalPages(response.data?.pagination?.last_page || 1);
        setCurrentPage(response.data?.pagination?.current_page || 1);
      } catch (error) {
        console.error('获取商品模板失败:', error);
        setTemplates([]);
        setTotalPages(1);
      } finally {
        setLoading(false);
      }
    };
    
    fetchTemplates();
  }, [category, featured, newParam, sale, sort, page]);
  
  // 处理页码变化
  const handlePageChange = (newPage: number) => {
    searchParams.set('page', newPage.toString());
    navigate(`${location.pathname}?${searchParams.toString()}`);
    
    // 滚动到页面顶部
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
  
  // 处理排序变化
  const handleSortChange = (newSort: string) => {
    searchParams.set('sort', newSort);
    searchParams.set('page', '1');
    navigate(`${location.pathname}?${searchParams.toString()}`);
  };
  
  return (
    <div className="container mx-auto px-4 py-6">
      <div className="flex flex-col md:flex-row gap-6">
        {/* 侧边栏过滤器 */}
        <div className="w-full md:w-64 flex-shrink-0">
          <FilterSidebar />
        </div>
        
        {/* 主内容区域 */}
        <div className="flex-1">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h1 className="text-2xl font-bold text-gray-900 mb-4 md:mb-0">
              {pageTitle}
            </h1>
            
            {/* 排序选择 */}
            <SortSelect value={sort} onChange={handleSortChange} />
          </div>
          
          {/* 商品模板列表 */}
          <TemplateCardList
            templates={templates}
            loading={loading}
            emptyMessage="未找到符合条件的商品"
            columns={{ 
              xs: 2, 
              sm: 2, 
              md: 3, 
              lg: 3, 
              xl: 4 
            }}
            aspectRatio="3:2"
            showCategory={true}
            showDiscount={true}
          />
          
          {/* 分页控件 */}
          {totalPages > 1 && (
            <div className="mt-8">
              <Pagination
                currentPage={currentPage}
                totalPages={totalPages}
                onPageChange={handlePageChange}
              />
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ShopPage; 