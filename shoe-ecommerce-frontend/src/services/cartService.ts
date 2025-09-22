import { api } from './api';
import { toast } from 'react-toastify';
import { store } from '../store';
import { exitCurrentCart } from '../store/slices/cartSlice';

// Create cart cache with 3 minutes expiration
const CACHE_TTL = 3 * 60 * 1000; // 3 minutes in milliseconds
const cartCache = {
  data: null as any,
  get: function(key: string) {
    return this.data;
  },
  set: function(key: string, value: any) {
    this.data = value;
  },
  delete: function(key: string) {
    this.data = null;
  }
};

// 产品模板缓存，减少重复API请求
const productTemplateCache = new Map<number, any>();

// Check if cache is valid
const isCacheValid = () => {
  return cartCache.get('cart') && (Date.now() - cartCache.get('cart').timestamp < CACHE_TTL);
};

/**
 * 根据产品ID获取产品的模板信息
 * @param productId 产品ID
 */
const getProductTemplateInfo = async (productId: number) => {
  try {
    // 检查缓存中是否有此产品
    if (productTemplateCache.has(productId)) {
      return productTemplateCache.get(productId);
    }
    
    // 获取产品详情 - 修复 API 访问方式
    const product = await api.get(`/products/${productId}`).then(res => res.data?.data);
    console.log(`获取到产品详情:`, product);
    
    if (!product) {
      return null;
    }
    
    // 如果有模板ID，获取模板信息
    if (product.template_id) {
      try {
        // 修复 API 访问方式
        const template = await api.get(`/templates/${product.template_id}`).then(res => res.data?.data);
        if (template) {
          // 添加模板信息到产品
          product.template_name = template.title || template.name;
          console.log(`获取到模板信息:`, template);
        }
      } catch (error) {
        console.error(`Failed to get template for product ${productId}:`, error);
      }
    }
    
    // 保存到缓存
    productTemplateCache.set(productId, product);
    return product;
  } catch (error) {
    console.error(`获取产品${productId}模板信息失败:`, error);
    return null;
  }
};

/**
 * 增强购物车项，添加模板信息
 * @param item 购物车项
 * @returns 增强后的购物车项
 */
export const enhanceCartItem = async (item: {
  id: number;
  product_id: number;
  quantity: number;
  price?: number;
  specifications?: Record<string, string>;
  product?: any;
  [key: string]: any;
}) => {
  try {
    // 如果已有product信息且完整，直接返回
    if (item.product && item.product.id !== undefined && 
        item.product.name && 
        (item.product.template_id !== undefined || item.product.template_name)) {
      console.log(`购物车项 #${item.id} 已有完整产品信息，跳过增强`, item.product);
      return item;
    }
    
    console.log(`开始增强购物车项 #${item.id} 的产品信息...`);
    
    // 获取产品详情，包括模板信息
    const productDetails = await getProductTemplateInfo(item.product_id);
    
    if (productDetails) {
      console.log(`已获取产品 #${item.product_id} 详情:`, productDetails);
      
      // 创建完整的product属性，包括模板信息
      return {
        ...item,
        // 确保保留原始价格
        price: item.price,
        // 添加或增强product属性
        product: {
          id: productDetails.id,
          name: productDetails.name,
          template_id: productDetails.template_id,
          template_name: productDetails.template_name || null,
          selling_price: productDetails.selling_price || productDetails.price || item.price,
          images: productDetails.images || []
        }
      };
    } else {
      console.warn(`无法获取产品 #${item.product_id} 详情`);
    }
  } catch (error) {
    console.error(`增强购物车项${item.id}失败:`, error);
  }
  
  // 如果获取失败，返回原始项
  return item;
};

/**
 * Process cart data to make sure each item has price
 * @param cartData The cart data from API
 */
