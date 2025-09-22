import React, { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Link, useNavigate } from 'react-router-dom';
import { Cart } from '../../store/slices/cartSlice';
import LoadingSpinner from '../LoadingSpinner';
import { CheckIcon, XMarkIcon as XIcon, TrashIcon, MinusIcon, PlusIcon, ShoppingCartIcon, ShoppingBagIcon, ExclamationCircleIcon, ImageIcon, ArrowLeftIcon, QuestionMarkCircleIcon, ReceiptPercentIcon, TruckIcon, CurrencyDollarIcon, TagIcon, CreditCardIcon, ChevronRightIcon } from '@heroicons/react/24/outline';
import { formatCurrency } from '../../utils/formatter';
import { toast } from 'react-toastify';
import { getCart, updateCartItem, removeFromCart } from '../../services/cartService';
import { apiService } from '../../services/api';
import { store } from '../../store';
import authService from '../../services/authService';
// 在顶部导入SkeletonLoader
import SkeletonLoader from '../animations/SkeletonLoader';
import ConfirmDialog from '../common/ConfirmDialog'; // Corrected import path

// 基本CartItem定义
interface CartItem {
  id: number;
  product_id: number;
  quantity: number;
  price: number;
  subtotal: number;
  name?: string;
  image?: string;
}

// 定义模板数据类型
interface TemplateData {
  id: number;
  name: string;
  description: string;
  category: {
    id: number;
    name: string;
  };
  parameters: Array<{
    name: string;
    values: string[];
  }>;
  images: Array<{
    id: number;
    url: string;
    thumbnail: string;
  }>;
  is_active: boolean;
  is_featured: boolean;
  is_new_arrival: boolean;
  is_sale: boolean;
  created_at: string;
  updated_at: string;
  linked_products: Array<{
    id: number;
    name: string;
    sku: string;
    price: number;
    original_price: number;
    discount_percentage: number;
    stock_quantity: number;
    images: Array<{
      id: number;
      url: string;
      thumbnail: string;
    }>;
    parameter_group: string;
    relation_type: string;
    parameters: Record<string, string> | any[];
  }>;
}

// 确保CartItem类型在本文件内部明确
type EnhancedCartItem = CartItem & {
  product?: {
    id: number;
    name: string;
    template_id?: number;
    template_name?: string;
    selling_price: number;
    images?: string | any[] | {
      id: number;
      url: string;
      thumbnail?: string;
    }[];
  };
  specifications?: Record<string, string>;
  images?: string[] | any[] | {
    id: number;
    url: string;
    thumbnail?: string;
  }[];
  image?: string;
  parameter_group?: string;
  sku?: string;
  price?: number;
  discount_percentage?: string | number;
  selling_price?: number;
  original_price?: number | null;
  template?: {
    id: number;
    name: string;
    category: any;
    parameters: any[];
  };
  isRemoving?: boolean; // 添加isRemoving属性标记正在删除的项目
};

// 定义与CartItem兼容的扩展类型
interface CartItemBase {
  id: number;
  product_id: number;
  quantity: number;
  price: number;
  subtotal: number;
}

// 扩展CartItem类型，使其可以包含template属性
interface ExtendedCartItem extends CartItemBase {
  name?: string;
  template?: {
    id: number;
    name: string;
    category: any;
    parameters: any[];
  };
  images?: any[];
  specifications?: Record<string, string>;
  product?: any;
}

// 特殊返回类型，用于在处理过程中传递缓存更新信息
interface CartItemWithCacheUpdates extends EnhancedCartItem {
  __cacheUpdates?: Record<number, TemplateData>;
}

interface CartDetailsProps {
  cart: Cart;
  onBackToList?: () => void;
  onQuantityChange: (id: number, quantity: number) => void;
  onRemoveItem: (id: number) => void;
  onClearCart: () => void;
  onCheckout: (selectedItems?: number[]) => void;
  updatingItemId?: number | null;
  cartLoading?: boolean;
  selectedItems: number[];
  onSelectItem: (itemId: number, isSelected: boolean) => void;
}

// 后端API基础URL - 使用当前域名，因为图片路径通过storage:link指向public/storage目录
const API_BASE_URL = ''; // 空字符串表示使用当前域名，无需指定完整URL

// 定义动画变体
const cardVariants = {
  hidden: { 
    opacity: 0,
    y: 20,
    scale: 0.98
  },
  visible: { 
    opacity: 1,
    y: 0,
    scale: 1,
    transition: {
      type: "spring",
      stiffness: 300,
      damping: 25
    }
  },
  exit: { 
    opacity: 0,
    y: -10,
    scale: 0.95,
    transition: { 
      duration: 0.3,
      ease: "easeIn"
    } 
  },
  hover: {
    y: -4,
    boxShadow: "0 8px 24px -6px rgba(0, 0, 0, 0.1)",
    transition: {
      duration: 0.3,
      ease: "easeOut"
    }
  }
};

// 更新按钮动画变体，使其更加轻盈和响应迅速
const buttonVariants = {
  tap: { scale: 0.96, transition: { duration: 0.1 } },
  hover: { scale: 1.04, transition: { duration: 0.2 } }
};

// 新增：添加价格动画变体
const priceVariants = {
  initial: { opacity: 0, y: 10 },
  animate: { 
    opacity: 1, 
    y: 0,
    transition: { 
      type: "spring", 
      stiffness: 300, 
      damping: 25 
    }
  },
  exit: { opacity: 0, y: -10 }
};

// 新增：添加微妙的背景动画变体
const bgPulseVariants = {
  initial: { backgroundColor: "rgba(243, 244, 246, 1)" },
  pulse: { 
    backgroundColor: ["rgba(243, 244, 246, 1)", "rgba(236, 253, 245, 1)", "rgba(243, 244, 246, 1)"],
    transition: { 
      duration: 2, 
      repeat: 0,
      ease: "easeInOut" 
    }
  }
};

// 扩展Cart类型，添加我们使用的额外属性
interface ExtendedCart extends Cart {
  total_items?: number;
  subtotal?: number;
  shipping?: number;
  tax?: number;
  discount?: number;
}

// 在组件函数上方添加一个类型断言辅助函数
const safeNumber = (value: any): number => {
  if (typeof value === 'number') {
    return value;
  }
  return 0;
};

