import React, { createContext, useState, useContext, useEffect, ReactNode } from 'react';
import { toast } from 'react-toastify';
import { ProductTemplate } from '../types/apiTypes';

interface CartItem {
  id: number;
  templateId: number;
  name: string;
  price: number;
  quantity: number;
  image: string;
  color?: string;
  size?: string;
  discount?: number;
}

interface CartContextType {
  items: CartItem[];
  addItem: (item: Omit<CartItem, 'id'>) => void;
  removeItem: (id: number) => void;
  updateQuantity: (id: number, quantity: number) => void;
  clearCart: () => void;
  getCartTotal: () => number;
  getItemsCount: () => number;
  isInCart: (templateId: number) => boolean;
}

// 创建上下文
const CartContext = createContext<CartContextType | undefined>(undefined);

// 自定义钩子，用于访问购物车上下文
export const useCart = () => {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};

interface CartProviderProps {
  children: ReactNode;
}

export const CartProvider: React.FC<CartProviderProps> = ({ children }) => {
  // 从本地存储中获取购物车数据或初始化为空数组
  const [items, setItems] = useState<CartItem[]>(() => {
    const savedCart = localStorage.getItem('cart');
    return savedCart ? JSON.parse(savedCart) : [];
  });

  // 当购物车数据变化时，保存到本地存储
  useEffect(() => {
    localStorage.setItem('cart', JSON.stringify(items));
  }, [items]);

  // 添加商品到购物车
  const addItem = (item: Omit<CartItem, 'id'>) => {
    setItems((prevItems) => {
      // 检查商品是否已在购物车
      const existingItemIndex = prevItems.findIndex(
        (i) => i.templateId === item.templateId && 
              (i.color === item.color) && 
              (i.size === item.size)
      );

      if (existingItemIndex >= 0) {
        // 更新已有商品的数量
        const updatedItems = [...prevItems];
        updatedItems[existingItemIndex].quantity += item.quantity;
        toast.success('已更新购物车商品数量');
        return updatedItems;
      } else {
        // 添加新商品到购物车
        const newItem = {
          ...item,
          id: Date.now(), // 使用时间戳作为临时ID
        };
        toast.success('商品已添加到购物车');
        return [...prevItems, newItem];
      }
    });
  };

  // 从购物车移除商品
  const removeItem = (id: number) => {
    setItems((prevItems) => prevItems.filter((item) => item.id !== id));
    toast.info('商品已从购物车移除');
  };

  // 更新商品数量
  const updateQuantity = (id: number, quantity: number) => {
    if (quantity <= 0) {
      removeItem(id);
      return;
    }

    setItems((prevItems) =>
      prevItems.map((item) =>
        item.id === id ? { ...item, quantity } : item
      )
    );
  };

  // 清空购物车
  const clearCart = () => {
    setItems([]);
    toast.info('购物车已清空');
  };

  // 计算购物车总价
  const getCartTotal = () => {
    return items.reduce((total, item) => {
      const itemPrice = item.discount 
        ? item.price * (1 - item.discount / 100)
        : item.price;
      return total + itemPrice * item.quantity;
    }, 0);
  };

  // 获取购物车商品总数
  const getItemsCount = () => {
    return items.reduce((count, item) => count + item.quantity, 0);
  };

  // 检查商品是否在购物车中
  const isInCart = (templateId: number) => {
    return items.some(item => item.templateId === templateId);
  };

  // 提供上下文值
  const value = {
    items,
    addItem,
    removeItem,
    updateQuantity,
    clearCart,
    getCartTotal,
    getItemsCount,
    isInCart
  };

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
};

export default CartContext; 