const processCartData = async (cartData: any) => {
  if (!cartData) return cartData;
  
  console.log('正在处理购物车数据:', cartData);
  
  // Process individual cart
  const processCart = async (cart: {
    id: number;
    name?: string;
    type?: string;
    is_default?: boolean;
    items?: Array<{
      id: number;
      product_id: number;
      quantity: number;
      price?: number;
      specifications?: Record<string, string>;
      product?: any;
      [key: string]: any;
    }>;
    [key: string]: any;
  }) => {
    if (!cart) return cart;
    
    console.log(`处理购物车 ${cart.id}:`, cart);
    
    // 处理购物车中的每个项目，增强它们的product属性
    let enhancedItems: Array<{
      id: number;
      product_id: number;
      quantity: number;
      price?: number;
      specifications?: Record<string, string>;
      product?: any;
      [key: string]: any;
    }> = [];
    
    if (Array.isArray(cart.items)) {
      // 并行处理所有购物车项
      enhancedItems = await Promise.all(cart.items.map(item => enhanceCartItem(item)));
    }
    
    return {
      ...cart,
      items: enhancedItems
    };
  };
  
  // If data has multiple carts
  if (cartData.data && Array.isArray(cartData.data.carts)) {
    console.log('处理多个购物车...');
    // 并行处理所有购物车
    const enhancedCarts = await Promise.all(cartData.data.carts.map(processCart));
    
    return {
      ...cartData,
      data: {
        ...cartData.data,
        carts: enhancedCarts
      }
    };
  }
  
  // If data is a single cart
  return processCart(cartData);
};

/**
 * Get cart data
 * @param params Query parameters
 * @param force Force refresh cache
 */
export const getCart = async (params: {
  cart_id?: number | string;
  type?: string;
} = {}, force = false) => {
  try {
    console.log('获取购物车数据，参数:', { params, force });

    const queryParams = params || {};
    const cleanParams: Record<string, any> = {};
    
    if (queryParams.cart_id !== undefined && queryParams.cart_id !== null) {
      cleanParams.cart_id = queryParams.cart_id;
    }
    
    if (queryParams.type !== undefined && queryParams.type !== null) {
      cleanParams.type = queryParams.type;
    }
    
    // 缓存逻辑可以保持不变，但需要确保缓存的是 response.data
    // if (!force && isCacheValid()) { 
    //   return cartCache.get('cart'); // Assuming cache stores the data part
    // }
    
    console.log('从API获取购物车数据，使用参数:', cleanParams);
    const response = await api.get('/cart', { params: cleanParams });
    
    // --- 修改开始 ---
    // 检查 Axios 响应状态是否成功 (例如 2xx)
    if (response.status >= 200 && response.status < 300 && response.data) {
        // 对 response.data 进行处理 (如增强商品信息)
        const processedData = await processCartData(response.data); // Pass response.data here
    
        // 缓存处理后的数据
        // cartCache.set('cart', {
        //  ...processedData, // Store the processed API data
        //    timestamp: Date.now()
        // });
    
        // 直接返回处理后的 API 数据部分
    return processedData;
    } else {
        // 如果 Axios 请求本身失败或返回的数据无效
        console.error('API request failed or returned invalid data:', response);
        throw new Error(response.data?.message || `请求失败，状态码: ${response.status}`);
    }
    // --- 修改结束 ---

  } catch (error: any) {
    // 捕获 api.get 或 processCartData 抛出的错误
    console.error('Failed to get cart:', error);
    // 可以进一步处理 AxiosError
    if (error.isAxiosError) {
        console.error('Axios error details:', error.response?.data);
        throw new Error(error.response?.data?.message || error.message || '获取购物车时发生网络错误。');
    } else {
        // 其他错误 (例如 processCartData 内部错误)
    throw error;
    }
  }
};

/**
 * 创建默认购物车
 * @returns 新创建的购物车或现有的默认购物车
 */
const createDefaultCart = async () => {
  try {
    console.log('[CartService] 尝试创建默认购物车');
    // Use the imported 'api' instance directly with POST method
    const response = await api.post('/cart/create', {
      name: 'Default Cart', // Or fetch user-specific default name if needed
      type: 'default',
      is_default: true,
    });
    
    if (response.data?.success && response.data.data) {
      console.log('[CartService] 默认购物车创建成功:', response.data.data);
      // Update cache immediately
      const processedData = await processCartData(response.data); 
      cartCache.set('cart', {
        ...processedData,
        timestamp: Date.now()
      });
      return processedData.data; // Return the newly created cart data
    } else {
      console.error('[CartService] 创建默认购物车响应无效:', response.data);
      throw new Error(response.data?.message || '创建默认购物车失败');
    }
  } catch (error) {
    console.error('[CartService] 创建默认购物车失败:', error);
    throw error; // Re-throw the error to be caught by caller
  }
};

