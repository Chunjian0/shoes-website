import { useEffect, useState, useCallback, useRef } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store';
import { 
  fetchCart, 
  updateCartItem, 
  removeFromCart, 
  setActiveCart,
  CartItem,
  createCart,
  updateCart,
  deleteCart,
  clearCart,
} from '../store/slices/cartSlice';
import LoadingSpinner from '../components/LoadingSpinner';
import { toast } from 'react-toastify';
import { useSelector } from 'react-redux';
import { RootState } from '../store';
import cartService from '../services/cartService';
import { motion, AnimatePresence, useAnimation } from 'framer-motion';
import ConfirmDialog from '../components/common/ConfirmDialog';
import IntegratedCartView from '../components/cart/IntegratedCartView';
import { TrashIcon } from '@heroicons/react/24/outline';
import SkeletonLoader from '../components/animations/SkeletonLoader';
import CartItemSkeleton from '../components/animations/SkeletonLoader.tsx';

// Parse URL query parameters
function useQuery() {
  return new URLSearchParams(useLocation().search);
}

// Animation configurations
const fadeInUp = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0 }
};

const fadeIn = {
  hidden: { opacity: 0 },
  visible: { opacity: 1 }
};

const scale = {
  tap: { scale: 0.95 },
  hover: { scale: 1.05 }
};

