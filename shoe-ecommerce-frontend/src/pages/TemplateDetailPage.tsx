import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { toast } from 'react-toastify';
import LoadingSpinner from '../components/LoadingSpinner';
import { ProductTemplate, RelatedProduct, ProductParameter } from '../types/apiTypes';
import templateService from '../services/templateService';
import { api } from '../services/api';
import TemplateCard from '../components/templates/TemplateCard';
import ImageGallery from 'react-image-gallery';
import 'react-image-gallery/styles/css/image-gallery.css';
import CheckIcon from '../components/icons/CheckIcon';
import ExclamationCircleIcon from '../components/icons/ExclamationCircleIcon';
import { useAppDispatch, useAppSelector } from '../store'; // Use typed hooks
import { fetchCart, addToCart, selectActiveCart } from '../store/slices/cartSlice';
import cartService from '../services/cartService';
import loggerService from '../services/loggerService';
import CreateCartModal from '../components/modals/CreateCartModal';

// Helper function to parse parameter_group string
const parseParameterGroup = (groupString: string | undefined | null): Record<string, string> => {
  if (!groupString) return {};
  const params: Record<string, string> = {};
  groupString.split(';').forEach(pair => {
    const parts = pair.split('=');
    if (parts.length === 2) {
      const key = parts[0].trim().toLowerCase();
      const value = parts[1].trim().toLowerCase();
      if (key && value) {
        params[key] = value;
      }
    }
  });
  return params;
};

const TemplateDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  
  // 状态
  const [template, setTemplate] = useState<ProductTemplate | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [activeImageIndex, setActiveImageIndex] = useState(0);
  const [selectedParameters, setSelectedParameters] = useState<Record<string, string>>({});
  const [selectedProduct, setSelectedProduct] = useState<RelatedProduct | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [activeTab, setActiveTab] = useState('description');
  const [relatedTemplates, setRelatedTemplates] = useState<ProductTemplate[]>([]);
  const [loadingRelated, setLoadingRelated] = useState(false);
  const [selectedProductId, setSelectedProductId] = useState<number | null>(null);
  const [stockWarning, setStockWarning] = useState<string | null>(null);
  const [totalStock, setTotalStock] = useState(0);
  const [lowStockProducts, setLowStockProducts] = useState<RelatedProduct[]>([]);
  const [isAddingToCart, setIsAddingToCart] = useState(false);
  const [showCartDropdown, setShowCartDropdown] = useState(false);
  const [loadingCarts, setLoadingCarts] = useState(false);
  const [availableCarts, setAvailableCarts] = useState<any[]>([]);
  const dispatch = useAppDispatch(); // Use typed dispatch
  const activeCart = useAppSelector(selectActiveCart);
  const [showCreateCartModal, setShowCreateCartModal] = useState(false);
  const [isBuyNowLoading, setIsBuyNowLoading] = useState(false);
  
  // refs
  const imagesRef = useRef<HTMLDivElement>(null);
  const parametersRef = useRef<HTMLDivElement>(null);

  // 处理模板数据，计算总库存和低库存产品
  const processTemplateData = (templateData: ProductTemplate) => {
    if (templateData && templateData.linked_products) {
      let totalStock = 0;
      const lowStockThreshold = 10;
      const lowStockProducts: RelatedProduct[] = [];
      
      templateData.linked_products.forEach(product => {
        const stock = product.stock_quantity || product.stock || 0;
        totalStock += stock;
        
        if (stock > 0 && stock <= lowStockThreshold) {
          lowStockProducts.push(product as any as RelatedProduct); // Cast to RelatedProduct, acknowledging the type mismatch from ProductTemplate.linked_products
        }
      });
      
      setTotalStock(totalStock);
      setLowStockProducts(lowStockProducts);
      
      // 如果没有选择产品，默认选择第一个
      if (!selectedProductId && templateData.linked_products.length > 0) {
        setSelectedProductId(templateData.linked_products[0].id);
      }
    }
  };

  // 加载模板数据
  useEffect(() => {
    const loadTemplateData = async () => {
      try {
        setLoading(true);
        setError(null);

        console.log(`Loading template #${id} details from API (no cache)`);
        const numericId = parseInt(id || '', 10);
        if (isNaN(numericId)) {
          setError('Invalid Template ID.');
          setLoading(false);
          return;
        }
        const data = await templateService.getTemplateDetails(numericId);
        setTemplate(data);
        processTemplateData(data);

        // 获取模板详情后再加载相关模板
        loadRelatedTemplates(data.id, data.category?.name);

      } catch (err) {
        setError('Unable to load template details. Please try again later.');
        console.error('Error loading template details:', err);
      } finally {
        setLoading(false);
      }
    };
    
    // 单独定义加载相关模板的函数
    const loadRelatedTemplates = async (templateId: number, categoryName?: string) => {
      try {
        console.log(`Loading related templates from API (no cache)`);
        setLoadingRelated(true);
        const relatedData = await templateService.getRelatedTemplates(templateId, categoryName);
        setRelatedTemplates(relatedData);

      } catch (err) {
        console.error('Error loading related templates:', err);
      } finally {
        setLoadingRelated(false);
      }
    };
    
    // 调试函数：分析产品参数匹配
    const debugParameterMatching = (templateData: ProductTemplate) => {
      console.log("===== 参数匹配调试信息 =====");
      
      // Check if parameters is an object and has keys, and linked_products exists
      if (!templateData.linked_products || typeof templateData.parameters !== 'object' || templateData.parameters === null || Object.keys(templateData.parameters).length === 0) {
        console.log("参数不是有效对象或没有关联产品可分析");
        if (templateData.parameters) {
           console.log("参数的实际类型:", typeof templateData.parameters, templateData.parameters);
        }
        return;
      }
      
      // Iterate over the object entries
      Object.entries(templateData.parameters).forEach(([paramName, paramValues]) => {
        // Ensure paramValues is an array before iterating its values
        if (!Array.isArray(paramValues)) {
          console.log(`参数 "${paramName}" 的值不是数组:`, paramValues);
          return; // Skip this parameter
        }

        console.log(`参数: ${paramName}`);
        
        paramValues.forEach(value => {
          console.log(`- 值: \"${value}\" (长度: ${value.length})`);
          
          // 查找匹配的产品 (Ensure linked_products is checked above)
          const matchingProducts = templateData.linked_products!.filter(product => {
            // Use type assertion as parameter_group is missing from linked_products type def
            if (!(product as any)?.parameter_group) return false; 
            
            const paramGroups = (product as any).parameter_group.split(';');
            return paramGroups.some((group: string) => { // Add type for group
              const [pName, pValue] = group.split('=');
              return pName && pValue && 
                     pName.trim().toLowerCase() === paramName.toLowerCase() && // Use paramName from Object.entries
                     pValue.trim().toLowerCase() === value.trim().toLowerCase();
            });
          });
          
          console.log(`  - 匹配产品数: ${matchingProducts.length}`);
          matchingProducts.forEach(p => {
            // Use type assertion
            console.log(`    * 产品 #${p.id}: ${p.name}, 参数组: ${(p as any)?.parameter_group ?? 'N/A'}`); 
          });
        });
      });
      
      console.log("===== 调试信息结束 =====");
    };
    
    if (id) {
      loadTemplateData();
    }
  }, [id]); // Simplified dependency array

  // 查找匹配当前选择参数的产品
  const findMatchingProduct = (templateData: ProductTemplate, params: Record<string, string>) => {
    if (!templateData.linked_products || templateData.linked_products.length === 0) {
      console.log('[findMatchingProduct] No linked products found');
      setSelectedProduct(null);
      return;
    }
    
    console.log('[findMatchingProduct] Finding product with params:', params);
    
    const products = templateData.linked_products || [];
    
    // Count the number of parameters being requested
    const paramCount = Object.keys(params).length;
    
    // If no parameters selected or empty params object, don't try to match
    if (paramCount === 0) {
      console.log('[findMatchingProduct] No parameters selected, showing template images');
      setSelectedProduct(null);
      return;
    }
    
    // Get all parameter names available in the template
    const allParamNames = templateData.parameters ? Object.keys(templateData.parameters) : [];
    
    // Check if we have all necessary parameters to determine a unique product
    // This is a heuristic - if there are more parameters in the template than we've selected,
    // and we've only selected one parameter, we might not have enough info to pick a product
    if (paramCount === 1 && allParamNames.length > 1) {
      console.log('[findMatchingProduct] Only one parameter selected, might not be enough to determine product');
      
      // Count how many products match this single parameter
      const normalizedParams = { ...params };
      const paramName = Object.keys(normalizedParams)[0];
      const paramValue = normalizedParams[paramName];
      
      // Count products matching this single parameter
      const matchingProducts = products.filter(product => {
        // Use type assertion
        if (!(product as any)?.parameter_group) return false;
        const productParams = parseParameterGroup((product as any).parameter_group);
        return productParams[paramName.toLowerCase()] === paramValue.toLowerCase();
      });
      
      // If multiple products match this parameter, we don't have enough info
      if (matchingProducts.length > 1) {
        console.log(`[findMatchingProduct] ${matchingProducts.length} products match single parameter, showing template images`);
        setSelectedProduct(null);
        return;
      }
    }
    
    // Normalize parameter keys/values (case insensitive)
    const normalizedParams: Record<string, string> = {};
    Object.entries(params).forEach(([key, value]) => {
      normalizedParams[key.toLowerCase().trim()] = value.toLowerCase().trim();
    });
    
    // 1. 使用parameter_group匹配参数
    let exactMatch = products.find(product => {
      // Use type assertion
      if (!(product as any)?.parameter_group) return false;
      
      // 解析parameter_group字符串 (格式如 "color=red;size=small")
      const productParams: Record<string, string> = {};
      (product as any).parameter_group.split(';').forEach((pair: string) => { // Add type for pair
        const parts = pair.split('=');
        if (parts.length === 2) {
          const key = parts[0].toLowerCase().trim();
          const value = parts[1].toLowerCase().trim();
          productParams[key] = value;
        }
      });
      
      // 检查所有请求的参数是否匹配
      for (const [key, value] of Object.entries(normalizedParams)) {
        // 如果请求的参数在产品中不存在，或值不匹配，则返回false
        if (!productParams[key] || productParams[key] !== value) {
          return false;
        }
      }
      
      // 所有参数都匹配，返回true
      return true;
    });
    
    // 2. 尝试匹配variations字段 (如果参数组匹配失败)
    if (!exactMatch) {
      console.log('[findMatchingProduct] 参数组匹配失败, 尝试匹配variations字段');
      
      exactMatch = products.find(product => {
        // Use type assertion for variations
        if (!(product as any)?.variations) return false; 
        
        for (const [key, value] of Object.entries(normalizedParams)) {
          // 标准化variations中的键和值
          
          let matchFound = false;
          
          // Use type assertion
          for (const [varKey, varValue] of Object.entries((product as any).variations ?? {})) { 
            const normalizedVarKey = varKey.toLowerCase().trim();
            const normalizedVarValue = String(varValue).toLowerCase().trim();
            
            if (normalizedVarKey === key && normalizedVarValue === value) {
              matchFound = true;
              break;
            }
          }
          
          if (!matchFound) return false;
        }
        
        return true;
      });
    }

    if (exactMatch) {
      // 确保产品有有效价格和库存数据
      const enhancedProduct = {
        ...exactMatch,
        price: exactMatch.price || 0,
        // original_price might not exist on exactMatch (from linked_products)
        original_price: (exactMatch as any).original_price || exactMatch.price || 0, 
        stock_quantity: exactMatch.stock_quantity || 0,
        discount_percentage: exactMatch.discount_percentage || 0,
        // parameters might not exist on exactMatch
        parameters: (exactMatch as any).parameters || {} 
      };
      console.log('[findMatchingProduct] Selected matching product:', enhancedProduct);
      loggerService.info('PRODUCT', 'Final selected matching product', {
        product_id: enhancedProduct.id,
        name: enhancedProduct.name,
        selected_params: normalizedParams
      });
      // Cast to RelatedProduct for state, acknowledging potential missing fields compared to definition
      setSelectedProduct(enhancedProduct as any as RelatedProduct); 
      setSelectedProductId(enhancedProduct.id); // 确保更新选中的产品ID
    } else {
      // 没有找到完全匹配的产品
      console.log('[findMatchingProduct] No exact match found, showing template images');
      setSelectedProduct(null);
      // 不改变selectedProductId，保持之前的状态
    }
  };
  
  // 处理参数变更
  const handleParameterChange = (paramName: string, value: string) => {
    // 确保值没有前后空格
    const trimmedParamName = paramName.trim();
    const trimmedValue = value.trim();
    
    // 如果当前选中的值与新点击的值相同，则取消选择
    if (selectedParameters[trimmedParamName] === trimmedValue) {
      console.log(`[handleParameterChange] Unselecting parameter: ${trimmedParamName}=${trimmedValue}`);
      loggerService.debug('PARAMETER', `User unselected parameter`, {
        parameter: trimmedParamName,
        previous_value: trimmedValue
      });
      
      // 创建新的参数对象，排除被取消的参数
      const newParams = { ...selectedParameters };
      delete newParams[trimmedParamName];
      setSelectedParameters(newParams);
      
      if (template) {
        findMatchingProduct(template, newParams);
      }
    } else {
      // 选择新参数
      console.log(`[handleParameterChange] Selecting parameter: ${trimmedParamName}=${trimmedValue}`);
      loggerService.debug('PARAMETER', `User selected parameter`, {
        parameter: trimmedParamName,
        value: trimmedValue,
        previous: selectedParameters[trimmedParamName]
      });
      
      const newParams = { ...selectedParameters, [trimmedParamName]: trimmedValue };
      setSelectedParameters(newParams);
      
      if (template) {
        findMatchingProduct(template, newParams);
      }
    }
    
    // 滚动到变化的参数区域，增强视觉反馈
    if (parametersRef.current) {
      parametersRef.current.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  };
  
  // 增加数量
  const increaseQuantity = () => {
    setQuantity(prev => prev + 1);
  };
  
  // 减少数量
  const decreaseQuantity = () => {
    setQuantity(prev => (prev > 1 ? prev - 1 : 1));
  };
  
  // 处理数量变更
  const handleQuantityChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value) && value > 0) {
      setQuantity(value);
    }
  };
  
  // 切换活动图片
  const handleImageChange = (index: number) => {
    setActiveImageIndex(index);
    
    // 滚动到变化的图片区域，增强视觉反馈
    if (imagesRef.current) {
      imagesRef.current.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  };
  
  // 商品选择变更
  const handleProductChange = (productId: number) => {
    setSelectedProductId(productId);
    
    // 检查库存
    if (template && template.linked_products) {
      const product = template.linked_products.find(p => p.id === productId);
      if (product) {
        const stock = product.stock_quantity || product.stock || 0;
        
        // 重置数量
        setQuantity(1);
        
        // 库存检查
        if (stock <= 0) {
          setStockWarning('此产品已无库存');
        } else if (stock < 10) {
          setStockWarning(`仅剩 ${stock} 个，抢购中`);
        } else {
          setStockWarning(null);
        }
      }
    }
  };
  
  // 加载购物车列表
  const loadCarts = async () => {
    setLoadingCarts(true);
    try {
      // 直接调用 api.get('/cart')
      const response = await api.get('/cart'); 
      console.log('[TemplateDetailPage] Load carts response:', response);
      // 检查 Axios 响应结构 (response.data) 和 API 返回的结构
      if (response.data?.success && response.data?.data?.carts) {
        setAvailableCarts(response.data.data.carts);
      } else {
        console.warn('[TemplateDetailPage] Failed to load carts or no carts found:', response.data);
        setAvailableCarts([]);
      }
    } catch (error: any) {
      console.error('[TemplateDetailPage] Failed to load carts:', error);
      toast.error(`Failed to load carts: ${error.message || 'Unknown error'}`);
      setAvailableCarts([]);
    } finally {
      setLoadingCarts(false);
    }
  };

  // 加入指定购物车
  const handleAddToSpecificCart = async (cartId: number) => {
    if (!template || !selectedProductId) return;
    
    const selectedProduct = template.linked_products?.find(p => p.id === selectedProductId);
    if (!selectedProduct) return;
    
    const stock = selectedProduct.stock_quantity || selectedProduct.stock || 0;
    
    // 检查库存是否足够
    if (stock < quantity) {
      toast.error(`库存不足，当前仅剩 ${stock} 个`);
      return;
    }
    
    setIsAddingToCart(true);
    
    try {
      // 提取选定参数的size和color
      const size = selectedParameters['size'] || selectedParameters['尺码'] || selectedParameters['Size'];
      const color = selectedParameters['color'] || selectedParameters['颜色'] || selectedParameters['Color'];
      
      // 使用新的购物车服务添加商品
      const response = await cartService.addToCart({
        product_id: selectedProductId,
        quantity: quantity,
        size: size,
        color: color,
        cart_id: cartId
      });
      
      console.log('添加购物车响应:', response);
      
      if (response?.success) {
      toast.success(`已将 ${quantity} 个 ${selectedProduct.name} 加入购物车`);
        
        // 更新本地库存缓存
        if (template && template.linked_products) {
          const updatedLinkedProducts = template.linked_products.map(p => {
            if (p.id === selectedProductId) {
              const newStock = (p.stock_quantity || p.stock || 0) - quantity;
              return { 
                ...p, 
                stock_quantity: newStock,
                stock: newStock
              };
            }
            return p;
          });
          
          const updatedTemplate = { 
            ...template, 
            linked_products: updatedLinkedProducts 
          };
          
          // 更新状态
          setTemplate(updatedTemplate);
          
          // 重新处理库存数据
          processTemplateData(updatedTemplate);
        }
        
        // 重新加载购物车数据
        dispatch(fetchCart());
      } else {
        toast.error(response?.message || '添加购物车失败，请稍后再试');
      }
    } catch (error) {
      console.error('添加购物车错误:', error);
      toast.error('添加购物车时发生错误');
    } finally {
      setIsAddingToCart(false);
      setShowCartDropdown(false);
    }
  };
  
  // 加入购物车
  const handleAddToCart = async () => {
    if (!template || !selectedProductId) {
      toast.error('请先选择一个产品规格');
      return;
    }
    
    const selectedProduct = template.linked_products?.find(p => p.id === selectedProductId);
    if (!selectedProduct) {
      toast.error('无法找到所选产品，请刷新页面重试');
      return;
    }
    
    // 记录选中的产品信息，便于调试
    console.log('[handleAddToCart] 选中的产品信息:', {
      id: selectedProduct.id,
      name: selectedProduct.name,
      sku: selectedProduct.sku,
      parameter_group: (selectedProduct as any)?.parameter_group, // Use type assertion
      parent_template_id: template.id
    });
    
    // 使用日志服务记录购物车操作开始
    loggerService.logCartOperation('开始添加', {
      product_id: selectedProductId,
      name: selectedProduct.name,
      sku: selectedProduct.sku,
      parent_template_id: template.id,
      quantity: quantity
    });
    
    const stock = selectedProduct.stock_quantity || selectedProduct.stock || 0;
    
    // 检查库存是否足够
    if (stock < quantity) {
      toast.error(`库存不足，当前仅剩 ${stock} 个`);
      return;
    }
    
    setIsAddingToCart(true);
    
    try {
      // 提取选定参数的size和color
      const size = selectedParameters['size'] || selectedParameters['尺码'] || selectedParameters['Size'];
      const color = selectedParameters['color'] || selectedParameters['颜色'] || selectedParameters['Color'];
      
      // 明确记录要添加到购物车的详细信息
      console.log('[handleAddToCart] 添加商品到购物车，详细信息:', {
        product_id: selectedProductId, // 明确使用selectedProductId
        product_name: selectedProduct.name,
        product_sku: selectedProduct.sku,
        template_id: template.id,
        color: color,
        size: size,
        quantity: quantity
      });
      
      // 使用日志服务记录最终参数
      loggerService.logCartOperation('参数构建', {
        product_id: selectedProductId,
        product_id_type: typeof selectedProductId,
        quantity: quantity,
        size: size,
        color: color,
        template_id: template.id
      });
      
      // 最终确认发送的参数 - 添加JSON字符串形式以检查序列化后的结果
      console.log('[handleAddToCart] 发送前最终确认参数:', JSON.stringify({
        product_id: selectedProductId,
        quantity: quantity,
        size: size ?? undefined, // Convert null to undefined
        color: color ?? undefined, // Convert null to undefined
        template_id: template.id
      }));
      
      // 使用购物车服务添加商品
      const response = await cartService.addToCart({
        product_id: selectedProductId, // 确保使用正确的product_id
        quantity: quantity,
        size: size ?? undefined, // Convert null to undefined
        color: color ?? undefined, // Convert null to undefined
        template_id: template.id
      });
      
      console.log('[handleAddToCart] 添加购物车响应:', response);
      
      // 记录添加结果
      loggerService.logCartOperation(
        response?.success ? '添加成功' : '添加失败', 
        {
          response: response,
          product_id: selectedProductId
        }
      );
      
      if (response?.success) {
        // 显示成功消息
        toast.success(`已将 ${quantity} 个 ${selectedProduct.name} 加入购物车`);
        
        // 更新本地库存缓存
        if (template && template.linked_products) {
          const updatedLinkedProducts = template.linked_products.map(p => {
            if (p.id === selectedProductId) {
              const newStock = (p.stock_quantity || p.stock || 0) - quantity;
              return { 
                ...p, 
                stock_quantity: newStock,
                stock: newStock
              };
            }
            return p;
          });
          
          const updatedTemplate = { 
            ...template, 
            linked_products: updatedLinkedProducts 
          };
          
          // 更新状态
          setTemplate(updatedTemplate);
          
          // 重新处理库存数据
          processTemplateData(updatedTemplate);
        }
        
        // 重新加载购物车数据
        dispatch(fetchCart());
      } else {
        // 显示错误消息
        toast.error(response?.message || '添加购物车失败，请稍后再试');
      }
    } catch (error: any) {
      console.error('[handleAddToCart] 添加购物车错误:', error);
      
      // 记录错误
      loggerService.error('CART', '添加购物车错误', {
        error: error.message,
        response: error.response?.data,
        product_id: selectedProductId
      });
      
      // 提取更有用的错误信息
      let errorMessage = '添加购物车时发生错误';
      
      if (error.response?.data) {
        if (error.response.data.message) {
          errorMessage = error.response.data.message;
        } else if (error.response.data.error) {
          errorMessage = `服务器错误: ${error.response.data.error}`;
        }
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      toast.error(errorMessage);
    } finally {
      setIsAddingToCart(false);
    }
  };
  
  // Handle Buy Now click
  const handleBuyNowClick = async () => {
    if (!selectedProduct) {
      toast.error('Please select a product configuration first.');
      return;
    }
    if (isBuyNowLoading || isAddingToCart) return; // Prevent double clicks

    setIsBuyNowLoading(true);
    console.log('Buy Now clicked for:', selectedProduct, 'with quantity:', quantity, 'and params:', selectedParameters);
    loggerService.info('USER_ACTION', 'buy_now_click', {
      product_id: selectedProduct.id,
      template_id: template?.id,
      quantity: quantity,
      specifications: selectedParameters
    });

    try {
      // 使用正确的API路径，不要重复/api前缀
      const response = await api.post('/checkout/buy-now', {
        product_id: selectedProduct.id,
        quantity: quantity,
        specifications: selectedParameters
      });

      console.log('Buy Now response:', response);

      // 正确处理嵌套的响应结构，响应格式是 response.data.data
      if (response.data && response.data.success) {
        const temporaryOrderId = response.data.order_id;
        console.log('Temporary order created with ID:', temporaryOrderId);
        toast.success('Order created, redirecting to checkout...');
        // Navigate to checkout page with the temporary order ID
        navigate(`/checkout?orderId=${temporaryOrderId}`);
      } else {
        console.error('Buy Now failed:', response);
        toast.error('Unable to start Buy Now process. Please try again later.');
      }
    } catch (error: any) {
        console.error('Error during Buy Now:', error);
        // 详细记录错误信息以便调试
        loggerService.error('BUY_NOW_ERROR', 'Error during Buy Now process', { 
           product_id: selectedProduct.id, 
           error: error.message || error.toString(),
           responseData: error.response?.data
        });
        
        let errorMessage = 'An error occurred during the Buy Now process. Please try again later.';
        // 检查是否是库存不足错误
        if (error.response?.data?.message?.includes('库存不足') || error.response?.status === 409) {
            errorMessage = error.response.data.message || 'Product is out of stock.';
        } else if (error.response?.data?.message) {
            errorMessage = `Error: ${error.response.data.message}`;
        }
        toast.error(errorMessage);
    } finally {
      setIsBuyNowLoading(false);
    }
  };
  
  // 调试信息显示
  const DebugInfo = () => {
    // 开发环境下才显示
    if (process.env.NODE_ENV !== 'development') {
      return null;
    }
    
    // 导出日志
    const exportLogs = () => {
      const logs = loggerService.exportLogs();
      
      // 创建Blob对象
      const blob = new Blob([logs], { type: 'text/plain;charset=utf-8' });
      
      // 创建临时下载链接
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `frontend-logs-${new Date().toISOString().replace(/[:.]/g, '-')}.txt`;
      
      // 触发下载
      document.body.appendChild(a);
      a.click();
      
      // 清理
      setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
      }, 0);
    };
    
    // 查看日志
    const viewLogs = () => {
      const logs = loggerService.getLogs();
      console.log('应用日志:', logs);
      
      // 显示带格式的日志
      const formattedLogs = logs.map(log => 
        `[${log.timestamp}] [${log.level}] [${log.category}] ${log.message} ${log.data ? JSON.stringify(log.data, null, 2) : ''}`
      ).join('\n\n');
      
      alert('日志已导出到控制台。查看更多日志请点击"导出日志"按钮。');
    };
    
    return (
      <div className="mt-8 border border-orange-300 rounded-md p-4 bg-orange-50">
        <h3 className="font-bold text-lg mb-2 text-orange-800">Debug Info</h3>
        
        <div className="mb-4 flex items-center space-x-2">
          <button 
            className="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm"
            onClick={viewLogs}
          >
            查看日志
          </button>
          <button 
            className="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm"
            onClick={exportLogs}
          >
            导出日志
          </button>
          <button 
            className="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm"
            onClick={() => loggerService.clearLogs()}
          >
            清空日志
          </button>
        </div>
        
        <div className="overflow-x-auto">
          <div className="mb-4">
            <h4 className="font-semibold text-orange-700">Request Parameters:</h4>
            <pre className="bg-white p-2 rounded mt-1 text-xs overflow-x-auto">
              {JSON.stringify({ templateId: id }, null, 2)}
            </pre>
          </div>
          
          <div className="mb-4">
            <h4 className="font-semibold text-orange-700">Selected Parameters:</h4>
            <pre className="bg-white p-2 rounded mt-1 text-xs overflow-x-auto">
              {JSON.stringify(selectedParameters, null, 2)}
            </pre>
          </div>
          
          <div className="mb-4">
            <h4 className="font-semibold text-orange-700">Template Data:</h4>
            <pre className="bg-white p-2 rounded mt-1 text-xs overflow-x-auto max-h-60">
              {template ? JSON.stringify(template, null, 2) : 'Loading...'}
            </pre>
          </div>
          
          <div>
            <h4 className="font-semibold text-orange-700">Selected Product:</h4>
            <pre className="bg-white p-2 rounded mt-1 text-xs overflow-x-auto">
              {selectedProduct ? JSON.stringify(selectedProduct, null, 2) : 'No product selected'}
            </pre>
          </div>
        </div>
      </div>
    );
  };
  
  // --- New Parameter Combination Availability Check ---
  const isCombinationAvailable = (paramName: string, value: string): boolean => {
    if (!template || !template.linked_products || template.linked_products.length === 0) {
      // If no template or linked products, assume unavailable? Or available? Let's default to true for initial load.
      return true; 
    }

    // Normalize the parameter name and value being checked
    const normalizedParamName = paramName.toLowerCase().trim();
    const normalizedValue = value.toLowerCase().trim();
    
    // Construct the potential next full combination
    const potentialSelection = { ...selectedParameters }; 
    potentialSelection[normalizedParamName] = normalizedValue;

    // Normalize the potential selection keys/values just in case
    const normalizedPotentialSelection: Record<string, string> = {};
    Object.entries(potentialSelection).forEach(([key, val]) => {
      normalizedPotentialSelection[key.toLowerCase().trim()] = val.toLowerCase().trim();
    });

    // Get the set of all parameter names defined for the template
    const allTemplateParamNames = Object.keys(template.parameters || {}).map(k => k.toLowerCase().trim());

    // Check if any linked product matches this potential combination
    return template.linked_products.some(product => {
      // Use type assertion
      const productParams = parseParameterGroup((product as any)?.parameter_group);
      
      // Optimization: If product doesn't even have the current param=value, skip
      if (productParams[normalizedParamName] !== normalizedValue) {
         return false;
          }

      // Check if *all* parameters in the potential selection are matched by the product's params
      // Also ensure the product doesn't have *extra* parameters not defined in the template (unless variations logic handles this)
      let match = true;
      for (const [key, val] of Object.entries(normalizedPotentialSelection)) {
        if (productParams[key] !== val) {
          match = false;
          break;
        }
      }
      
      // Optional stricter check: Ensure the product doesn't have extra parameters
      // that aren't part of the *template's* defined parameters
      // for (const productKey in productParams) {
      //    if (!allTemplateParamNames.includes(productKey)) {
             // Might be an invalid product or uses variations, handle as needed
      //    }
      // }

      return match;
    });
  };

  // --- End of New Check ---

  // 检查参数选项是否可用（基于库存等） - This function is now deprecated by isCombinationAvailable
  // const isParameterValueAvailable = (paramName: string, value: string): boolean => {
    // ... (old implementation removed) ...
  // };
  
  // 获取参数显示样式
  const getParameterValueStyle = (paramName: string, value: string): string => {
    // 确保比较时使用trim值
    const trimmedParamName = paramName.trim(); // Trim paramName as well
    const trimmedValue = value.trim();
    const isSelected = selectedParameters[trimmedParamName]?.trim() === trimmedValue;
    // Use the new combination check
    const isAvailable = isCombinationAvailable(trimmedParamName, trimmedValue); 
    
    if (isSelected) {
      return 'border-blue-600 bg-blue-50 text-blue-600';
    } else if (isAvailable) {
      return 'border-gray-300 hover:border-blue-600 text-gray-700';
    } else {
      return 'border-gray-200 text-gray-400 cursor-not-allowed opacity-60';
    }
  };
  
  // 决定使用哪种参数渲染器
  const renderParameterSelector = (param: ProductParameter) => {
    // 统一使用文本按钮样式
    return (
      <div className="flex flex-wrap gap-2">
        {param.values.map((value) => {
          const trimmedValue = value.trim();
          const isAvailable = isCombinationAvailable(param.name, trimmedValue);
          const isSelected = selectedParameters[param.name] === trimmedValue;
          
          console.log(`[renderParameterSelector] Parameter value: ${param.name}=${value}, trimmed: ${trimmedValue}, available: ${isAvailable}, selected: ${isSelected}`);
          
          return (
            <button
              key={trimmedValue}
              onClick={() => isAvailable && handleParameterChange(param.name, trimmedValue)}
              className={`px-4 py-2 border rounded-md transition-all ${
                !isAvailable 
                  ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' 
                  : isSelected
                    ? 'bg-blue-100 border-blue-500 text-blue-700 font-medium shadow-sm'
                    : 'bg-white hover:bg-gray-50 border-gray-300 text-gray-700 hover:text-gray-900'
              }`}
              disabled={!isAvailable}
            >
              {isSelected && (
                <span className="mr-1">✓</span>
              )}
              {trimmedValue}
            </button>
          );
        })}
      </div>
    );
  };
  
  // Handle creating a new cart
  const handleCreateCartSubmit = async (cartData: { name: string; type: string; is_default?: boolean }) => {
    console.log('[TemplateDetailPage] Submitting create cart:', cartData);
    try {
      await cartService.createCart(cartData);
      toast.success(`Cart "${cartData.name}" created successfully!`);
      setShowCreateCartModal(false); // Close modal on success
      loadCarts(); // Refresh cart list in dropdown
    } catch (error: any) {
      console.error('[TemplateDetailPage] Failed to create cart:', error);
      const errorMessage = error.response?.data?.message || error.message || 'An unknown error occurred';
      toast.error(`Failed to create cart: ${errorMessage}`);
      // Optionally keep modal open on error: setShowCreateCartModal(true);
    }
  };
  
  if (loading) {
    return (
      <div className="container mx-auto px-4 py-12 flex items-center justify-center min-h-screen">
        <LoadingSpinner size="large" />
        <p className="ml-4 text-lg text-gray-600">Loading product template...</p>
      </div>
    );
  }

  if (error || !template) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <svg className="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
              </svg>
            </div>
            <div className="ml-3">
              <h3 className="text-sm leading-5 font-medium text-red-800">Error</h3>
              <div className="mt-1 text-sm leading-5 text-red-700">
                {error || 'Unable to load product template data. Please try again later.'}
              </div>
              <div className="mt-4">
                <Link
                  to="/"
                  className="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition ease-in-out duration-150"
                >
                  Return to Homepage
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  // 准备图片库数据
  const galleryImages = template.images && template.images.length > 0
    ? template.images.map(img => {
        // Handle union type: string | { url?, thumbnail?, image_url? }
        if (typeof img === 'string') {
          return {
            original: img,
            thumbnail: img, // Use original if no thumbnail string provided
            originalAlt: template.title,
            thumbnailAlt: template.title
          };
        } else if (typeof img === 'object' && img !== null) {
          return {
            original: img.url || img.image_url || 'https://via.placeholder.com/600x400?text=No+Image', // Provide fallback
            thumbnail: img.thumbnail || img.url || img.image_url || 'https://via.placeholder.com/150x100?text=No+Image', // Provide fallback
            originalAlt: template.title,
            thumbnailAlt: template.title
          };
        }
        return null; // Should not happen based on type, but satisfy TS
      }).filter(img => img !== null) // Filter out any null results
    : [
        {
          original: 'https://via.placeholder.com/600x400?text=No+Image',
          thumbnail: 'https://via.placeholder.com/150x100?text=No+Image',
          originalAlt: 'No Image Available',
          thumbnailAlt: 'No Image Thumbnail'
        }
      ];

  // 获取当前选择产品的库存状态
  const getStockStatus = () => {
    if (!selectedProduct) return 'unknown';
    
    const stock = selectedProduct.stock_quantity || selectedProduct.stock || 0;
    if (stock <= 0) return 'out_of_stock';
    if (stock < 10) return 'low_stock';
    return 'in_stock';
  };
  
  // 库存状态显示
  const renderStockStatus = () => {
    const status = getStockStatus();
    
    if (status === 'out_of_stock') {
      return (
        <div className="flex items-center text-red-600">
          <ExclamationCircleIcon className="h-5 w-5 mr-1" />
          <span>Out of Stock</span>
        </div>
      );
    } else if (status === 'low_stock') {
      return (
        <div className="flex items-center text-amber-600">
          <ExclamationCircleIcon className="h-5 w-5 mr-1" />
          <span>Only {selectedProduct?.stock_quantity || selectedProduct?.stock} left, selling fast</span>
        </div>
      );
    } else if (status === 'in_stock') {
      return (
        <div className="flex items-center text-green-600">
          <CheckIcon className="h-5 w-5 mr-1" />
          <span>In Stock</span>
        </div>
      );
    }
    
    return null;
  };

  // 模板数据已加载
  return (
    <>
      {/* Breadcrumb navigation */}
      <div className="bg-gray-50 border-b">
        <div className="container mx-auto px-4 py-3">
          <nav className="flex text-sm">
            <Link to="/" className="text-gray-500 hover:text-gray-700">Home</Link>
            <span className="mx-2 text-gray-400">/</span>
            {template.category && (
              <>
                <Link to={`/category/${template.category.id}`} className="text-gray-500 hover:text-gray-700">
                  {template.category.name}
                </Link>
                <span className="mx-2 text-gray-400">/</span>
              </>
            )}
            <span className="text-gray-900 font-medium">{template.title}</span>
          </nav>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <div className="flex flex-col md:flex-row -mx-4">
          {/* 左侧商品图片区域 */}
          <div className="md:w-1/2 px-4 mb-8 md:mb-0" ref={imagesRef}>
            <div className="relative bg-gray-100 rounded-lg overflow-hidden h-96 md:h-[500px] mb-4">
              <AnimatePresence>
                <motion.img
                  key={activeImageIndex}
                  src={
                    // 优先使用选中产品的图片，如果没有则使用模板图片
                    selectedProduct && selectedProduct.images && selectedProduct.images.length > 0
                      ? (typeof selectedProduct.images[activeImageIndex] === 'string' 
                          ? selectedProduct.images[activeImageIndex]
                          : (selectedProduct.images[activeImageIndex] as any)?.url || (selectedProduct.images[activeImageIndex] as any)?.image_url || selectedProduct.image_url || galleryImages[0]?.original || '')
                      : (template.images && template.images[activeImageIndex]
                          ? (typeof template.images[activeImageIndex] === 'string'
                              ? template.images[activeImageIndex]
                              : (template.images[activeImageIndex] as any)?.url || (template.images[activeImageIndex] as any)?.image_url)
                           : '')
                  }
                  alt={`${selectedProduct?.name || template.title} - 图 ${activeImageIndex + 1}`}
                  className="w-full h-full object-contain"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  transition={{ duration: 0.3 }}
                />
              </AnimatePresence>
              
              {/* 图片翻页按钮 */}
              {((selectedProduct && selectedProduct.images && selectedProduct.images.length > 1) || 
                 (template.images && template.images.length > 1)) && (
                <>
                  <button
                    onClick={() => {
                      const imagesCount = selectedProduct && selectedProduct.images && selectedProduct.images.length > 0
                        ? selectedProduct.images.length
                        : template.images ? template.images.length : 0;
                      setActiveImageIndex(prev => (prev === 0 ? imagesCount - 1 : prev - 1));
                    }}
                    className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-75 rounded-full p-2 hover:bg-opacity-100 transition-all focus:outline-none"
                  >
                    <svg className="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                    </svg>
                  </button>
                  <button
                    onClick={() => {
                      const imagesCount = selectedProduct && selectedProduct.images && selectedProduct.images.length > 0
                        ? selectedProduct.images.length
                        : template.images ? template.images.length : 0;
                      setActiveImageIndex(prev => (prev === imagesCount - 1 ? 0 : prev + 1));
                    }}
                    className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-75 rounded-full p-2 hover:bg-opacity-100 transition-all focus:outline-none"
                  >
                    <svg className="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </>
              )}
            </div>
            
            {/* 缩略图列表 */}
            {((selectedProduct && selectedProduct.images && selectedProduct.images.length > 1) || 
              (template.images && template.images.length > 1)) && (
              <div className="flex space-x-2 overflow-x-auto pb-2">
                {(selectedProduct && selectedProduct.images && selectedProduct.images.length > 0
                  ? selectedProduct.images
                  : template.images || []).map((image, idx) => (
                  <button
                    key={idx} // Use index as key, safer given potential type inconsistency
                    onClick={() => handleImageChange(idx)}
                    className={`relative flex-shrink-0 h-16 w-16 rounded-md overflow-hidden border-2 transition-all ${
                      activeImageIndex === idx ? 'border-blue-500' : 'border-transparent hover:border-gray-300'
                    }`}
                  >
                    <img
                      src={typeof image === 'string' 
                        ? image 
                        // Handle object case safely, provide fallback
                        : (typeof image === 'object' && image !== null ? (image.thumbnail || image.url || (image as any).image_url) : '') || 'https://via.placeholder.com/150x100?text=No+Image'}
                      alt={`${selectedProduct?.name || template.title} - 缩略图 ${idx + 1}`}
                      className="w-full h-full object-cover"
                    />
                  </button>
                ))}
              </div>
            )}
          </div>
          
          {/* 右侧商品信息和选择区域 */}
          <div className="md:w-1/2 px-4" ref={parametersRef}>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">{template.title}</h1>
            
            {template.category && (
              <Link to={`/products?category=${template.category.name}`} className="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
                {template.category.name}
              </Link>
            )}
            
            {/* Promo Page Link Button */}
            {template.promo_page_url && (
              <motion.div 
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2 }}
                className="mb-6"
              >
                <Link 
                  to={template.promo_page_url} 
                  className="inline-flex items-center px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out transform hover:scale-105"
                >
                  <svg className="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clipRule="evenodd"></path></svg>
                  Explore Special Features
                </Link>
              </motion.div>
            )}
            
            {selectedProduct && (
              <div className="mb-6">
                <div className="flex items-baseline mb-2">
                  <span className="text-2xl font-bold text-blue-600">${(selectedProduct.price || 0).toFixed(2)}</span>
                  {selectedProduct.original_price && selectedProduct.original_price > selectedProduct.price ? (
                    <span className="ml-2 text-lg line-through text-gray-500">
                      ${selectedProduct.original_price.toFixed(2)}
                    </span>
                  ) : null}
                  {selectedProduct.discount_percentage && selectedProduct.discount_percentage > 0 ? (
                    <span className="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                      -{selectedProduct.discount_percentage}%
                    </span>
                  ) : null}
                  
                  <div className="ml-auto">
                    {(selectedProduct.stock_quantity || 0) > 0 ? (
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Stock: {selectedProduct.stock_quantity} units
                      </span>
                    ) : (
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Out of stock
                      </span>
                    )}
                  </div>
                </div>
                <p className="text-sm text-gray-500">SKU: {selectedProduct.sku}</p>
                
                {/* 显示产品参数组信息 */}
                {selectedProduct.parameter_group && (
                  <div className="mt-2 text-sm">
                    <span className="text-gray-600">Configuration: </span>
                    <span className="font-medium text-gray-800">{selectedProduct.parameter_group}</span>
                  </div>
                )}
              </div>
            )}
            
            {/* 参数选择区域 */}
            <div className="border-t border-b border-gray-200 py-6 space-y-6">
              {/* Check if parameters is an object and has keys */}
              {template.parameters && typeof template.parameters === 'object' && Object.keys(template.parameters).length > 0 &&
                Object.entries(template.parameters).map(([paramName, paramValues]) => (
                  // Ensure paramValues is an array before rendering selector
                  Array.isArray(paramValues) && (
                    <div key={paramName} className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                        {paramName} {/* Use the key as the name */}
                  </label>
                      {/* Pass name and values array to the selector */}
                      {renderParameterSelector({ name: paramName, values: paramValues })}
                </div>
                  )
              ))}
              
              {/* 数量选择 */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Quantity
                </label>
                <div className="flex items-center">
                  <button 
                    onClick={decreaseQuantity}
                    className="rounded-l-md border border-gray-300 h-10 w-10 flex items-center justify-center bg-gray-50 hover:bg-gray-100"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
                    </svg>
                  </button>
                  <input 
                    type="number" 
                    value={quantity}
                    onChange={handleQuantityChange}
                    min="1"
                    className="h-10 w-14 border-t border-b border-gray-300 text-center focus:outline-none focus:ring-0 focus:border-gray-300"
                  />
                  <button 
                    onClick={increaseQuantity}
                    className="rounded-r-md border border-gray-300 h-10 w-10 flex items-center justify-center bg-gray-50 hover:bg-gray-100"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4m0 0l4 4m-4-4l4-4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            
            {/* 操作按钮 */}
            <div className="mt-6 flex space-x-4">
              <div className="relative flex-1">
              <button
                onClick={handleAddToCart}
                  disabled={!selectedProduct || (selectedProduct?.stock_quantity ?? 0) <= 0 || isAddingToCart}
                  className="w-full bg-blue-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-blue-300 disabled:cursor-not-allowed transition-colors"
                >
                  {isAddingToCart ? (
                    <svg className="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  ) : (
                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  )}
                  {isAddingToCart ? 'Adding...' : 'Add to Cart'}
              </button>
              <button
                  onClick={() => { setShowCartDropdown(!showCartDropdown); if(!showCartDropdown) loadCarts(); }}
                disabled={!selectedProduct || (selectedProduct?.stock_quantity ?? 0) <= 0}
                  className="absolute inset-y-0 right-0 flex items-center px-2 rounded-r-md bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <svg className="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                
                {/* 购物车下拉菜单 */}
                {showCartDropdown && (
                  <div className="absolute z-10 right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                    <div className="py-1 max-h-60 overflow-auto">
                      <div className="px-4 py-2 text-sm text-gray-700 font-medium border-b border-gray-200">
                        Select a Cart
                      </div>
                      
                      {loadingCarts ? (
                        <div className="px-4 py-3 text-sm text-gray-500 italic">
                          Loading carts...
                        </div>
                      ) : availableCarts.length === 0 ? (
                        <div className="px-4 py-3 text-sm text-gray-500 italic">
                          No carts available
                        </div>
                      ) : (
                        <>
                          {availableCarts.map(cart => (
                            <button
                              key={cart.id}
                              className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center justify-between"
                              onClick={() => handleAddToSpecificCart(cart.id)}
                            >
                              <span className="flex items-center">
                                <svg className="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  {cart.type === 'wishlist' ? (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                  ) : (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                  )}
                                </svg>
                                {cart.name}
                              </span>
                              {cart.is_default && (
                                <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                  Default
                                </span>
                              )}
                            </button>
                          ))}
                          
                          <div className="border-t border-gray-100 my-1"></div>
                          
                          <button
                            className="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 flex items-center"
                            onClick={() => {
                              setShowCreateCartModal(true);
                              setShowCartDropdown(false);
                            }}
                          >
                            <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create New Cart
                          </button>
                        </>
                      )}
                    </div>
                  </div>
                )}
              </div>
              <button 
                onClick={handleBuyNowClick} 
                disabled={!selectedProduct || (selectedProduct?.stock_quantity ?? 0) <= 0 || isAddingToCart || isBuyNowLoading}
                className="px-6 py-3 border border-green-600 text-green-600 font-semibold rounded-md hover:bg-green-50 transition-colors flex-1 flex items-center justify-center disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-not-allowed"
              >
                {isBuyNowLoading ? (
                    <svg className="animate-spin h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                ) : (
                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                )}
                {isBuyNowLoading ? 'Processing...' : 'Buy Now'}
              </button>
            </div>
            
            {/* 其他信息 */}
            <div className="mt-6">
              <div className="flex items-center space-x-4 divide-x divide-gray-200">
                <div className="flex items-center text-sm text-gray-600">
                  <svg className="h-5 w-5 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  Free Shipping
                </div>
                <div className="flex items-center text-sm text-gray-600 pl-4">
                  <svg className="h-5 w-5 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                  </svg>
                  30-day Return Policy
                </div>
              </div>
            </div>
          </div>
        </div>
        
        {/* 商品详情标签页 */}
        <div className="mt-16">
          <div className="border-b border-gray-200">
            <nav className="flex -mb-px">
              <button
                onClick={() => setActiveTab('description')}
                className={`mr-6 py-4 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'description'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Product Description
              </button>
              <button
                onClick={() => setActiveTab('specifications')}
                className={`mr-6 py-4 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'specifications'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Specifications
              </button>
              <button
                onClick={() => setActiveTab('reviews')}
                className={`py-4 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'reviews'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Reviews
              </button>
            </nav>
          </div>
          
          <div className="py-6">
            {activeTab === 'description' && (
              <div className="prose max-w-none">
                <div dangerouslySetInnerHTML={{ __html: template.description || '暂无描述' }} />
              </div>
            )}
            
            {activeTab === 'specifications' && (
              <div className="bg-white rounded-md shadow-sm overflow-hidden">
                <dl>
                  {/* 只保留product technical specifications */}
                  {selectedProduct && selectedProduct.parameters && Object.keys(selectedProduct.parameters).length > 0 && (
                    <div className="px-4 py-4 bg-white">
                      <h3 className="text-base font-semibold text-gray-900 pb-3 border-b border-gray-200">
                        Product Technical Specifications
                      </h3>
                      
                      <div className="mt-3 grid sm:grid-cols-3 sm:gap-4">
                        {Object.entries(selectedProduct.parameters).map(([key, value], idx) => {
                          // 格式化参数键名，去除后缀ID和特殊字符
                          const formattedKey = key
                            .replace(/[-_][0-9a-f]{12,}$/, '') // 移除后缀ID哈希
                            .split(/[-_]/)
                            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(' ');
                            
                          return (
                            <React.Fragment key={key}>
                              <dt className={`text-sm font-medium text-gray-700 py-2 ${idx % 2 === 0 ? 'bg-gray-50' : ''}`}>
                                {formattedKey}
                              </dt>
                              <dd className={`text-sm text-gray-900 py-2 sm:col-span-2 ${idx % 2 === 0 ? 'bg-gray-50' : ''}`}>
                                <span className="font-medium">{value}</span>
                              </dd>
                            </React.Fragment>
                          );
                        })}
                      </div>
                    </div>
                  )}
                  
                  {/* 提示信息，当没有参数时显示 */}
                  {(!selectedProduct || !selectedProduct.parameters || Object.keys(selectedProduct.parameters || {}).length === 0) && (
                    <div className="px-4 py-12 text-center">
                      <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <h3 className="mt-2 text-sm font-medium text-gray-900">No Technical Specifications</h3>
                      <p className="mt-1 text-sm text-gray-500">This product does not have any technical specifications.</p>
                    </div>
                  )}
                </dl>
              </div>
            )}
            
            {activeTab === 'reviews' && (
              <div className="space-y-6">
                <div className="flex items-center justify-center p-12 bg-gray-50 rounded-lg">
                  <div className="text-center">
                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h3 className="mt-2 text-sm font-medium text-gray-900">No Reviews Yet</h3>
                    <p className="mt-1 text-sm text-gray-500">This product template does not have any reviews yet</p>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
        
        {/* Related Templates Section */}
        {loadingRelated ? (
          <div className="mt-16">
            <h2 className="text-2xl font-bold text-gray-900 mb-8">Related Templates</h2>
            <div className="flex justify-center py-12">
              <LoadingSpinner size="medium" />
              <p className="ml-3 text-gray-600">Loading related templates...</p>
            </div>
          </div>
        ) : relatedTemplates.length > 0 ? (
          <div className="mt-16">
            <h2 className="text-2xl font-bold text-gray-900 mb-8">Related Templates</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {relatedTemplates.map((template) => (
                <motion.div
                  key={template.id}
                  className="group relative"
                  whileHover={{ y: -8 }}
                  transition={{ type: 'spring', stiffness: 300 }}
                >
                  <div className="w-full h-56 bg-gray-200 rounded-lg overflow-hidden">
                    <img
                      src={template.images && template.images.length > 0 
                          ? (typeof template.images[0] === 'string' 
                             ? template.images[0] 
                             : (template.images[0] as any)?.url || (template.images[0] as any)?.image_url || '') 
                          : ''}
                      alt={template.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                  </div>
                  <div className="mt-4">
                    <h3 className="text-sm text-gray-700">
                      <Link to={`/templates/${template.id}`}>
                        <span aria-hidden="true" className="absolute inset-0" />
                        {template.title}
                      </Link>
                    </h3>
                    <p className="mt-1 text-sm text-gray-500">
                      {template.description && template.description.substring(0, 60)}
                      {template.description && template.description.length > 60 ? '...' : ''}
                    </p>
                    <p className="mt-1 font-medium text-gray-900">
                      {template.price ? `$${template.price.toFixed(2)}` : 'Custom Pricing'}
                    </p>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
        ) : null}
      </div>
      
      {/* Related templates */}
      {relatedTemplates && relatedTemplates.length > 0 && (
        <div className="mt-16">
          <h2 className="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {relatedTemplates.map((relatedTemplate: ProductTemplate) => (
              <div key={relatedTemplate.id} className="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                <Link to={`/template/${relatedTemplate.id}`} className="block">
                  <div className="aspect-w-3 aspect-h-4">
                    <img
                      src={(relatedTemplate.images && relatedTemplate.images.length > 0 ? (typeof relatedTemplate.images[0] === 'string' ? relatedTemplate.images[0] : (relatedTemplate.images[0] as any)?.thumbnail || (relatedTemplate.images[0] as any)?.url || (relatedTemplate.images[0] as any)?.image_url) : null) || relatedTemplate.image_url || relatedTemplate.main_image || relatedTemplate.image || 'https://via.placeholder.com/300x400?text=No+Image'}
                      alt={relatedTemplate.title}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <div className="p-4">
                    <h3 className="text-lg font-semibold text-gray-900 mb-1">{relatedTemplate.title}</h3>
                    <p className="text-sm text-gray-500 mb-2">{relatedTemplate.category?.name}</p>
                    <div className="flex items-baseline">
                      <span className="text-lg font-bold text-blue-600">${(relatedTemplate.price ?? 0).toFixed(2)}</span>
                      {/* original_price might not exist on ProductTemplate, handle safely */}
                      {(relatedTemplate as any).original_price && (relatedTemplate as any).original_price > (relatedTemplate.price ?? 0) && (
                        <span className="ml-2 text-sm line-through text-gray-500">
                          ${((relatedTemplate as any).original_price ?? 0).toFixed(2)}
                        </span>
                      )}
                    </div>
                  </div>
                </Link>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Debug information in development environment */}
      {process.env.NODE_ENV === 'development' && <DebugInfo />}

      {/* Create Cart Modal */}
      <CreateCartModal 
        isOpen={showCreateCartModal} 
        onClose={() => setShowCreateCartModal(false)} 
        onSubmit={handleCreateCartSubmit} 
      />
    </>
  );
};

export default TemplateDetailPage; 