/**
 * Add product to cart - Function overloads
 */
// 对象参数版本的重载签名
export function addToCart(data: {
  product_id: number; 
  quantity: number; 
  cart_id?: number;
  cart_type?: string;
  cart_name?: string;
  size?: string; 
  color?: string; 
  template_id?: number;
  specifications?: Record<string, string> | null;
}): Promise<any>;

// 多参数版本的重载签名
export function addToCart(
  cartId: number,
  product: any,
  quantity?: number,
  specifications?: Record<string, string> | null
): Promise<any>;

// 具体实现
export async function addToCart(
  cartIdOrData: number | {
    product_id: number;
    quantity: number;
  cart_id?: number; 
  cart_type?: string; 
    cart_name?: string;
    size?: string;
    color?: string;
    template_id?: number;
    specifications?: Record<string, string> | null;
  },
  product?: any,
  quantity: number = 1,
  specifications: Record<string, string> | null = null
): Promise<any> {
  try {
    // 处理不同的调用方式
    let targetCartId: number | null = null;
    let productId: number;
    let productData: {
      product_id: number;
      quantity: number;
      cart_id: number | null;
      cart_type?: string;
      cart_name?: string;
      specifications: Record<string, string> | null;
      template_id?: number;
      parameter_group?: string;
      color?: string;
      size?: string;
    };
    
    // 检查是对象形式还是多参数形式
    if (typeof cartIdOrData === 'object') {
      // 对象参数版本
      const data = cartIdOrData;
      
      // 首先记录原始参数，便于调试
      console.log('[addToCart] 原始参数对象:', {
        product_id: data.product_id,
        quantity: data.quantity,
        size: data.size,
        color: data.color,
        template_id: data.template_id,
        specifications: data.specifications
      });
      
      // 确保product_id是数字类型
      const safeProductId = parseInt(String(data.product_id), 10);
      if (isNaN(safeProductId)) {
        throw new Error(`无效的产品ID: ${data.product_id}`);
      }
      
      // If cart_id is not provided or is explicitly null/undefined, use null.
      // Otherwise, use the provided cart_id.
      // Do not default to 0 as it's an invalid ID.
      targetCartId = (data.cart_id === null || data.cart_id === undefined) ? null : Number(data.cart_id);
      productId = safeProductId;
      
      // 构建规格对象
      const specs: Record<string, string> = {};
      
      // 处理单独的color和size参数
      if (data.color !== undefined && data.color !== null) {
        specs['color'] = String(data.color).trim();
        console.log('[addToCart] 已添加颜色规格:', data.color);
      }
      
      if (data.size !== undefined && data.size !== null) {
        specs['size'] = String(data.size).trim();
        console.log('[addToCart] 已添加尺寸规格:', data.size);
      }
      
      // 如果已有specifications，合并已有的规格
      if (data.specifications) {
        Object.entries(data.specifications).forEach(([key, value]) => {
          if (value !== null && value !== undefined) {
            specs[key.trim()] = String(value).trim();
          }
        });
      }
      
      console.log('[addToCart] 最终规格对象:', specs);
      
      productData = {
        product_id: safeProductId, // 使用安全处理过的产品ID
        quantity: data.quantity,
        cart_id: targetCartId, // Use the potentially null cart_id
        specifications: Object.keys(specs).length > 0 ? specs : null,
        // 确保color和size参数同时也作为顶级参数传递
        color: specs['color'],
        size: specs['size']
      };
      
      // 如果有模板ID，添加到数据中
      if (data.template_id) {
        productData.template_id = data.template_id;
      }
      
      // 可选的cart_type和cart_name
      if (data.cart_type) {
        productData.cart_type = data.cart_type;
      }
      
      if (data.cart_name) {
        productData.cart_name = data.cart_name;
      }
      
      console.log('[addToCart] 处理后的添加购物车参数:', productData);
    } else {
      // 多参数版本
      console.log('[addToCart] 使用多参数调用方式');
      
      // 确保product对象存在
      if (!product || !product.id) {
        throw new Error('无效的产品对象');
      }
      
      // 检查product是否包含模板ID信息
      let templateId = null;
      
      if (product.template_id) {
        templateId = product.template_id;
      }
      
      // 从specifications中提取template_id(如果有的话)
      let cleanedSpecifications = { ...specifications };
      if (specifications && 'template_id' in specifications) {
        templateId = specifications.template_id;
        delete cleanedSpecifications.template_id;
      }
      
      targetCartId = cartIdOrData as number;
      productId = product.id;
      
      console.log('[addToCart] 多参数方式信息:', {
        cartId: targetCartId, 
        productId: product.id,
        productName: product.name,
        quantity,
        template_id: templateId,
        specifications: cleanedSpecifications
      });
      
      productData = {
        product_id: product.id,
        quantity: quantity,
        cart_id: targetCartId, // Use the potentially null cart_id
        specifications: cleanedSpecifications,
      };
      
      // 如果有模板ID，添加到参数中
      if (templateId !== null) {
        productData.template_id = templateId;
      }
    }
    
    // 最后再次确认product_id正确设置
    console.log('[addToCart] 最终确认product_id:', productData.product_id);
    
    const cartPayload = {
      product_id: productData.product_id,
      quantity: productData.quantity,
      cart_id: productData.cart_id, // Pass null if it's null
      cart_type: productData.cart_type,
      cart_name: productData.cart_name,
      size: productData.size,
      color: productData.color,
      template_id: productData.template_id,
      specifications: productData.specifications,
    };
    
    console.log('[addToCart] 最终发送的 Payload:', cartPayload);

    try {
      // Use the imported 'api' instance directly with POST method
      const response = await api.post('/cart', cartPayload);
      
      console.log('[addToCart] 添加到购物车API响应:', response.data);
    
      if (response.data?.success) {
    // 成功后清除购物车缓存
    cartCache.delete('cart');
    
        return response.data;
      } else {
        throw new Error(response.data?.message || '添加到购物车失败');
      }
    } catch (error) {
      console.error('[addToCart] 添加到购物车失败:', error);
      throw error;
    }
  } catch (error) {
    console.error('[addToCart] 添加购物车失败:', error);
    throw error;
  }
}

