import { createSlice, createAsyncThunk, PayloadAction, isRejectedWithValue } from '@reduxjs/toolkit';
import { apiService } from '../../services/api';
import { toast } from 'react-toastify';
import cartService from '../../services/cartService';
import { api } from '../../services/api';
import { RootState } from '../index';
import { AxiosError } from 'axios';
import { createSelector } from 'reselect';
import 'react-toastify/dist/ReactToastify.css';

// Debug log function
const logDebug = (message: string, data?: any) => {
  console.log(`[CartSlice] ${message}`, data ? data : '');
};

// Cart item type definition
export interface CartItem {
  id: number;
  product_id: number;
  name: string;
  price: number;
  quantity: number;
  size?: string;
  color?: string;
  image: string;
  subtotal: number;
  specifications?: Record<string, string>;
  product?: {
    id: number;
    name: string;
    template_id?: number;
    template_name?: string;
    selling_price: number;
    images?: {
      id: number;
      url: string;
    }[];
  };
}

// Cart type definition
export interface Cart {
  id: number;
  name: string;
  type: string;
  is_default: boolean;
  items: CartItem[];
  total: number;
  item_count: number;
}

// Cart state type definition
interface CartState {
  carts: Cart[];
  activeCartId: number | null;
  totalAmount: number;
  cartCount: number;
  loading: boolean;
  error: string | null;
}

// Initial state
const initialState: CartState = {
  carts: [],
  activeCartId: null,
  totalAmount: 0,
  cartCount: 0,
  loading: false,
  error: null
};

// 获取参数中的购物车类型，默认为default
type FetchCartParams = {
  type?: string;
  cart_id?: number;
};

// Async Action: Fetch cart
export const fetchCart = createAsyncThunk<any, FetchCartParams | void, { rejectValue: string }>(
  'cart/fetchCart',
  async (params = {}, { rejectWithValue }) => {
    try {
      logDebug('Fetching cart', params as FetchCartParams);
      const response: any = await apiService.cart.get(params as FetchCartParams); // Use any for response
      logDebug('Cart fetched successfully', response);
      
      // Adjust based on actual API response structure
      if (!response?.success && !response?.data?.carts) { // Check multiple possible success indicators
        return rejectWithValue(response?.message || 'Failed to fetch cart data structure invalid');
      }
      
      return response.data; // Assuming data holds the cart info
    } catch (error: any) {
      logDebug('Failed to fetch cart', error);
      return rejectWithValue(error.response?.data?.message || error.message || 'Failed to fetch cart');
    }
  }
);

// Type for addToCart payload
interface AddToCartPayload {
    product_id: number; 
    quantity: number; 
  specifications?: Record<string, string> | null; // Allow null
    cart_id?: number;
    cart_type?: string;
    cart_name?: string;
  template_id?: number; // Added
  size?: string; // Added
  color?: string; // Added
}