// Create a custom hook for debouncing
const useDebounce = <T extends unknown>(value: T, delay: number): T => {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

// Update queue record
interface UpdateQueueItem {
  id: number;
  quantity: number;
  timestamp: number;
}

// Cart quantity update manager - improved version with instant UI feedback
function useCartQuantityManager() {
  // Local storage for cart item quantities
  const localQuantities = useRef<Record<number, number>>({});
  // Update queue
  const updateQueue = useRef<Record<number, UpdateQueueItem>>({});
  // Timers
  const timers = useRef<Record<number, NodeJS.Timeout>>({});
  const dispatch = useAppDispatch();

  // Initialize local quantities
  const initializeLocalQuantities = useCallback((items: CartItem[]) => {
    const newQuantities: Record<number, number> = {};
    items.forEach(item => {
      newQuantities[item.id] = item.quantity;
    });
    localQuantities.current = newQuantities;
  }, []);

  // Get local quantity - immediately reflected in UI
  const getLocalQuantity = useCallback((id: number) => {
    return localQuantities.current[id] || 0;
  }, []);

  // Update local quantity - immediately update UI, submit to server later
  const updateLocalQuantity = useCallback((id: number, quantity: number) => {
    if (quantity < 1) return; // Ensure quantity is at least 1
    
    // Immediately update local state to reflect in UI
    localQuantities.current = {
      ...localQuantities.current,
      [id]: quantity
    };
    
    // Add to update queue
    updateQueue.current[id] = {
      id,
      quantity,
      timestamp: Date.now()
    };
    
    // Clear existing timer
    if (timers.current[id]) {
      clearTimeout(timers.current[id]);
    }
    
    // Set new timer, execute API call after 500ms (reduce delay feeling)
    timers.current[id] = setTimeout(() => {
      const queueItem = updateQueue.current[id];
      if (queueItem) {
        console.log(`Executing cart item #${id} quantity update, final quantity: ${queueItem.quantity}`);
        dispatch(updateCartItem({ id, quantity: queueItem.quantity }));
        // Remove from queue after execution
        delete updateQueue.current[id];
      }
    }, 500); // Reduce delay for better responsiveness
  }, [dispatch]);

  return {
    initializeLocalQuantities,
    getLocalQuantity,
    updateLocalQuantity
  };
}

const CartPage = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const location = useLocation(); // Get location object
  const query = useQuery();
  const cartId = query.get('cart_id');
  const { isAuthenticated, loading: authLoading } = useSelector((state: RootState) => state.auth);
  
  // Get cart data from Redux
  const { 
    carts,
    activeCartId,
    loading: cartLoading, 
    error
  } = useAppSelector(state => state.cart);
  
  // Local state
  const [showRemoveConfirm, setShowRemoveConfirm] = useState(false);
  const [selectedCartId, setSelectedCartId] = useState<number | null>(activeCartId);
  const [showExitConfirm, setShowExitConfirm] = useState(false);
  const [exitLoading, setExitLoading] = useState(false);
  const [animationComplete, setAnimationComplete] = useState(false);
  const [itemToRemove, setItemToRemove] = useState<number | null>(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [cartToDelete, setCartToDelete] = useState<number | null>(null);
  const [viewMode, setViewMode] = useState<'list' | 'detail'>('list');
  const [selectedItems, setSelectedItems] = useState<number[]>([]);
  const [isRemoving, setIsRemoving] = useState(false);
  const [updatingItemId, setUpdatingItemId] = useState<number | null>(null);
  
  // Add local cart state for immediate UI updates
  const [localCarts, setLocalCarts] = useState<typeof carts>([]);
  
  // Animation controllers
  const controls = useAnimation();
  const cartIconControls = useAnimation();
  
  // Create cart quantity manager
  const { 
    initializeLocalQuantities, 
  } = useCartQuantityManager(); // Removed unused quantity manager functions for now
  
  // Sync Redux cart state to local state to keep both consistent
  useEffect(() => {
    if (!cartLoading && carts.length > 0) {
      setLocalCarts(carts);
    }
  }, [carts, cartLoading]);
  
  // Loop pulse animation
  useEffect(() => {
    const startPulseAnimation = async () => {
      await cartIconControls.start({
        scale: [1, 1.1, 1],
        transition: { duration: 2, repeat: Infinity, repeatType: "loop" } // Use loop instead of reverse
      });
    };
    
    startPulseAnimation();

    // Cleanup function to stop animation when component unmounts
    return () => {
       cartIconControls.stop();
    };
  }, [cartIconControls]);

  // Get cart data when component loads
  useEffect(() => {
    console.log("[CartPage Effect - Fetch Data] Running. cartId:", cartId, "activeCartId:", activeCartId);
    if (cartId) {
      const cartIdNum = parseInt(cartId);
      if (!isNaN(cartIdNum)) {
        console.log("[CartPage Effect - Fetch Data] Fetching specific cart:", cartIdNum);
        dispatch(fetchCart({ cart_id: cartIdNum }));
        // Avoid setting state directly based on URL param here, let the next effect handle it
        // setSelectedCartId(cartIdNum);
        setViewMode('detail');
      } else {
        console.log("[CartPage Effect - Fetch Data] Invalid cartId in URL, fetching default.");
        dispatch(fetchCart({}));
        // setSelectedCartId(activeCartId);
        setViewMode('list');
        navigate('/cart', { replace: true });
      }
    } else {
      console.log("[CartPage Effect - Fetch Data] No cartId in URL, fetching default.");
      dispatch(fetchCart({}));
      // setSelectedCartId(activeCartId);
      setViewMode('list');
    }
  }, [dispatch, cartId, navigate /* Removed activeCartId here */]);

  // Add new useEffect to specifically handle resetting confirmation dialog states when route changes
  useEffect(() => {
    // Any route or view mode change resets confirmation dialog states
    setShowRemoveConfirm(false);
    setShowExitConfirm(false);
  }, [cartId, viewMode]);

  // Validate selectedCartId & viewMode based on fetched data and URL
  useEffect(() => {
    console.log("[CartPage Effect - Validate Selection] Running. cartLoading:", cartLoading, "Carts:", carts, "URL cartId:", cartId, "Current selectedCartId:", selectedCartId);
    if (!cartLoading && carts) { 
      let newSelectedId: number | null = null;
      let newViewMode: 'list' | 'detail' = viewMode; // Start with current view mode
      const cartsExist = carts.length > 0;
      const urlCartIdNum = cartId ? parseInt(cartId) : NaN;
      const isValidUrlCartId = !isNaN(urlCartIdNum);

      if (isValidUrlCartId) {
          const urlCartExists = carts.some(cart => cart.id === urlCartIdNum);
          if (urlCartExists) {
              console.log("[CartPage Effect - Validate Selection] URL cart ID is valid and exists:", urlCartIdNum);
              newSelectedId = urlCartIdNum;
              newViewMode = 'detail'; // If URL specifies a valid cart, default to detail view
          } else {
              // URL specifies a cart ID that doesn't exist in the fetched data
              console.log("[CartPage Effect - Validate Selection] URL cart ID not found in fetched carts:", urlCartIdNum);
              newSelectedId = activeCartId && carts.some(c => c.id === activeCartId) ? activeCartId : (cartsExist ? carts[0].id : null);
              newViewMode = 'list'; // Fallback to list view
              console.log("[CartPage Effect - Validate Selection] Fallback selectedId:", newSelectedId);
              // Update URL to reflect the actual state (list view or the fallback cart id)
              if (newSelectedId) {
                 // Maybe navigate to the fallback cart's detail? Or just list?
                 // Let's navigate to list for now to avoid confusion
                 if (location.pathname !== '/cart' || location.search !== '') {
                    console.log("[CartPage Effect - Validate Selection] Navigating to /cart (list view) due to invalid URL cartId");
                    navigate('/cart', { replace: true });
                 }
              } else {
                 // No fallback cart, ensure we are in list view
                 if (location.pathname !== '/cart' || location.search !== '') {
                    console.log("[CartPage Effect - Validate Selection] Navigating to /cart (list view) as no carts exist");
                    navigate('/cart', { replace: true });
                 }
              }
          }
      } else {
          // No cart ID in URL, or it's invalid
          console.log("[CartPage Effect - Validate Selection] No valid cartId in URL.");
          newSelectedId = activeCartId && carts.some(c => c.id === activeCartId) ? activeCartId : (cartsExist ? carts[0].id : null);
          newViewMode = 'list'; // Default to list view if no specific cart is requested via URL
          console.log("[CartPage Effect - Validate Selection] Default selectedId:", newSelectedId);
      }

      // Update state only if changed
      if (selectedCartId !== newSelectedId) {
        console.log("[CartPage Effect - Validate Selection] Updating selectedCartId from", selectedCartId, "to", newSelectedId);
        setSelectedCartId(newSelectedId);
        setSelectedItems([]); // Reset selection when cart ID changes
      }
      if (viewMode !== newViewMode) {
         console.log("[CartPage Effect - Validate Selection] Updating viewMode from", viewMode, "to", newViewMode);
         setViewMode(newViewMode);
      }

    } else if (!cartLoading && !carts) {
      // Carts finished loading but result is null/undefined (API error?)
      console.log("[CartPage Effect - Validate Selection] Carts finished loading but are null/undefined.");
      if (selectedCartId !== null) setSelectedCartId(null);
      if (viewMode !== 'list') setViewMode('list');
      setSelectedItems([]);
    }
  }, [carts, cartLoading, cartId, activeCartId, navigate, selectedCartId, viewMode, location.pathname, location.search]); // Added location dependencies

  // Initialize quantities useEffect
  useEffect(() => {
    // Renamed variable to avoid conflict
    const cartForQuantities = selectedCartId !== null ? carts?.find(cart => cart.id === selectedCartId) : null;
    // FIX: Check if cartForQuantities and its items exist
    if (cartForQuantities && cartForQuantities.items && cartForQuantities.items.length > 0) { 
      initializeLocalQuantities(cartForQuantities.items);
    }
  }, [carts, selectedCartId, initializeLocalQuantities]);

  // Check auth useEffect
  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      console.log("[CartPage Effect - Auth Check] User not authenticated, redirecting to login.");
      navigate('/login?returnUrl=' + encodeURIComponent(location.pathname + location.search), { replace: true });
    }
  }, [isAuthenticated, authLoading, navigate, location.pathname, location.search]);

  // Get current selected cart - prioritize local state for immediate feedback
  
  // Handle select cart
  const handleSelectCart = (cartId: number) => {
    console.log("[CartPage Handler] handleSelectCart called with ID:", cartId);
    navigate(`/cart?cart_id=${cartId}`); 
  };
  
  // Return to cart list
  const handleBackToList = () => {
    console.log("[CartPage Handler] handleBackToList called.");
    // No need to set viewMode here, let the useEffect handle it via navigate
    navigate('/cart'); // Let the URL drive the state
  };

  // Handle quantity change - 使用 updatingItemId
  const handleQuantityChange = (id: number, quantity: number) => {
    console.log(`[CartPage Handler] Quantity change for item ${id} to ${quantity}`);
    setUpdatingItemId(id); // 设置正在更新的商品 ID
    dispatch(updateCartItem({ id, quantity }))
        .unwrap() // 使用 unwrap 处理 Promise
        .catch((error) => {
            console.error("Failed to update item:", error);
            toast.error("更新商品数量失败"); // 提示错误
        })
        .finally(() => {
            setUpdatingItemId(null); // 更新完成后清除 ID
        });
  };
  
  // Handle remove cart item - 保持不变
  const handleRemoveItem = (id: number) => {
    console.log(`[CartPage Handler] Removing item ${id}`);
    setIsRemoving(true);
    dispatch(removeFromCart(id))
        .unwrap()
        .then(() => toast.success("商品已移除")) // Translate
        .catch(() => toast.error("移除商品失败")) // Translate
        .finally(() => setIsRemoving(false));
    setSelectedItems(prev => prev.filter(itemId => itemId !== id));
  };
  
  // --- NEW: Handle item selection ---
  const handleSelectItem = (itemId: number, isSelected: boolean) => {
      console.log(`[CartPage Handler] Item ${itemId} selection changed to ${isSelected}`);
      setSelectedItems(prevSelected => {
          if (isSelected) {
              return prevSelected.includes(itemId) ? prevSelected : [...prevSelected, itemId];
          } else {
              return prevSelected.filter(id => id !== itemId);
          }
      });
  };
  // --- END NEW ---
  
  // Handle checkout
  const handleCheckout = (checkoutSelectedItems: number[] = selectedItems) => {
    console.log("[CartPage Handler] handleCheckout called. Selected items:", checkoutSelectedItems);
    const cartToCheckoutId = selectedCartId;
    if (!cartToCheckoutId) {
        toast.error("无法结账，请先选择一个购物车。");
        return;
    }
    let checkoutUrl = `/checkout?cart_id=${cartToCheckoutId}`;
    if (checkoutSelectedItems.length > 0) {
        const itemParams = checkoutSelectedItems.map(id => `items[]=${id}`);
        checkoutUrl += '&' + itemParams.join('&');
        console.log(`导航到结账页面，包含选定商品，购物车ID: ${cartToCheckoutId}`);
    } else {
         console.log(`导航到结账页面，包含整个购物车，购物车ID: ${cartToCheckoutId}`);
    }
    navigate(checkoutUrl);
  };
  
  // Handle create cart
  const handleCreateCart = async (data: { name: string; type: string; is_default: boolean }) => {
    console.log("[CartPage Handler] handleCreateCart called with data:", data);
    try {
      await dispatch(createCart(data)).unwrap();
      toast.success('购物车创建成功');
      // Fetching might not be needed if slice updates state correctly
      // dispatch(fetchCart({})); 
    } catch (error: any) {
      const message = error?.message || '创建购物车失败';
      toast.error(message);
      // throw error; // Avoid re-throwing unless needed upstream
    }
  };
  
  // Handle update cart
  const handleUpdateCart = async (id: number, data: { name: string; type: string; is_default: boolean }) => {
    console.log(`[CartPage Handler] handleUpdateCart called for ID ${id} with data:`, data);
    try {
      await dispatch(updateCart({id, data})).unwrap();
      toast.success('购物车更新成功');
      if (data.is_default) {
          // If setting as default, also update activeCartId in the store locally
          dispatch(setActiveCart(id));
      }
    } catch (error: any) {
      const message = error?.message || '更新购物车失败';
      toast.error(message);
    }
  };
  
  // Handle delete cart
  const handleDeleteCart = async (id: number) => {
    setCartToDelete(id);
    setShowDeleteConfirm(true);
  };
  
  // Confirm delete cart
  const confirmDeleteCart = async () => {
    if (cartToDelete === null) return;

    try {
      // 传递对象 { id }
      await dispatch(deleteCart({ id: cartToDelete })).unwrap();
      toast.success('购物车已删除');
      // 如果删除的是当前查看的购物车，返回列表视图
      if (selectedCartId === cartToDelete) {
        setSelectedCartId(null); // Deselect
        setViewMode('list'); // Go back to list
        navigate('/cart', { replace: true }); // Update URL
      }
    } catch (err) {
      toast.error(`删除购物车失败: ${err instanceof Error ? err.message : '未知错误'}`);
    } finally {
      setCartToDelete(null);
      setShowDeleteConfirm(false);
    }
  };
  
  // Handle set active cart
  const handleSetActiveCart = (id: number) => {
    console.log("[CartPage Handler] handleSetActiveCart called for ID:", id);
    // This might be better handled inside handleUpdateCart when is_default is true
    dispatch(setActiveCart(id));
    // Update the cart on the backend to set it as default
    handleUpdateCart(id, { name: carts?.find(c=>c.id === id)?.name || '', type: carts?.find(c=>c.id === id)?.type || 'default', is_default: true });
    toast.success('默认购物车已设置');
  };
  
  // Confirm remove item
  const confirmRemoveItem = () => {
    console.log("[CartPage Handler] confirmRemoveItem called.");
    if (itemToRemove !== null) {
      handleRemoveItem(itemToRemove);
    }
    setShowRemoveConfirm(false);
    setItemToRemove(null);
  };
  
  // Handle exit cart
  const handleExitCart = () => {
    console.log("[CartPage Handler] handleExitCart called.");
    setExitLoading(true);
    setTimeout(() => {
      setExitLoading(false);
      setShowExitConfirm(false);
      handleBackToList(); // Navigate back to list view
      toast.info('已退出当前购物车');
    }, 300); // Shorter delay
  };
  
  // Empty cart view
  const EmptyCartView = () => (
    <motion.div 
      className="text-center py-12"
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
    >
      <div className="w-24 h-24 mx-auto mb-6 bg-blue-50 rounded-full flex items-center justify-center">
        <motion.svg
          animate={cartIconControls}
          className="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </motion.svg>
      </div>
      <h2 className="text-2xl font-bold text-gray-800 mb-2">你的购物车是空的</h2>
      <p className="text-gray-600 mb-8">看起来你还没有添加任何商品到购物车。</p>
      <Link 
        to="/products"
        className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition transform hover:-translate-y-1 duration-300 ease-in-out"
      >
        <svg className="-ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
        开始购物
      </Link>
    </motion.div>
  );
  
  // --- NEW: Define handleClearCart --- 
  const handleClearCart = useCallback(async () => {
    if (!selectedCartId) {
       toast.error("没有选择购物车，无法清空。");
       return;
    }
    console.log(`[CartPage Handler] Clearing cart ${selectedCartId}`);
    // Consider adding a confirmation dialog here
    try {
      // FIX: Call clearCart WITHOUT arguments
      await dispatch(clearCart()).unwrap(); // <--- 确保这里没有参数 (selectedCartId)
      toast.success('购物车已成功清空');
      setSelectedItems([]);
    } catch (err: any) {
        toast.error(`清空购物车失败: ${err.message || '未知错误'}`);
    }
  }, [dispatch, selectedCartId]);
  // --- END NEW ---
  
  console.log("[CartPage Render] Starting render. Auth:", isAuthenticated, "Cart Loading:", cartLoading, "Error:", error, "Carts:", carts);

  if (!isAuthenticated) {
    console.log("[CartPage Render] Not authenticated, rendering LoadingSpinner.");
    return <LoadingSpinner />; 
  }

  // 1. Handle Loading State (只在初始加载时显示骨架屏)
  if (cartLoading && (!carts || carts.length === 0)) {
    console.log("[CartPage Render] Initial cart loading, rendering Cart Skeletons.");
    return (
        <div className="container mx-auto px-4 py-8">
            <div className="space-y-4">
                <CartItemSkeleton />
                <CartItemSkeleton />
                <CartItemSkeleton />
            </div>
        </div>
    );
  }

  // 2. Handle Error State
  if (error) {
    console.error("[CartPage Render] Error state, rendering error message:", error);
    return <div className="text-red-500 p-4">加载购物车时出错: {error}</div>;
  }
  
  // 3. Handle Empty Cart State (after loading & no error)
  if (!carts || carts.length === 0) {
    console.log("[CartPage Render] Carts loaded but empty, rendering EmptyCartView.");
    return <EmptyCartView />; 
  }

  // 4. Carts Loaded - Define current/active cart data for IntegratedCartView HERE
  // This is the correct place for this declaration
  const currentCart = selectedCartId !== null ? carts.find(cart => cart.id === selectedCartId) : null;
  const activeCartData = activeCartId !== null ? carts.find(cart => cart.id === activeCartId) : null;
  console.log("[CartPage Render] Carts loaded. ViewMode:", viewMode, "SelectedCartId:", selectedCartId, "Current Cart Data:", currentCart);

  // --- Main Render for IntegratedCartView (List or Detail) ---
  console.log(`[CartPage Render] Rendering IntegratedCartView in '${viewMode}' mode.`);
  return (
    <div className="container mx-auto px-4 py-8"> 
      <IntegratedCartView
        carts={carts} 
        activeCartId={activeCartId}
        // Use the correctly scoped currentCart, ensure it's Cart | null
        currentCart={currentCart || null} 
        // Ensure activeCart prop is Cart | null
        activeCart={activeCartData || null} // FIX: Add || null here
        onSelectCart={handleSelectCart}
        onCreateCart={handleCreateCart}
        onUpdateCart={handleUpdateCart}
        onDeleteCart={handleDeleteCart}
        onSetActiveCart={handleSetActiveCart}
        onBackToList={handleBackToList}
        currentView={viewMode} 
        cartLoading={false} 
        onQuantityChange={handleQuantityChange}
        onRemoveItem={(id: number) => { setItemToRemove(id); setShowRemoveConfirm(true); }}
        onCheckout={handleCheckout}
        loading={isRemoving || updatingItemId !== null}
        selectedItems={selectedItems}
        onSelectItem={handleSelectItem}
        onClearCart={handleClearCart}
        updatingItemId={updatingItemId}
      />

      {/* Render Dialogs */} 
      <ConfirmDialog
        isOpen={showRemoveConfirm}
        onConfirm={confirmRemoveItem}
        onCancel={() => { setShowRemoveConfirm(false); setItemToRemove(null); }}
        title="移除商品"
        message="你确定要从购物车中移除此商品吗？"
        confirmText="移除"
      />

      <ConfirmDialog
        isOpen={showExitConfirm}
        onConfirm={handleExitCart}
        onCancel={() => setShowExitConfirm(false)} 
        title="退出购物车"
        message="你确定要退出当前购物车视图吗？"
        confirmText={exitLoading ? '正在退出...' : '退出'}
      />

      {/* 删除购物车确认对话框 */}
      <ConfirmDialog
        isOpen={showDeleteConfirm}
        onCancel={() => { 
          setShowDeleteConfirm(false);
          setCartToDelete(null);
        }}
        onConfirm={confirmDeleteCart}
        title="确认删除购物车"
        message="确定要删除此购物车吗？此操作不可撤销。"
        confirmText="删除"
      />
    </div>
  );
};

export default CartPage; 