// 定义一个组件外的函数，计算订单总价
const calculateOrderSummary = (items: EnhancedCartItem[], selectedItemIds: number[]) => {
  // 只计算选中项目的总价
  const selectedCartItems = items.filter(item => selectedItemIds.includes(item.id));

  const subtotal = selectedCartItems.reduce((sum, item) => {
    const getItemPrice = (item: EnhancedCartItem): number => {
      if (item.selling_price && Number(item.selling_price) > 0) {
        return Number(item.selling_price);
      }
      if (typeof item.price === 'number' && item.price > 0) {
        return item.price;
      }
      if (item.product?.selling_price) {
        return item.product.selling_price;
      }
      return 0;
    };
    const itemPrice = getItemPrice(item);
    return sum + (itemPrice * item.quantity);
  }, 0);
  
  // TODO: Add logic for tax, shipping, discounts if applicable for selected items
  const total = subtotal; // Placeholder

  return {
    subtotal,
    total,
    itemCount: selectedCartItems.length // Count only selected items
  };
};

const CartDetails: React.FC<CartDetailsProps> = ({
  cart,
  onBackToList,
  onQuantityChange: parentOnQuantityChange,
  onRemoveItem: parentOnRemoveItem,
  onClearCart: parentOnClearCart,
  onCheckout,
  updatingItemId,
  cartLoading = false,
  selectedItems,
  onSelectItem
}): React.ReactNode => {
  const [showZeroQuantityConfirm, setShowZeroQuantityConfirm] = useState(false);
  const [itemToCheckRemove, setItemToCheckRemove] = useState<number | null>(null);
  const [showClearCartConfirm, setShowClearCartConfirm] = useState(false);
  const [showCheckoutConfirm, setShowCheckoutConfirm] = useState(false);
  const [enhancedItems, setEnhancedItems] = useState<EnhancedCartItem[]>([]);
  const [isLoadingProducts, setIsLoadingProducts] = useState(false);
  const [templateCache, setTemplateCache] = useState<Record<number, TemplateData>>({});
  const [isUpdating, setIsUpdating] = useState(false); // Keep internal updating state
  const [localItems, setLocalItems] = useState<EnhancedCartItem[]>([]);
  const [localOrderSummary, setLocalOrderSummary] = useState({ subtotal: 0, total: 0, itemCount: 0 }); // Added itemCount
  const updateQueueRef = useRef<Record<number, { id: number; quantity: number; timer: NodeJS.Timeout | null; createdAt: number; }>>({});
  const [itemsBeingRemoved, setItemsBeingRemoved] = useState<Set<number>>(new Set());
  const selectAllRef = useRef<HTMLInputElement>(null); // Ref for Select All checkbox
  
  // Update local state, ensure order summary updates
  const updateLocalState = useCallback((id: number, quantity: number) => {
    console.log(`Immediately updating UI state: Item ID=${id}, new quantity=${quantity}`);
    
    setLocalItems(prev => {
      const updatedItems = prev.map(item => {
        if (item.id === id) {
          const price = typeof item.price === 'number' 
            ? item.price 
            : (item.product?.selling_price || 0);
          
          const updatedItem = {
            ...item,
            quantity,
            subtotal: price * quantity
          };
          
          return updatedItem;
        }
        return item;
      });
      
      // Recalculate summary based on *selected* items
      const summary = calculateOrderSummary(updatedItems, selectedItems);
      setLocalOrderSummary(summary);
      
      return updatedItems;
    });
  }, [selectedItems]); // Add selectedItems dependency
  
  // Remove local item, update summary
  const removeLocalItem = useCallback((id: number) => {
    // Remove from selected items using the prop callback
    onSelectItem(id, false); // Inform parent that this item is deselected
    
    setLocalItems(prev => {
      const updatedItems = prev.filter(item => item.id !== id);
      
      // Recalculate summary based on remaining selected items
      const remainingSelected = selectedItems.filter(itemId => itemId !== id);
      const summary = calculateOrderSummary(updatedItems, remainingSelected);
      setLocalOrderSummary(summary);
      
      return updatedItems;
    });
    
    toast.success('Item removed from cart');
  }, [onSelectItem, selectedItems]);
  
  // Cancel pending update
  const cancelPendingUpdate = useCallback((id: number) => {
    const pendingUpdate = updateQueueRef.current[id];
    if (pendingUpdate && pendingUpdate.timer) {
      clearTimeout(pendingUpdate.timer);
      delete updateQueueRef.current[id];
    }
  }, []);
  
  // Quantity change handler - calls parent prop
  const handleQuantityChange = useCallback((id: number, quantity: number) => {
    // Call the parent's handler (which might handle debouncing/API calls)
    parentOnQuantityChange(id, quantity);

    // Also update local state immediately for responsiveness
    // Find the item to get its price for subtotal recalculation
    const item = localItems.find(i => i.id === id);
    if (item) {
        const price = typeof item.price === 'number' 
            ? item.price 
            : (item.product?.selling_price || 0);
        
        setLocalItems(prev => prev.map(i => i.id === id ? { ...i, quantity, subtotal: price * quantity } : i));
    } 
    // Recalculate summary based on selected items after local quantity update
    const summary = calculateOrderSummary(localItems.map(i => i.id === id ? { ...i, quantity } : i), selectedItems);
    setLocalOrderSummary(summary);

  }, [parentOnQuantityChange, localItems, selectedItems]);

  // Remove item handler - calls parent prop
  const handleRemoveItem = useCallback((id: number, e?: React.MouseEvent) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    setItemsBeingRemoved(prev => new Set(prev).add(id));
    // Mark for animation
    setLocalItems(prev => prev.map(item => item.id === id ? { ...item, isRemoving: true } : item)); 
    // Delay the actual removal slightly for animation
    setTimeout(() => {
       parentOnRemoveItem(id); // Call parent's remove handler (handles API etc.)
       // Parent (CartPage) should update its state, which will flow back down
       // We don't need to call removeLocalItem here anymore as CartPage controls the data flow
      setItemsBeingRemoved(prev => {
        const newSet = new Set(prev);
        newSet.delete(id);
        return newSet;
      });
    }, 300); 
  }, [parentOnRemoveItem]);
  
  // Initialize local items when cart prop changes
  useEffect(() => {
    if (cart?.items) {
      setLocalItems(cart.items as EnhancedCartItem[]);
      // Calculate initial summary based on selected items (likely empty initially)
      const summary = calculateOrderSummary(cart.items as EnhancedCartItem[], selectedItems);
      setLocalOrderSummary(summary);
    } else {
      setLocalItems([]);
      setLocalOrderSummary({ subtotal: 0, total: 0, itemCount: 0 });
    }
  }, [cart, selectedItems]); // Depend on selectedItems too

  // Cleanup debounce timers on unmount
  useEffect(() => {
    return () => {
      Object.values(updateQueueRef.current).forEach(update => {
        if (update.timer) {
          clearTimeout(update.timer);
        }
      });
    };
  }, []);
  
  // Enhance a single cart item
  const enhanceCartItem = useCallback(async (item: ExtendedCartItem, tempCache: Record<number, TemplateData>): Promise<CartItemWithCacheUpdates> => {
    try {
      console.log(`开始增强购物车项 #${item.id}, 商品ID: ${item.product_id}, 规格:`, item.specifications);
      
      let enhancedItem: CartItemWithCacheUpdates = { ...item };
      
      if (item.template && item.template.name) {
        console.log(`购物车项 #${item.id} 已包含template对象:`, item.template);
        const newSpecifications = item.specifications 
          ? { ...item.specifications } 
          : {};
        if (!newSpecifications.template_name) {
          newSpecifications.template_name = item.template.name;
        }
        const newProduct = item.product 
          ? { ...item.product } 
          : {
              id: item.product_id,
              name: item.name || '',
              selling_price: item.price || 0,
              images: item.images || []
            };
        newProduct.template_id = item.template.id;
        newProduct.template_name = item.template.name;
        return {
          ...item,
          specifications: newSpecifications,
          product: newProduct
        };
      }
      
      let templateId: number | undefined = undefined;
      if (item.specifications && 'template_id' in item.specifications) {
        templateId = parseInt(item.specifications.template_id);
        console.log(`从规格中找到模板ID: ${templateId}`);
      }
      if (!templateId && item.product?.template_id) {
        templateId = item.product.template_id;
        console.log(`从product对象中找到模板ID: ${templateId}`);
      }
      
      if (item.product && item.product.name && 
          (item.product.template_name || (templateId && tempCache[templateId]?.name))) {
        console.log(`购物车项 #${item.id} 已有完整信息，无需增强`);
        if (templateId && tempCache[templateId]?.name) {
          const templateName = tempCache[templateId].name;
          return {
            ...item,
            specifications: {
              ...(item.specifications || {}),
              template_name: templateName
            },
            product: {
              ...(item.product || {}),
              template_name: templateName
            }
          };
        }
        if ('images' in item && Array.isArray(item.images) && item.images.length > 0 && 
            (!item.product.images || (Array.isArray(item.product.images) && item.product.images.length === 0))) {
          return {
            ...item,
            product: {
              ...(item.product || {}),
              images: [...item.images]
            }
          };
        }
        return { ...item };
      }
      
      if (templateId && templateId > 0) {
        if (tempCache[templateId]) {
          console.log(`使用缓存的模板数据: ${templateId}`);
          const cachedTemplate = tempCache[templateId];
          return {
            ...item,
            template: {
              id: cachedTemplate.id,
              name: cachedTemplate.name,
              category: cachedTemplate.category,
              parameters: cachedTemplate.parameters
            },
            specifications: {
              ...(item.specifications || {}),
              template_name: cachedTemplate.name
            },
            product: {
              ...(item.product || {}),
              template_id: templateId,
              template_name: cachedTemplate.name,
              name: item.product?.name || cachedTemplate.name,
              images: item.product?.images || cachedTemplate.images
            }
          };
        }
        
        try {
          const response = await apiService.templates.getById(templateId);
          if (response.success && response.data) {
            console.log(`获取模板数据成功: ${templateId}`);
            const template = response.data;
            const newTemplateCache = {
              ...tempCache,
              [templateId]: template
            };
            const result: CartItemWithCacheUpdates = {
              ...item,
              template: {
                id: template.id,
                name: template.name,
                category: template.category,
                parameters: template.parameters
              },
              specifications: {
                ...(item.specifications || {}),
                template_name: template.name
              },
              product: {
                ...(item.product || {}),
                template_id: templateId,
                template_name: template.name,
                name: item.product?.name || template.name,
                images: item.product?.images || template.images
              },
              __cacheUpdates: newTemplateCache
            };
            return result;
          }
        } catch (error) {
          console.error(`获取模板数据失败: ${templateId}`, error);
        }
      }
      
      return { ...item };
    } catch (error) {
      console.error(`增强购物车项失败: ${item.id}`, error);
      return { ...item };
    }
  }, []);
  
  // Enhance all cart items
  useEffect(() => {
    if (!cart || !cart.items || cart.items.length === 0) {
      setEnhancedItems([]);
      return;
    }
    let isMounted = true;
    const enhanceCartItems = async () => {
      setIsLoadingProducts(true);
      try {
        const tempCache = { ...templateCache };
        let cacheUpdated = false;
        const enhancedItemsPromises = cart.items.map(item => 
          enhanceCartItem(item as ExtendedCartItem, tempCache)
        );
        const results = await Promise.all(enhancedItemsPromises);
        results.forEach(result => {
          if ('__cacheUpdates' in result && result.__cacheUpdates) {
            Object.assign(tempCache, result.__cacheUpdates);
            cacheUpdated = true;
            delete result.__cacheUpdates;
          }
        });
        if (!isMounted) return;
        if (cacheUpdated) {
          setTemplateCache(tempCache);
        }
        setEnhancedItems(results);
      } catch (error) {
        console.error('增强购物车项失败:', error);
        if (!isMounted) return;
        setEnhancedItems(cart.items as EnhancedCartItem[]);
      } finally {
        if (isMounted) {
          setIsLoadingProducts(false);
        }
      }
    };
    enhanceCartItems();
    return () => {
      isMounted = false;
    };
  }, [cart, enhanceCartItem, templateCache]);
  
  // Calculate Select All checkbox state
  useEffect(() => {
    const numItems = localItems.length;
    const numSelected = selectedItems.length;
    const checkbox = selectAllRef.current;
    if (checkbox) {
        if (numItems > 0 && numSelected === numItems) {
            checkbox.checked = true;
            checkbox.indeterminate = false;
        } else if (numSelected > 0) {
            checkbox.checked = false;
            checkbox.indeterminate = true;
    } else {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        }
    }
  }, [selectedItems, localItems]);

  // Handle Select All change
  const handleSelectAll = (event: React.ChangeEvent<HTMLInputElement>) => {
    const isChecked = event.target.checked;
    const allItemIds = localItems.map(item => item.id);
    if (isChecked) {
        // Select all - inform parent for each item
        allItemIds.forEach(id => {
             if (!selectedItems.includes(id)) {
                  onSelectItem(id, true);
             }
        });
    } else {
        // Deselect all - inform parent for each item
        selectedItems.forEach(id => {
             onSelectItem(id, false);
        });
    }
  };
  
  const getItemPrice = (item: EnhancedCartItem) => {
    try {
      console.log('计算商品价格详情:', {
        id: item.id,
        price: item.price,
        sellingPrice: item.selling_price,
        discountPercentage: item.discount_percentage,
        originalPrice: item.original_price,
        productSellingPrice: item.product?.selling_price
      });
      if (item.discount_percentage && item.price) {
        const discount = parseFloat(item.discount_percentage.toString()) / 100;
        const discountedPrice = item.price * (1 - discount);
        console.log(`通过折扣计算: 原价${item.price} * (1 - ${discount}) = ${discountedPrice}`);
        return discountedPrice.toFixed(2);
      }
      if (item.selling_price && Number(item.selling_price) > 0) {
        console.log(`使用selling_price: ${item.selling_price}`);
        return Number(item.selling_price).toFixed(2);
      }
      if (item.original_price && item.price) {
        console.log(`使用折扣后价格: ${item.price} (原价: ${item.original_price})`);
        return item.price.toFixed(2);
      }
      if (typeof item.price === 'number' && item.price > 0) {
        console.log(`使用普通价格: ${item.price}`);
        return item.price.toFixed(2);
      }
      if (item.product?.selling_price) {
        console.log(`使用product.selling_price: ${item.product.selling_price}`);
        return item.product.selling_price.toFixed(2);
      }
      console.log(`没有找到合适的价格，使用默认值: ${item.price || '0.00'}`);
      return item.price ? item.price.toFixed(2) : '0.00';
    } catch (error) {
      console.error('价格计算出错:', error);
      return '0.00';
    }
  };
  
  const getDisplayQuantity = (id: number): number => {
    const item = localItems.find(i => i.id === id);
    return item ? item.quantity : 1; // Default to 1 if not found
  };

  const getItemSubtotal = (item: EnhancedCartItem) => {
    try {
      if (!item) {
        console.error('计算小计错误: 商品为空');
        return '0.00';
      }
      const priceStr = getItemPrice(item);
      const price = parseFloat(priceStr);
      if (isNaN(price)) {
        console.error(`计算小计错误: 价格格式错误 "${priceStr}"`);
        return '0.00';
      }
      const quantity = getDisplayQuantity(item.id); // Use ID
      if (isNaN(quantity)) {
        console.error(`计算小计错误: 数量格式错误 "${quantity}"`);
        return '0.00';
      }
      const subtotal = price * quantity;
      return subtotal.toFixed(2);
    } catch (error) {
      console.error('计算商品小计时出错:', error);
      return '0.00';
    }
  };
  
  const formatSpecifications = (specs: any) => {
    if (!specs) return [];
    return Object.entries(specs).map(([key, value]) => ({
      name: key as string,
      value: value as string
    }));
  };
  
  const parseParameterGroup = (paramGroup: string): Record<string, string> => {
    if (!paramGroup) return {};
    const params: Record<string, string> = {};
    paramGroup.split(';').forEach(pair => {
      const parts = pair.trim().split('=');
      if (parts.length === 2) {
        const key = parts[0].trim();
        const value = parts[1].trim();
        params[key] = value;
      }
    });
    return params;
  };
  
  const getProductImageUrl = (item: EnhancedCartItem): string => {
    try {
      console.log('处理商品图片信息:', {
        itemId: item.id,
        productId: item.product_id,
        hasOuterImages: 'images' in item && Array.isArray(item.images),
        outerImages: 'images' in item ? item.images : null,
        productData: item.product,
        hasImages: !!(item.product?.images),
        imageData: typeof item.product?.images === 'object' ? 
          (Array.isArray(item.product?.images) ? item.product?.images[0] : item.product?.images) : 
          item.product?.images
      });
      if ('images' in item && Array.isArray(item.images) && item.images.length > 0) {
        const imageUrl = item.images[0];
        console.log('从外层images数组获取URL:', imageUrl);
        if (!imageUrl) {
          return `/images/placeholder-product.jpg`;
        }
        if (typeof imageUrl === 'string') {
          if (imageUrl.startsWith('http')) {
            return imageUrl;
          }
          return imageUrl.startsWith('/') ? imageUrl : `/${imageUrl}`;
        } else if (typeof imageUrl === 'object' && imageUrl !== null && 'url' in imageUrl) {
          const url = imageUrl.url as string;
          if (url.startsWith('http')) {
            return url;
          }
          return url.startsWith('/') ? url : `/${url}`;
        }
      }
      if (!item.product) {
        console.log('商品对象不存在，使用占位图');
        return '/images/placeholder-product.jpg';
      }
      if (Array.isArray(item.product.images) && item.product.images.length > 0) {
        const image = item.product.images[0];
        if (image && typeof image === 'object' && 'url' in image) {
          const imageUrl = image.url as string;
          console.log('从images数组对象中获取URL:', imageUrl);
          if (!imageUrl) {
            console.log('图片URL为空，使用占位图');
            return `/images/placeholder-product.jpg`;
          }
          if (imageUrl.startsWith('http')) {
            return imageUrl;
          }
          return imageUrl.startsWith('/') ? imageUrl : `/${imageUrl}`;
        }
        if (typeof image === 'string') {
          const imageUrl = image;
          console.log('从images字符串数组中获取URL:', imageUrl);
          if (!imageUrl) {
            return `/images/placeholder-product.jpg`;
          }
          if (imageUrl.startsWith('http')) {
            return imageUrl;
          }
          return imageUrl.startsWith('/') ? imageUrl : `/${imageUrl}`;
        }
      }
      if (item.product.images && typeof item.product.images === 'string') {
        const imageUrl = item.product.images;
        console.log('从images字符串中获取URL:', imageUrl);
        if (!imageUrl) {
          return `/images/placeholder-product.jpg`;
        }
        if (imageUrl.startsWith('http')) {
          return imageUrl;
        }
        return imageUrl.startsWith('/') ? imageUrl : `/${imageUrl}`;
      }
      if (item.image) {
        const imageUrl = item.image;
        console.log('从item.image字段获取URL:', imageUrl);
        if (!imageUrl) {
          return `/images/placeholder-product.jpg`;
        }
        if (imageUrl.startsWith('http')) {
          return imageUrl;
        }
        return imageUrl.startsWith('/') ? imageUrl : `/${imageUrl}`;
      }
      console.log('无有效图片，使用占位图');
      return `/images/placeholder-product.jpg`;
    } catch (error) {
      console.error('图片URL处理出错:', error);
      return `/images/placeholder-product.jpg`;
    }
  };
  
  const getTemplateUrl = (item: EnhancedCartItem): string => {
    try {
      const templateId = item.product?.template_id;
      console.log('生成模板URL:', {
        itemId: item.id, 
        productId: item.product_id,
        templateId,
        specData: item.specifications
      });
      if (!templateId) {
        return `/products/${item.product_id}`;
      }
      let url = `/products/${templateId}`;
      if (item.specifications && Object.keys(item.specifications).length > 0) {
        const specParams = new URLSearchParams();
        for (const [key, value] of Object.entries(item.specifications)) {
          if (key !== 'template_id') {
            specParams.append(key, value);
          }
        }
        if (Array.from(specParams).length > 0) {
          url += `?${specParams.toString()}`;
        }
      } else {
        const cachedTemplate = templateCache[templateId];
        if (cachedTemplate) {
          const linkedProduct = cachedTemplate.linked_products.find(p => p.id === item.product_id);
          if (linkedProduct && linkedProduct.parameter_group) {
            const params = parseParameterGroup(linkedProduct.parameter_group);
            if (Object.keys(params).length > 0) {
              const specParams = new URLSearchParams();
              for (const [key, value] of Object.entries(params)) {
                specParams.append(key, value);
              }
              url += `?${specParams.toString()}`;
            }
          }
        }
      }
      console.log('生成的URL路径:', url);
      return url;
    } catch (error) {
      console.error('模板URL生成出错:', error);
      return `/products/${item.product_id}`;
    }
  };

  const getProductName = (item: EnhancedCartItem) => {
    const product = item.product;
    if (item.template?.name) {
      console.log('使用template.name:', item.template.name);
      return item.template.name;
    }
    if (item.specifications?.template_name) {
      console.log('使用specifications.template_name:', item.specifications.template_name);
      return item.specifications.template_name;
    }
    if (product?.template_name) {
      console.log('使用product.template_name:', product.template_name);
      return product.template_name;
    }
    if (product?.name) {
      console.log('使用product.name:', product.name);
      return product.name;
    }
    console.log('没有找到名称，使用默认名称:', `未知商品 #${item.id}`);
    return `未知商品 #${item.id}`;
  };

  const getVariantName = (item: EnhancedCartItem) => {
    const product = item.product;
    const templateName = item.specifications?.template_name || product?.template_name;
    if (!product) return '';
    const specs = item.specifications || {};
    const specEntries = Object.entries(specs).filter(
      ([key]) => key !== 'template_id' && key !== 'template_name'
    );
    if (specEntries.length === 0) {
      return '';
    }
    const specsText = specEntries
      .map(([key, value]) => `${key}: ${value}`)
      .join(', ');
    return `Variant: ${templateName || product.name} (${specsText})`;
  };

  const executeRemove = (id: number, e?: React.MouseEvent) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    setItemToCheckRemove(null);
    handleRemoveItem(id);
    return false;
  };
  
  const handleRemoveConfirm = (id: number, e?: React.MouseEvent) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    if (itemToCheckRemove !== null) {
      handleRemoveItem(id);
      return;
    }
    setItemToCheckRemove(id);
  };
  
  const handleClearCart = async (e?: React.MouseEvent) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    setShowClearCartConfirm(true);
  };

  const executeClearCart = async () => {
    try {
      setIsUpdating(true);
      console.log('执行清空购物车...');
      if (parentOnClearCart) {
        parentOnClearCart();
          } else {
        console.warn('parentOnClearCart function not provided');
      }
      setShowClearCartConfirm(false);
      setLocalItems([]);
      //setSelectedItems([]); // Also clear selected items
      onSelectItem(-1, false); // Use special ID -1 to signal clear all to parent
      setLocalOrderSummary({ subtotal: 0, total: 0, itemCount: 0 }); // Reset summary
      toast.success('Cart cleared successfully');
    } catch (error: any) {
      console.error('Failed to clear cart:', error);
      toast.error('Failed to clear cart. Please try again.');
    } finally {
      setIsUpdating(false);
    }
  };

  const handleDecrease = (id: number, e?: React.MouseEvent) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    const currentQuantity = getDisplayQuantity(id);
    if (currentQuantity <= 1) {
      setShowZeroQuantityConfirm(true);
      setItemToCheckRemove(id);
      return;
    }
    const item = document.querySelector(`[data-item-id="${id}"]`);
    if (item) {
      item.classList.add('quantity-changed');
      setTimeout(() => {
        item.classList.remove('quantity-changed');
      }, 700);
    }
    const button = e?.currentTarget;
    if (button) {
      button.classList.add('button-pulse');
      setTimeout(() => {
        button.classList.remove('button-pulse');
      }, 300);
    }
    const quantityElement = item?.querySelector('.quantity-display');
    if (quantityElement) {
      quantityElement.classList.add('quantity-decrement');
      setTimeout(() => {
        quantityElement.classList.remove('quantity-decrement');
      }, 200);
    }
    //updateLocalState(id, currentQuantity - 1); // Update locally immediately
    handleQuantityChange(id, currentQuantity - 1); // Call parent handler
  };

  const handleIncrease = (id: number, e?: React.MouseEvent) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    const currentQuantity = getDisplayQuantity(id);
    const item = document.querySelector(`[data-item-id="${id}"]`);
    if (item) {
      item.classList.add('quantity-changed');
      setTimeout(() => {
        item.classList.remove('quantity-changed');
      }, 700);
    }
    const button = e?.currentTarget;
    if (button) {
      button.classList.add('button-pulse');
      setTimeout(() => {
        button.classList.remove('button-pulse');
      }, 300);
    }
    const quantityElement = item?.querySelector('.quantity-display');
    if (quantityElement) {
      quantityElement.classList.add('quantity-increment');
      setTimeout(() => {
        quantityElement.classList.remove('quantity-increment');
      }, 200);
    }
    //updateLocalState(id, currentQuantity + 1); // Update locally immediately
    handleQuantityChange(id, currentQuantity + 1); // Call parent handler
  };

  const hasAnyPendingUpdates = useCallback((): boolean => {
    const updatesInQueue = Object.keys(updateQueueRef.current).length > 0;
    const itemsBeingRemovedCount = itemsBeingRemoved.size > 0;
    const anyPending = updatesInQueue || itemsBeingRemovedCount;
    if (anyPending) {
      console.log(`更新状态：队列中有 ${Object.keys(updateQueueRef.current).length} 个数量更新和 ${itemsBeingRemoved.size} 个删除操作`);
    }
    return anyPending;
  }, [itemsBeingRemoved]);

  const getUpdateStatusText = useCallback((): string => {
    const updatesCount = Object.keys(updateQueueRef.current).length;
    const deletesCount = itemsBeingRemoved.size;
    if (updatesCount > 0 && deletesCount > 0) {
      return `Updating ${updatesCount} items & removing ${deletesCount} items...`;
    } else if (updatesCount > 0) {
      return `Updating ${updatesCount} ${updatesCount === 1 ? 'item' : 'items'}...`;
    } else if (deletesCount > 0) {
      return `Removing ${deletesCount} ${deletesCount === 1 ? 'item' : 'items'}...`;
    }
    return 'Updating Cart...';
  }, [updateQueueRef, itemsBeingRemoved]);
  
  const isPendingUpdate = (id: number): boolean => {
    return !!updateQueueRef.current[id];
  };

  const handleCheckout = useCallback((e?: React.MouseEvent) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    if (selectedItems.length === 0) {
      toast.warning('Please select at least one item to checkout');
      return;
    }
    setShowCheckoutConfirm(true); // Show confirmation first
  }, [selectedItems]);

  const executeCheckout = useCallback(() => {
      console.log('Executing checkout with selected items:', selectedItems);
      onCheckout(selectedItems); // Call parent checkout with selected items
      setShowCheckoutConfirm(false);
      // Assuming parent (CartPage) handles navigation and state clearing after checkout
  }, [selectedItems, onCheckout]);

  if (!cart || !cart.items) {
    return (
      <div className="flex flex-col items-center justify-center p-8">
        <LoadingSpinner size="large" />
        <p className="mt-4 text-gray-600">Loading cart data...</p>
      </div>
    );
  }
  
  if (isLoadingProducts) {
    return (
      <div className="flex flex-col items-center justify-center p-8">
        <LoadingSpinner size="large" />
        <p className="mt-4 text-gray-600">加载产品数据中...</p>
      </div>
    );
  }

  const cartAnimationStyles = `
    @keyframes quantityPulse {
      0% { background-color: transparent; }
      30% { background-color: rgba(219, 234, 254, 0.6); }
      100% { background-color: transparent; }
    }
    
    @keyframes subtotalHighlight {
      0% { color: #1F2937; transform: scale(1); }
      50% { color: #3B82F6; transform: scale(1.1); }
      100% { color: #1F2937; transform: scale(1); }
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideIn {
      from { transform: translateX(-10px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes floatUp {
      0% { opacity: 0; transform: translateY(6px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes floatDown {
      0% { opacity: 0; transform: translateY(-6px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes scaleButton {
      0% { transform: scale(1); }
      50% { transform: scale(0.97); }
      100% { transform: scale(1); }
    }
    
    @keyframes itemRemove {
      0% { opacity: 1; transform: scale(1); max-height: 200px; }
      70% { opacity: 0; transform: scale(0.95) translateX(-20px); max-height: 200px; }
      100% { opacity: 0; transform: scale(0.95) translateX(-20px); max-height: 0; margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; }
    }
    
    .quantity-changed {
      animation: quantityPulse 0.7s ease-out;
    }
    
    .subtotal-changed {
      animation: subtotalHighlight 0.7s ease-out;
    }
    
    .cart-item-enter {
      animation: fadeInUp 0.5s ease-out;
    }
    
    .cart-icon-animate {
      animation: slideIn 0.4s ease-out;
    }
    
    .quantity-increment {
      animation: floatDown 0.2s ease-out;
    }
    
    .quantity-decrement {
      animation: floatUp 0.2s ease-out;
    }
    
    .button-pulse {
      animation: scaleButton 0.3s ease-out;
    }
    
    .loading-bar {
      height: 2px;
      background: linear-gradient(90deg, #3B82F6, #60A5FA);
      width: 100%;
      position: absolute;
      top: 0;
      left: 0;
      background-size: 200% 100%;
      animation: loadingBar 1.5s infinite linear;
    }
    
    @keyframes loadingBar {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    
    .animate-bounce-subtle {
      animation: bounceSlight 2s infinite ease-in-out;
    }
    
    @keyframes bounceSlight {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }
    
    .pulse-shadow {
      animation: pulseShadow 2s infinite;
    }
    
    @keyframes pulseShadow {
      0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.3); }
      70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
      100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
  `;

  return (
    <>
      <style>{cartAnimationStyles}</style>
      <div className="container mx-auto px-4 py-6 sm:py-8 lg:py-10 bg-white min-h-screen max-w-7xl">
        {/* Page Header */}
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-10 pb-6 border-b border-gray-100">
          <div className="flex items-center">
            {onBackToList && (
            <button 
              onClick={onBackToList}
              className="text-gray-500 hover:text-gray-700 transition-colors duration-200 mr-4 flex items-center"
            >
              <ArrowLeftIcon className="w-5 h-5" />
              <span className="ml-2 text-sm font-medium hidden sm:inline">Back</span>
            </button>
             )}
            <h1 className="text-2xl font-medium text-gray-800">{cart.name || 'Shopping Cart'}</h1>
          </div>
          {/* Optional: Add clear cart button here if needed, using handleClearCart */} 
           {localItems.length > 0 && typeof parentOnClearCart === 'function' && (
                <button
                  onClick={handleClearCart} 
                  className="text-sm text-red-600 hover:text-red-800 font-medium flex items-center transition-colors duration-200"
                  disabled={hasAnyPendingUpdates()}
                >
                  <TrashIcon className="w-4 h-4 mr-1" />
                  Clear Cart
                </button>
            )}
        </div>
        
        {/* Loading Indicator */}
        {cartLoading && (
          <div className="flex justify-center items-center py-12">
            <LoadingSpinner size="large" />
          </div>
        )}
        
        {/* Empty Cart View */}
        {!cartLoading && localItems.length === 0 && (
          // ... (keep empty cart view JSX) ...
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, ease: "easeOut" }}
            className="flex flex-col items-center justify-center py-20 px-4"
          >
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              transition={{ delay: 0.2, duration: 0.5 }}
              className="text-gray-200 mb-10 relative animate-bounce-subtle"
            >
              <ShoppingCartIcon className="w-32 h-32" />
              <motion.div 
                className="absolute -top-4 -right-4 bg-red-50 rounded-full w-12 h-12 flex items-center justify-center"
                initial={{ scale: 0, rotate: -45 }}
                animate={{ scale: 1, rotate: 0 }}
                transition={{ delay: 0.7, type: "spring", stiffness: 200 }}
              >
                <span className="text-red-500 text-sm font-medium">0</span>
              </motion.div>
            </motion.div>
            
            <motion.h2 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.3, duration: 0.5 }}
              className="text-3xl font-light text-gray-800 mb-4 text-center"
            >
              Your cart is empty
            </motion.h2>
            
            <motion.p 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.4, duration: 0.5 }}
              className="text-gray-500 mb-10 text-center max-w-md text-lg"
            >
              You haven't added any items to your cart yet
            </motion.p>
            
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.5, duration: 0.5 }}
              className="flex flex-col sm:flex-row gap-5"
            >
              <motion.div
                whileHover={{ scale: 1.05, y: -5 }}
                whileTap={{ scale: 0.98 }}
                transition={{ type: "spring", stiffness: 300 }}
              >
                <Link 
                  to="/products"
                  className="px-10 py-4 bg-gray-900 text-white rounded-full text-base font-medium transition-all duration-300 hover:bg-gray-800 hover:shadow-lg flex items-center"
                >
                  <ShoppingBagIcon className="w-5 h-5 mr-2" />
                  Start Shopping
                </Link>
              </motion.div>
              
              <motion.div
                whileHover={{ scale: 1.05, y: -5 }}
                whileTap={{ scale: 0.98 }}
                transition={{ type: "spring", stiffness: 300 }}
              >
                <Link 
                  to="/featured" 
                  className="px-10 py-4 border border-gray-300 text-gray-700 rounded-full text-base font-medium transition-all duration-300 hover:bg-gray-50 hover:shadow-md flex items-center"
                >
                  <TagIcon className="w-5 h-5 mr-2" />
                  View Featured
                </Link>
              </motion.div>
            </motion.div>
          </motion.div>
        )}

        {/* Cart Content */}
        {!cartLoading && localItems.length > 0 && (
          <div className="flex flex-col lg:flex-row gap-8">
            <div className="flex-grow">
              {/* Cart Header Row with Select All */}
              <div className="border-b border-gray-200 pb-4 mb-6 flex items-center">
                 <div className="w-10 pl-4">
                     <input
                         type="checkbox"
                         ref={selectAllRef} // Use ref
                         onChange={handleSelectAll} // Attach handler
                         className="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                  </div>
                <div className="flex-grow ml-4">
                  <div className="flex justify-between">
                    <span className="text-sm font-medium text-gray-600">Product</span>
                    <div className="flex space-x-10">
                      <span className="text-sm font-medium text-gray-600 w-20 text-center">Quantity</span>
                      <span className="text-sm font-medium text-gray-600 w-20 text-right">Subtotal</span>
                    </div>
                  </div>
                </div>
              </div>
              
              {/* Cart Item List */}
              <div className="space-y-4">
                {localItems.map(item => {
                  const itemPrice = getItemPrice(item);
                  const subtotal = getItemSubtotal(item);
                  const isSelected = selectedItems.includes(item.id);
                  const isCurrentlyUpdating = updatingItemId === item.id;
                  const isBeingRemoved = itemsBeingRemoved.has(item.id);
                  return (
                    <motion.div
                      key={item.id}
                      className={`relative bg-white rounded-lg border ${isSelected ? 'border-blue-300 bg-blue-50' : 'border-gray-200'} overflow-hidden transition-colors duration-200 ${isCurrentlyUpdating ? 'opacity-80 pointer-events-none' : ''}`}
                      style={{ animation: item.isRemoving ? 'itemRemove 0.3s forwards' : 'none' }}
                      data-item-id={item.id}
                      layout
                    >
                      <div className="p-4 flex flex-col sm:flex-row">
                        {/* Item Checkbox */}
                         <div className="flex-shrink-0 mr-1 flex items-center sm:pl-4">
                          <input
                            type="checkbox"
                                checked={isSelected}
                                onChange={(e) => onSelectItem(item.id, e.target.checked)}
                            className="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            disabled={isCurrentlyUpdating || isBeingRemoved}
                          />
                        </div>
                        
                        {/* Product Image */}
                        <div className="flex-shrink-0 sm:ml-3">
                          <Link 
                            to={getTemplateUrl(item)}
                            className={`block relative w-full sm:w-20 h-20 bg-gray-100 rounded-md overflow-hidden ${isCurrentlyUpdating || isBeingRemoved ? 'opacity-50' : ''}`}
                          >
                            <img 
                              src={getProductImageUrl(item)} 
                              alt={getProductName(item)}
                              className="object-cover object-center w-full h-full"
                              onError={(e) => { (e.target as HTMLImageElement).src = '/images/placeholder-product.jpg'; }}
                            />
                          </Link>
                        </div>
                        
                        {/* Product Details, Quantity, Subtotal */}
                        <div className="flex-grow sm:ml-4 mt-3 sm:mt-0 flex flex-col">
                          <div className="flex justify-between">
                            <div>
                              <Link 
                                to={getTemplateUrl(item)} 
                                className="text-base font-medium text-gray-800 hover:text-blue-600 block mb-1"
                              >
                                {getProductName(item)}
                              </Link>
                              {getVariantName(item) && (
                                <p className="text-sm text-gray-500 mb-1">{getVariantName(item)}</p>
                              )}
                              <p className="text-sm text-gray-500">
                                Price: ${itemPrice}
                              </p>
                            </div>
                            
                            <div className="flex flex-col sm:flex-row items-end sm:items-center space-y-2 sm:space-y-0 sm:space-x-8">
                              {/* Quantity Controls */}
                              <div className="flex items-center">
                                <button
                                  className="flex-shrink-0 bg-gray-100 hover:bg-gray-200 rounded-l-md w-8 h-8 flex items-center justify-center border border-gray-300 transition-colors duration-200 focus:outline-none disabled:opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                  onClick={(e) => handleDecrease(item.id, e)}
                                    disabled={getDisplayQuantity(item.id) <= 1 || isBeingRemoved || isCurrentlyUpdating}
                                >
                                  <MinusIcon className="w-4 h-4 text-gray-600" />
                                </button>
                                <div className="w-12 h-8 flex items-center justify-center border-t border-b border-gray-300 bg-white quantity-display relative">
                                  <span className={`text-gray-800 font-medium ${isCurrentlyUpdating ? 'opacity-50' : ''}`}>
                                        {getDisplayQuantity(item.id)}
                                  </span>
                                  {isCurrentlyUpdating && (
                                      <div className="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
                                          <LoadingSpinner size="xs" />
                                      </div>
                                  )}
                                </div>
                                <button
                                  className="flex-shrink-0 bg-gray-100 hover:bg-gray-200 rounded-r-md w-8 h-8 flex items-center justify-center border border-gray-300 transition-colors duration-200 focus:outline-none disabled:opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                  onClick={(e) => handleIncrease(item.id, e)}
                                     disabled={isBeingRemoved || isCurrentlyUpdating}
                                >
                                  <PlusIcon className="w-4 h-4 text-gray-600" />
                                </button>
                              </div>
                              
                              {/* Subtotal & Remove */}
                              <div className="text-right w-24">
                                <p className={`text-gray-800 font-medium ${isCurrentlyUpdating ? 'opacity-50' : ''}`}>
                                  ${subtotal}
                                </p>
                                <button
                                  className="text-red-500 hover:text-red-700 text-sm mt-1 focus:outline-none transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                  onClick={(e) => handleRemoveConfirm(item.id, e)}
                                  disabled={isBeingRemoved || isCurrentlyUpdating}
                                >
                                  {isBeingRemoved ? 'Removing...' : 'Remove'}
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </motion.div>
                  );
                })}
              </div>
            </div>
            
            {/* Order Summary */}
            <div className="lg:w-1/3 xl:w-1/4">
              <motion.div 
                className="bg-gray-50 rounded-lg p-6 sticky top-20"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.5, duration: 0.5 }}
              >
                <h3 className="text-lg font-medium text-gray-800 mb-4">Order Summary</h3>
                <div className="space-y-2 mb-6">
                    {/* Display summary based on selected items */} 
                  <motion.div 
                    className="flex justify-between items-center"
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.6, duration: 0.5 }}
                  >
                         <span className="text-gray-600">Subtotal ({localOrderSummary.itemCount} items)</span> 
                    <span className="text-gray-800 font-medium">
                      ${localOrderSummary.subtotal.toFixed(2)}
                    </span>
                  </motion.div>
                      {/* Add other summary lines (tax, shipping, discount) if needed, calculated based on selected items */} 
                </div>

                <motion.div 
                  className="mt-8 pt-6 border-t border-gray-200"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  transition={{ delay: 0.9, duration: 0.7 }}
                >
                  <motion.div 
                    className="flex justify-between items-center mb-8"
                    initial={{ scale: 0.9, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    transition={{ delay: 1, type: "spring", stiffness: 200 }}
                  >
                    <span className="text-gray-800 font-medium">Total</span>
                    <motion.span 
                      className="text-2xl text-gray-900 font-bold"
                      key={localOrderSummary.total} // Key ensures animation on change
                      initial={{ scale: 0.8 }}
                      animate={{ scale: 1 }}
                      transition={{ type: "spring", stiffness: 300, damping: 15 }}
                    >
                      ${localOrderSummary.total.toFixed(2)}
                    </motion.span>
                  </motion.div>

                  {/* Checkout Button */} 
                  <motion.button
                    variants={buttonVariants}
                    initial={{ y: 20, opacity: 0 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 1.1, duration: 0.5 }}
                    whileHover={{ scale: 1.02, boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)" }}
                    whileTap={{ scale: 0.98 }}
                    onClick={handleCheckout} // Use updated handler
                    className="w-full py-4 bg-gray-900 text-white rounded-full text-base font-medium transition-all duration-300 hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2 pulse-shadow"
                    disabled={selectedItems.length === 0 || hasAnyPendingUpdates()}
                  >
                    {hasAnyPendingUpdates() ? (
                      <>
                        <LoadingSpinner size="small" className="mr-2" />
                        <span>{getUpdateStatusText()}</span>
                      </>
                    ) : (
                      <>
                        <CreditCardIcon className="w-5 h-5 mr-2" />
                         <span>Checkout ({selectedItems.length} Items)</span> 
                        <motion.span 
                          animate={{ x: [0, 3, 0] }}
                          transition={{ repeat: Infinity, duration: 1.5, repeatType: "reverse" }}
                        >
                          <ChevronRightIcon className="w-4 h-4 ml-1" />
                        </motion.span>
                      </>
                    )}
                  </motion.button>

                  <motion.div 
                    className="mt-6 text-center"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 1.2, duration: 0.7 }}
                  >
                    <a href="#" className="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">
                      <QuestionMarkCircleIcon className="w-4 h-4 mr-1" />
                      Need help? Contact support
                    </a>
                  </motion.div>
                </motion.div>
              </motion.div>
            </div>
          </div>
        )}
      </div>

      {/* Confirmation Dialogs */} 
      <ConfirmDialog
        isOpen={showZeroQuantityConfirm}
        title="Remove Item?"
        message="Setting quantity to zero will remove this item. Do you want to proceed?"
        confirmText="Remove Item"
        cancelText="Cancel"
        onConfirm={() => executeRemove(itemToCheckRemove!)} // Use executeRemove
        onCancel={() => setItemToCheckRemove(null)}
        confirmButtonClass="bg-red-600 hover:bg-red-700"
      />
      <ConfirmDialog
        isOpen={showClearCartConfirm}
        title="Clear Cart"
        message="Are you sure you want to remove all items from this cart?"
        confirmText="Clear Cart"
        cancelText="Cancel"
        onConfirm={executeClearCart} 
        onCancel={() => setShowClearCartConfirm(false)}
        confirmButtonClass="bg-red-600 hover:bg-red-700"
      />
      <ConfirmDialog
        isOpen={showCheckoutConfirm}
        title="Confirm Checkout"
        message={`Proceed to checkout with ${selectedItems.length} item(s)?`}
        confirmText="Proceed to Checkout"
        cancelText="Cancel"
        onConfirm={executeCheckout} // Use executeCheckout
        onCancel={() => setShowCheckoutConfirm(false)}
        confirmButtonClass="bg-blue-600 hover:bg-blue-700"
      />
    </>
  );
};

export default CartDetails;