// Async Action: Add item to cart
export const addToCart = createAsyncThunk<any, AddToCartPayload, { rejectValue: string }>(
  'cart/addToCart',
  async (item, { rejectWithValue }) => {
    try {
      logDebug('添加商品到购物车', item);
      
      // 确保参数格式正确
      const cartItem: any = {
        product_id: item.product_id,
        quantity: item.quantity,
        cart_id: item.cart_id, // Pass cart_id if provided
        cart_type: item.cart_type,
        cart_name: item.cart_name,
        specifications: item.specifications,
        template_id: item.template_id,
        size: item.size,
        color: item.color
      };
      
      const response: any = await apiService.cart.add(cartItem); // Use any for response
      logDebug('添加商品成功', response);
      
      // Adjust based on actual API response structure
      if (!response?.success && !response?.data?.cart) { 
        toast.error(response?.message || '添加到购物车失败 (Invalid Response)');
        return rejectWithValue(response?.message || '添加商品到购物车失败');
      }
      
      toast.success('商品已添加到购物车');
      return response.data; // Assuming data holds the updated cart
    } catch (error: any) {
      logDebug('添加商品到购物车失败', error);
      
      let errorMessage = '添加到购物车失败';
      if (error.response?.data?.error) {
        if (error.response.data.error.includes('Integrity constraint violation') && 
            error.response.data.error.includes('Duplicate entry')) {
          errorMessage = '该商品已在购物车中';
        } else {
          errorMessage = error.response.data.message || errorMessage;
        }
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      toast.error(errorMessage);
      return rejectWithValue(errorMessage);
    }
  }
);

// Async Action: Update cart item
export const updateCartItem = createAsyncThunk<any, { id: number; quantity: number }, { rejectValue: string }>(
  'cart/updateCartItem',
  async ({ id, quantity }, { rejectWithValue }) => {
    try {
      logDebug('Updating cart item', { id, quantity });
      const response: any = await apiService.cart.update(id, { quantity }); // Use any for response
      logDebug('Cart item updated successfully', response);
      
      // Adjust based on actual API response structure
      if (!response?.success && !response?.data?.cart) { 
        toast.error(response?.message || 'Failed to update cart (Invalid Response)');
        return rejectWithValue(response?.message || 'Failed to update cart item');
      }
      
      return response.data; // Assuming data holds the updated cart
    } catch (error: any) {
      logDebug('Failed to update cart item', error);
      toast.error(error.response?.data?.message || error.message || 'Failed to update cart');
      return rejectWithValue(error.response?.data?.message || error.message || 'Failed to update cart item');
    }
  }
);

// Async Action: Remove item from cart
export const removeFromCart = createAsyncThunk<any, number, { rejectValue: string }>(
  'cart/removeFromCart',
  async (id, { rejectWithValue }) => {
    try {
      logDebug('Removing item from cart', id);
      const response: any = await apiService.cart.remove(id); // Use any for response
      logDebug('Item removed from cart successfully', response);
      
      // Adjust based on actual API response structure
      if (!response?.success && !response?.data?.cart) { 
        toast.error(response?.message || 'Failed to remove item (Invalid Response)');
        return rejectWithValue(response?.message || 'Failed to remove item from cart');
      }
      
      toast.success('Item removed from cart');
      return response.data; // Assuming data holds the updated cart (or just item id)
    } catch (error: any) {
      logDebug('Failed to remove item from cart', error);
      toast.error(error.response?.data?.message || error.message || 'Failed to remove item');
      return rejectWithValue(error.response?.data?.message || error.message || 'Failed to remove item from cart');
    }
  }
);

// Async Action: Clear cart
export const clearCart = createAsyncThunk(
  'cart/clearCart',
  async (_, { getState }) => {
    try {
      await apiService.cart.clear();
      return { success: true };
    } catch (error) {
      console.error('清空购物车失败:', error);
      toast.error('清空购物车失败');
      throw error;
    }
  }
);

// Type for createCart payload
interface CreateCartPayload { name: string; type: string; is_default?: boolean }

// Async Action: Create new cart
export const createCart = createAsyncThunk<any, CreateCartPayload, { rejectValue: string }>(
  'cart/createCart',
  async (data, { rejectWithValue, dispatch }) => {
    try {
      logDebug('Creating new cart', data);
      // Assuming cartService exists and has createCart method
      const response: any = await (cartService as any).createCart(data); // Use any for response and service
      logDebug('Cart created successfully', response);
      
      if (!response?.success && !response?.data?.cart) {
        toast.error(response?.message || 'Failed to create cart (Invalid Response)');
        return rejectWithValue(response?.message || 'Failed to create cart');
      }
      
      toast.success('Cart created successfully');
      dispatch(fetchCart()); // Refresh cart list after creating
      return response.data.cart; // Return the newly created cart
    } catch (error: any) {
      logDebug('Failed to create cart', error);
      toast.error(error.response?.data?.message || error.message || 'Failed to create cart');
      return rejectWithValue(error.response?.data?.message || error.message || 'Failed to create cart');
    }
  }
);

// Type for updateCart payload
interface UpdateCartPayload { id: number; data: { name?: string; type?: string; is_default?: boolean } }

// Async Action: Update cart details
export const updateCart = createAsyncThunk<any, UpdateCartPayload, { rejectValue: string }>(
  'cart/updateCart',
  async ({ id, data }, { rejectWithValue, dispatch }) => {
    try {
      logDebug('Updating cart', { id, data });
      const response: any = await (cartService as any).updateCart(id, data); // Use any for response and service
      logDebug('Cart updated successfully', response);
      
      if (!response?.success && !response?.data?.cart) {
        toast.error(response?.message || 'Failed to update cart (Invalid Response)');
        return rejectWithValue(response?.message || 'Failed to update cart');
      }
      
      toast.success('Cart updated successfully');
      dispatch(fetchCart()); // Refresh cart list
      return response.data.cart; // Return the updated cart
    } catch (error: any) {
      logDebug('Failed to update cart', error);
      toast.error(error.response?.data?.message || error.message || 'Failed to update cart');
      return rejectWithValue(error.response?.data?.message || error.message || 'Failed to update cart');
    }
  }
);

// Type for deleteCart payload
interface DeleteCartPayload { id: number }

// Async Action: Delete cart
export const deleteCart = createAsyncThunk<any, DeleteCartPayload, { rejectValue: string }>(
  'cart/deleteCart',
  async ({ id }, { rejectWithValue, dispatch }) => {
    try {
      logDebug('Deleting cart', { id });
      const response: any = await (cartService as any).deleteCart(id); // Use any for response and service
      logDebug('Cart deleted successfully', response);
      
      if (!response?.success) { // Check only success flag
        toast.error(response?.message || 'Failed to delete cart (Invalid Response)');
        return rejectWithValue(response?.message || 'Failed to delete cart');
      }
      
      toast.success('Cart deleted successfully');
      dispatch(fetchCart()); // Refresh cart list
      return { deletedCartId: id }; // Return the ID of the deleted cart
    } catch (error: any) {
      logDebug('Failed to delete cart', error);
      toast.error(error.response?.data?.message || error.message || 'Failed to delete cart');
      return rejectWithValue(error.response?.data?.message || error.message || 'Failed to delete cart');
    }
  }
);

// Async Action: Move items between carts
export const moveCartItems = createAsyncThunk(
  'cart/moveItems',
  async ({ sourceCartId, targetCartId, items }: { sourceCartId: number; targetCartId: number; items: number[] }, { getState }) => {
    try {
      const state = getState() as RootState;
      const sourceCart = state.cart.carts.find((cart: Cart) => cart.id === sourceCartId);
      const itemsToMove = sourceCart?.items.filter((item: CartItem) => items.includes(item.id)) || [];

      await apiService.cart.moveItems({
        source_cart_id: sourceCartId,
        target_cart_id: targetCartId,
        item_ids: items
      });

      return {
        success: true,
        sourceCartId,
        targetCartId,
        items
      };
    } catch (error) {
      console.error('移动购物车商品失败:', error);
      toast.error('移动购物车商品失败');
      throw error;
    }
  }
);

// Cart slice
export const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {
    // Clear cart error
    clearCartError: (state) => {
      logDebug('Clearing cart error');
      state.error = null;
    },
    
    // Reset cart (for logout or user switch)
    resetCart: () => {
      logDebug('Resetting cart');
      return initialState;
    },
    
    // Set active cart ID
    setActiveCart: (state, action: PayloadAction<number>) => {
      logDebug('Setting active cart', action.payload);
      state.activeCartId = action.payload;
      
      // If there's no cart with this ID, find the default cart
      if (!state.carts.find(cart => cart.id === action.payload)) {
        const defaultCart = state.carts.find(cart => cart.is_default);
        if (defaultCart) {
          state.activeCartId = defaultCart.id;
        } else if (state.carts.length > 0) {
          state.activeCartId = state.carts[0].id;
        }
      }
    },
    
    // Exit current cart and return to default cart
    exitCurrentCart: (state) => {
      logDebug('Exiting current cart');
      // Find default cart
      const defaultCart = state.carts.find(cart => cart.is_default);
      
      if (defaultCart) {
        state.activeCartId = defaultCart.id;
      } else if (state.carts.length > 0) {
        state.activeCartId = state.carts[0].id;
      } else {
        state.activeCartId = null;
      }
    },

    setCartData: (state, action: PayloadAction<any>) => {
      const { carts, total } = action.payload;
      state.carts = carts.map((cart: any) => ({
        ...cart,
        items: cart.items.map((item: any) => ({
          ...item,
          // 确保每个item都有price字段，如果没有则使用product的selling_price
          price: item.price || (item.product ? item.product.selling_price : 0)
        }))
      }));
      state.totalAmount = total;
      state.cartCount = carts.reduce((count: number, cart: any) => count + cart.item_count, 0);
      state.loading = false;
      state.error = null;
    }
  },
  extraReducers: (builder) => {
    // Handle fetch cart
    builder
      .addCase(fetchCart.pending, (state) => {
        state.loading = true;
        state.error = null;
        logDebug('fetchCart pending');
      })
      .addCase(fetchCart.fulfilled, (state, action) => {
        state.loading = false;
        state.carts = action.payload.carts || [];
        state.totalAmount = action.payload.total || 0;
        state.cartCount = action.payload.cart_count || 0;
        
        // Find the default cart and set it as active, or the first cart if none is default
        const defaultCart = state.carts.find(cart => cart.is_default);
        if (defaultCart) {
          state.activeCartId = defaultCart.id;
        } else if (state.carts.length > 0) {
          state.activeCartId = state.carts[0].id;
        }
        logDebug('fetchCart fulfilled', { carts: state.carts, activeCartId: state.activeCartId });
      })
      .addCase(fetchCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
        logDebug('fetchCart rejected', { error: state.error });
      })
    
    // Handle add to cart
      .addCase(addToCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(addToCart.fulfilled, (state, action) => {
        state.loading = false;
        // Fetch the cart again to get updated state
        // This is simpler than trying to manually update the state here
        logDebug('addToCart fulfilled, will re-fetch cart');
      })
      .addCase(addToCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
    
    // Handle update cart item
      .addCase(updateCartItem.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(updateCartItem.fulfilled, (state, action) => {
        // Update the specific item in the relevant cart
        const cartId = action.payload.item.cart_id;
        const updatedItem = action.payload.item;
        const cartIndex = state.carts.findIndex(cart => cart.id === cartId);
        if (cartIndex !== -1) {
          const itemIndex = state.carts[cartIndex].items.findIndex(item => item.id === updatedItem.id);
          if (itemIndex !== -1) {
            state.carts[cartIndex].items[itemIndex] = updatedItem;
            // Recalculate cart total
            state.carts[cartIndex].total = state.carts[cartIndex].items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
          }
        }
        logDebug('updateCartItem fulfilled', { cartId, updatedItem });
      })
      .addCase(updateCartItem.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
    
    // Handle remove from cart
      .addCase(removeFromCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(removeFromCart.fulfilled, (state, action) => {
        // Find the cart and remove the item
        const cartId = action.meta.arg; // The argument passed to the thunk (item ID)
        state.carts.forEach(cart => {
          const itemIndex = cart.items.findIndex(item => item.id === cartId);
          if (itemIndex !== -1) {
            cart.items.splice(itemIndex, 1);
            cart.total = cart.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            cart.item_count = cart.items.length;
          }
        });
        logDebug('removeFromCart fulfilled', { removedItemId: cartId });
      })
      .addCase(removeFromCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
    
    // Handle clear cart
      .addCase(clearCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(clearCart.fulfilled, (state, action) => {
        state.loading = false;
        const activeCart = state.carts.find(cart => cart.id === state.activeCartId);
        if (activeCart) {
          activeCart.items = [];
          activeCart.total = 0;
          activeCart.item_count = 0;
        }
        state.totalAmount = 0;
        state.cartCount = 0;
      })
      .addCase(clearCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || '清空购物车失败';
      })
    
    // Handle create cart
      .addCase(createCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(createCart.fulfilled, (state, action) => {
        const newCart = action.payload; // Assuming payload is the new cart object
        if (newCart) {
          // Add the new cart to the state
          state.carts.push(newCart);
          // Optionally, set the new cart as active if it's marked as default
          if (newCart.is_default) {
            state.activeCartId = newCart.id;
            // Ensure other carts are not default
            state.carts.forEach(cart => {
              if (cart.id !== newCart.id) {
                cart.is_default = false;
              }
            });
          }
          state.cartCount = state.carts.length;
          logDebug('createCart fulfilled, added new cart to state', { newCart });
        } else {
          logDebug('createCart fulfilled but payload was empty');
        }
      })
      .addCase(createCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
    // Handle update cart
      .addCase(updateCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(updateCart.fulfilled, (state, action) => {
        const updatedCartData = action.payload.data;
        const cartIndex = state.carts.findIndex(cart => cart.id === updatedCartData.id);
        if (cartIndex !== -1) {
          // Update basic info
          state.carts[cartIndex].name = updatedCartData.name;
          state.carts[cartIndex].type = updatedCartData.type;
          state.carts[cartIndex].is_default = updatedCartData.is_default;

          // Handle default status change
          if (updatedCartData.is_default) {
            state.activeCartId = updatedCartData.id;
            state.carts.forEach(cart => {
              if (cart.id !== updatedCartData.id) {
                cart.is_default = false;
              }
            });
          }
          logDebug('updateCart fulfilled', { updatedCartData });
        } else {
            logDebug('updateCart fulfilled but cart not found in state?');
        }
      })
      .addCase(updateCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
    // Handle delete cart
      .addCase(deleteCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(deleteCart.fulfilled, (state, action) => {
        state.loading = false;
        // Remove the deleted cart from state
        state.carts = state.carts.filter(cart => cart.id !== action.payload.id);
        state.cartCount = state.carts.length;
        
        // If active cart was deleted, set a new active cart
        if (state.activeCartId === action.payload.id) {
          if (state.carts.length > 0) {
            const defaultCart = state.carts.find(cart => cart.is_default);
            state.activeCartId = defaultCart ? defaultCart.id : state.carts[0].id;
          } else {
            state.activeCartId = null;
          }
        }
      })
      .addCase(deleteCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
    
    // Handle move items
      .addCase(moveCartItems.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(moveCartItems.fulfilled, (state, action) => {
        state.loading = false;
        
        if (action.payload) {
          const { sourceCartId, targetCartId, items } = action.payload;
          
          // 从源购物车中移除已移动的商品
          const sourceCart = state.carts.find((cart: Cart) => cart.id === sourceCartId);
          if (sourceCart) {
            sourceCart.items = sourceCart.items.filter((item: CartItem) => !items.includes(item.id));
            // 更新源购物车的总金额和商品数量
            sourceCart.total = sourceCart.items.reduce((sum: number, item: CartItem) => sum + (item.price * item.quantity), 0);
            sourceCart.item_count = sourceCart.items.length;
          }
          
          // 更新目标购物车（这里假设移动后会重新获取购物车数据）
          // 实际应用中可能需要在这里添加更详细的逻辑来处理目标购物车的更新
          
          // 更新全局计数
          state.totalAmount = state.carts.reduce((sum: number, cart) => sum + cart.total, 0);
          state.cartCount = state.carts.reduce((sum: number, cart) => sum + cart.item_count, 0);
        }
      })
      .addCase(moveCartItems.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || '移动购物车商品失败';
      });
  }
});

// Export actions
export const { clearCartError, resetCart, setActiveCart, exitCurrentCart } = cartSlice.actions;

// Export selector to get the active cart
export const selectActiveCart = (state: { cart: CartState }) => {
  const { carts, activeCartId } = state.cart;
  if (!activeCartId) return null;
  return carts.find(cart => cart.id === activeCartId) || null;
};

// Export reducer
export default cartSlice.reducer; 