/**
 * Update cart item
 * @param id Cart item ID
 * @param data Update data
 */
export const updateCartItem = async (id: number, data: { quantity: number }) => {
  try {
    console.log(`[CartService] 更新购物车项 #${id} 数量为 ${data.quantity}`);
    // Use api.put
    const response = await api.put(`/cart/${id}`, data);
    
    if (response.data?.success) {
      // Invalidate cache on update
          getCart({}, true).catch(err => console.error('刷新购物车缓存失败:', err));
          
          return response.data;
        } else {
      throw new Error(response.data?.message || '更新购物车失败');
        }
  } catch (error: any) {
    console.error('准备更新购物车项时出错:', error);
    throw error;
  }
};

/**
 * Remove item from cart
 * @param id Cart item ID
 */
export const removeFromCart = async (id: number) => {
  try {
    console.log(`[CartService] 从购物车移除项 #${id}`);
    // Use api.delete
    const response = await api.delete(`/cart/${id}`);
    
    if (response.data?.success) {
      // Invalidate cache on removal
          getCart({}, true).catch(err => console.error('刷新购物车缓存失败:', err));
          
          return response.data;
        } else {
      throw new Error(response.data?.message || '从购物车移除商品失败');
        }
  } catch (error: any) {
    console.error('准备从购物车移除商品时出错:', error);
    throw error;
  }
};

/**
 * Clear cart
 * @param cartId 可选的购物车ID。如果提供，则只清空指定购物车，否则清空当前活动购物车
 */
export const clearCart = async (cartId?: number) => {
  try {
    console.log('[CartService] 清空购物车', cartId ? `ID: ${cartId}` : '当前活动购物车');
    
    // 构建API请求URL，支持可选的购物车ID
    const url = cartId ? `/cart/clear/${cartId}` : '/cart/clear';
    
    // 使用 api.post 而不是 api.delete，因为我们需要传递参数
    const response = await api.post(url);
    
    if (response.data?.success) {
      // 清除缓存
    cartCache.set('cart', null);
    
      toast.success('购物车已成功清空');
      return response.data;
    } else {
      throw new Error(response.data?.message || '清空购物车失败');
    }
  } catch (error) {
    console.error('清空购物车失败:', error);
    toast.error('清空购物车失败');
    throw error;
  }
};

/**
 * Create a new cart
 * @param data Cart creation data (name, type, is_default)
 */
export const createCart = async (data: { name: string; type: string; is_default?: boolean }) => {
  try {
    console.log('[cartService] Creating new cart:', data);
    
    // Use the dedicated endpoint for creating a cart
    const response = await api.post('/cart/create', data);

    console.log('[cartService] Create cart response:', response);
    
    // Check the structure of the API response
    if (response.data?.success) {
      // Invalidate cache after creating a new cart
      clearCartCache(); 
      return response.data; // Return the entire successful response data
    } else {
      // Handle API error response (e.g., validation failure)
      const errorMessage = response.data?.message || 'Unknown error creating cart';
      console.error('[cartService] Failed to create cart (API error):', errorMessage, response.data);
      toast.error(`Failed to create cart: ${errorMessage}`);
      throw new Error(errorMessage);
    }
  } catch (error: any) {
    // Handle network or other unexpected errors
    console.error('[cartService] Failed to create new cart:', error);
    const errorMsg = error.response?.data?.message || error.message || 'Failed to create cart';
    // Check for validation errors specifically
    if (error.response?.data?.errors) {
      const validationErrors = Object.values(error.response.data.errors).flat().join(' ');
      toast.error(`Validation Error: ${validationErrors}`);
      throw new Error(`Validation Error: ${validationErrors}`);
    } else {
      toast.error(errorMsg);
      throw new Error(errorMsg);
    }
  }
};

/**
 * Apply coupon to cart
 * @param code Coupon code
 */
export const applyCoupon = async (code: string) => {
  try {
    console.log(`[CartService] 应用优惠券: ${code}`);
    // Use api.post
    const response = await api.post('/cart/apply-coupon', { code });
    
    if (response.data?.success) {
      // Invalidate or update cache with coupon info
      getCart({}, true).catch(err => console.error('刷新购物车缓存失败:', err));
    
      toast.success('优惠券应用成功');
      return response.data;
    } else {
      throw new Error(response.data?.message || '应用优惠券失败');
    }
  } catch (error: any) {
    console.error('Failed to apply coupon:', error);
    const errorMsg = error.response?.data?.message || 'Failed to apply coupon';
    toast.error(errorMsg);
    throw error;
  }
};

/**
 * Clear cart cache
 */
export const clearCartCache = () => {
  cartCache.set('cart', null);
};

/**
 * Exit current cart, return to default cart
 */
export const exitCart = () => {
  try {
    // 强制清除缓存，确保重新加载数据
    clearCartCache();
    
    // 触发Redux状态更新
    store.dispatch(exitCurrentCart());
    
    // 延迟一下再重新加载数据，确保状态已更新
    setTimeout(async () => {
      try {
        // 强制刷新购物车数据
        await getCart({}, true);
      } catch (error) {
        console.error('Failed to refresh cart data after exit:', error);
      }
    }, 300);
    
    return true;
  } catch (error) {
    console.error('Failed to exit cart:', error);
    toast.error('Error switching cart, please try again');
    
    // 即使出错，也尝试重新加载数据
    try {
      getCart({}, true);
    } catch (secondError) {
      console.error('Failed to refresh cart after error:', secondError);
    }
    
    return false;
  }
};

// Export cart service
const cartService = {
  getCart,
  addToCart,
  updateCartItem,
  removeFromCart,
  clearCart,
  createCart,
  applyCoupon,
  clearCartCache,
  exitCart
};

export default cartService; 