import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Cart } from '../../store/slices/cartSlice';
import { 
  PlusIcon, 
  ShoppingBagIcon, 
  HeartIcon, 
  BookmarkIcon,
  PencilIcon,
  XMarkIcon,
  CheckIcon,
  TrashIcon,
  ArrowLeftIcon,
  EllipsisVerticalIcon,
  QuestionMarkCircleIcon
} from '@heroicons/react/24/outline';
import CartDetails from './CartDetails';
import LoadingSpinner from '../LoadingSpinner';
import SkeletonLoader from '../animations/SkeletonLoader';
import AnimatedElement from '../animations/AnimatedElement';
import CartTutorial from './CartTutorial';

interface IntegratedCartViewProps {
  carts: Cart[];
  activeCartId: number | null;
  onSelectCart: (cartId: number) => void;
  onCreateCart: (data: { name: string; type: string; is_default: boolean }) => Promise<void>;
  onUpdateCart: (id: number, data: { name: string; type: string; is_default: boolean }) => Promise<void>;
  onDeleteCart: (id: number) => Promise<void>;
  onSetActiveCart: (id: number) => void;
  onBackToList: () => void;
  loading: boolean;
  currentView: 'list' | 'detail';
  cartLoading: boolean;
  currentCart: Cart | null;
  activeCart: Cart | null;
  onQuantityChange: (id: number, quantity: number) => void;
  onRemoveItem: (id: number) => void;
  onClearCart?: () => void;
  onCheckout: (selectedItems?: number[]) => void;
  selectedItems: number[];
  onSelectItem: (itemId: number, isSelected: boolean) => void;
  updatingItemId: number | null;
}

const cartTypes = [
  { id: 'default', name: 'Shopping Cart', icon: ShoppingBagIcon, color: 'blue' },
  { id: 'wishlist', name: 'Wishlist', icon: HeartIcon, color: 'pink' },
  { id: 'saveforlater', name: 'Save for Later', icon: BookmarkIcon, color: 'purple' }
];

// 动画变体
const containerVariants = {
  hidden: { opacity: 0 },
  visible: { 
    opacity: 1,
    transition: { 
      staggerChildren: 0.1,
      delayChildren: 0.2
    }
  },
  exit: { 
    opacity: 0,
    transition: { 
      staggerChildren: 0.05,
      staggerDirection: -1
    }
  }
};

const itemVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0 },
  exit: { opacity: 0, y: -10 }
};

const modalVariants = {
  hidden: { opacity: 0, scale: 0.9 },
  visible: { 
    opacity: 1, 
    scale: 1,
    transition: {
      type: "spring",
      damping: 25,
      stiffness: 500
    }
  },
  exit: { 
    opacity: 0, 
    scale: 0.9,
    transition: {
      duration: 0.2
    }
  }
};

const overlayVariants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1 },
  exit: { opacity: 0 }
};

const IntegratedCartView: React.FC<IntegratedCartViewProps> = ({
  carts,
  activeCartId,
  onSelectCart,
  onCreateCart,
  onUpdateCart,
  onDeleteCart,
  onSetActiveCart,
  onBackToList,
  loading,
  currentView,
  cartLoading,
  currentCart,
  activeCart,
  onQuantityChange,
  onRemoveItem,
  onClearCart,
  onCheckout,
  selectedItems,
  onSelectItem,
  updatingItemId
}) => {
  // 本地状态
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [newCartName, setNewCartName] = useState('');
  const [newCartType, setNewCartType] = useState('default');
  const [isDefault, setIsDefault] = useState(false);
  const [editCartId, setEditCartId] = useState<number | null>(null);
  const [editCartName, setEditCartName] = useState('');
  const [editCartType, setEditCartType] = useState('');
  const [editIsDefault, setEditIsDefault] = useState(false);
  const [loadingAction, setLoadingAction] = useState(false);
  const [menuOpenCartId, setMenuOpenCartId] = useState<number | null>(null);
  const [hoverCartId, setHoverCartId] = useState<number | null>(null);
  const [showTutorial, setShowTutorial] = useState(false);

  // 处理创建购物车
  const handleCreateCart = async () => {
    if (!newCartName.trim()) return;
    
    setLoadingAction(true);
    try {
      await onCreateCart({
        name: newCartName.trim(),
        type: 'cart',
        is_default: isDefault
      });
      
      // 重置表单
      setNewCartName('');
      setNewCartType('cart');
      setIsDefault(false);
      setShowCreateModal(false);
    } catch (error) {
      console.error(error);
    } finally {
      setLoadingAction(false);
    }
  };

  // 打开编辑购物车模态框
  const handleOpenEditModal = (cart: Cart) => {
    setEditCartId(cart.id);
    setEditCartName(cart.name);
    setEditCartType(cart.type);
    setEditIsDefault(cart.is_default);
    setShowEditModal(true);
    setMenuOpenCartId(null); // 关闭菜单
  };

  // 处理更新购物车
  const handleUpdateCart = async () => {
    if (!editCartName.trim() || !editCartId) return;
    
    setLoadingAction(true);
    try {
      await onUpdateCart(editCartId, {
        name: editCartName.trim(),
        type: editCartType,
        is_default: editIsDefault
      });
      
      // 重置表单
      setEditCartId(null);
      setEditCartName('');
      setEditCartType('');
      setEditIsDefault(false);
      setShowEditModal(false);
    } catch (error) {
      console.error(error);
    } finally {
      setLoadingAction(false);
    }
  };

  // 处理删除购物车
  const handleDeleteCart = async (id: number) => {
    if (!window.confirm('Are you sure you want to delete this cart? All items in the cart will be deleted.')) {
      return;
    }
    
    setLoadingAction(true);
    try {
      await onDeleteCart(id);
      setMenuOpenCartId(null); // 关闭菜单
    } catch (error) {
      console.error(error);
    } finally {
      setLoadingAction(false);
    }
  };

  // 根据购物车类型获取图标和颜色
  const getCartTypeInfo = (type: string) => {
    const cartType = cartTypes.find(t => t.id === type) || cartTypes[0];
    return {
      icon: cartType.icon,
      name: cartType.name,
      color: cartType.color
    };
  };

  // 获取背景颜色
  const getBackgroundColor = (type: string, isHovered: boolean) => {
    const colorMap: Record<string, string> = {
      'blue': isHovered ? 'bg-blue-50' : 'bg-blue-25',
      'pink': isHovered ? 'bg-pink-50' : 'bg-pink-25',
      'purple': isHovered ? 'bg-purple-50' : 'bg-purple-25'
    };
    
    const info = getCartTypeInfo(type);
    return colorMap[info.color] || colorMap.blue;
  };

  // 获取图标颜色
  const getIconColor = (type: string) => {
    const colorMap: Record<string, string> = {
      'blue': 'text-blue-600',
      'pink': 'text-pink-600',
      'purple': 'text-purple-600'
    };
    
    const info = getCartTypeInfo(type);
    return colorMap[info.color] || colorMap.blue;
  };

  // 获取边框颜色
  const getBorderColor = (type: string, isHovered: boolean) => {
    const colorMap: Record<string, string> = {
      'blue': isHovered ? 'border-blue-200' : 'border-blue-100',
      'pink': isHovered ? 'border-pink-200' : 'border-pink-100',
      'purple': isHovered ? 'border-purple-200' : 'border-purple-100'
    };
    
    const info = getCartTypeInfo(type);
    return colorMap[info.color] || colorMap.blue;
  };

  // 购物车列表视图
  const renderCartList = () => (
    <motion.div
      key="cart-list"
      variants={containerVariants}
      initial="hidden"
      animate="visible"
      exit="exit"
      className="w-full"
    >
      <div className="flex justify-between items-center mb-8">
        <div className="flex items-center">
          <motion.h1 
            className="text-2xl font-medium text-gray-800"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
          >
            My Shopping Cart
          </motion.h1>
          
          <motion.button
            className="ml-2 text-gray-400 hover:text-gray-600 focus:outline-none"
            initial={{ opacity: 0, scale: 0 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 0.3 }}
            onClick={() => setShowTutorial(true)}
            whileHover={{ scale: 1.1 }}
            whileTap={{ scale: 0.9 }}
            title="View Shopping Cart Tutorial"
          >
            <QuestionMarkCircleIcon className="w-5 h-5" />
          </motion.button>
        </div>
        
        <motion.button
          className="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-full text-sm font-medium flex items-center transition-all duration-300 shadow-sm hover:shadow"
          onClick={() => setShowCreateModal(true)}
          whileHover={{ scale: 1.05 }}
          whileTap={{ scale: 0.95 }}
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
        >
          <PlusIcon className="w-4 h-4 mr-1" />
          Create Shopping Cart
        </motion.button>
      </div>

      {loading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <SkeletonLoader count={3} type="cart-item" height="250px" />
        </div>
      ) : carts.length === 0 ? (
        <motion.div 
          className="bg-white rounded-2xl shadow-sm p-8 text-center border border-gray-100"
          variants={itemVariants}
        >
          <div className="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <ShoppingBagIcon className="w-10 h-10 text-gray-400" />
          </div>
          <h2 className="text-xl font-medium text-gray-800 mb-2">No shopping cart</h2>
          <p className="text-gray-500 mb-6">Create a new shopping cart to start shopping</p>
          <motion.button
            className="bg-gray-900 hover:bg-gray-800 text-white px-6 py-2 rounded-full text-sm font-medium inline-flex items-center transition-all duration-300"
            onClick={() => setShowCreateModal(true)}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            <PlusIcon className="w-4 h-4 mr-1" />
            Create a shopping cart
          </motion.button>
        </motion.div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {carts.map((cart, index) => {
            const typeInfo = getCartTypeInfo(cart.type);
            const IconComponent = typeInfo.icon;
            const isHovered = hoverCartId === cart.id;
            
            return (
              <AnimatedElement
                key={cart.id}
                type="slide-up"
                options={{ 
                  delay: index * 0.1,
                  duration: 0.5
                }}
              >
                <motion.div
                  variants={itemVariants}
                  className={`relative bg-white rounded-2xl border overflow-hidden transition-all duration-300 ${getBorderColor(cart.type, isHovered)}`}
                  onMouseEnter={() => setHoverCartId(cart.id)}
                  onMouseLeave={() => setHoverCartId(null)}
                  whileHover={{ 
                    y: -5, 
                    boxShadow: "0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)"
                  }}
                >
                  {/* 购物车菜单按钮 */}
                  <div className="absolute top-4 right-4 z-10">
                    <button 
                      className="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors duration-200"
                      onClick={(e) => {
                        e.stopPropagation();
                        setMenuOpenCartId(menuOpenCartId === cart.id ? null : cart.id);
                      }}
                    >
                      <EllipsisVerticalIcon className="w-4 h-4" />
                    </button>
                    
                    {/* 菜单弹出 */}
                    <AnimatePresence>
                      {menuOpenCartId === cart.id && (
                        <motion.div 
                          className="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-100"
                          initial={{ opacity: 0, y: -10 }}
                          animate={{ opacity: 1, y: 0 }}
                          exit={{ opacity: 0, y: -10 }}
                          transition={{ duration: 0.2 }}
                        >
                          <button 
                            className="w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 flex items-center"
                            onClick={(e) => {
                              e.stopPropagation();
                              handleOpenEditModal(cart);
                            }}
                          >
                            <PencilIcon className="w-4 h-4 mr-2 text-gray-500" />
                            Edit shopping cart
                          </button>
                          
                          {!cart.is_default && (
                            <button 
                              className="w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 flex items-center"
                              onClick={(e) => {
                                e.stopPropagation();
                                onSetActiveCart(cart.id);
                                setMenuOpenCartId(null);
                              }}
                            >
                              <CheckIcon className="w-4 h-4 mr-2 text-gray-500" />
                              Set as default
                            </button>
                          )}
                          
                          <button 
                            className="w-full px-4 py-2 text-sm text-left text-red-600 hover:bg-red-50 flex items-center"
                            onClick={(e) => {
                              e.stopPropagation();
                              handleDeleteCart(cart.id);
                            }}
                          >
                            <TrashIcon className="w-4 h-4 mr-2" />
                            Delete the shopping cart
                          </button>
                        </motion.div>
                      )}
                    </AnimatePresence>
                  </div>
                  
                  {cart.is_default && (
                    <div className="absolute top-0 left-0">
                      <div className="bg-blue-600 text-white text-xs px-2 py-1 rounded-br-md">
                        默认
                      </div>
                    </div>
                  )}
                  
                  {/* 购物车内容 - 可点击区域 */}
                  <div 
                    className="p-6 cursor-pointer"
                    onClick={() => onSelectCart(cart.id)}
                  >
                    <div className="flex items-start mb-4">
                      <div className={`w-12 h-12 rounded-full flex items-center justify-center ${getBackgroundColor(cart.type, isHovered)}`}>
                        <IconComponent className={`w-6 h-6 ${getIconColor(cart.type)}`} />
                      </div>
                      <div className="ml-4">
                        <h3 className="text-lg font-medium text-gray-800">
                          {cart.name}
                        </h3>
                        <p className="text-sm text-gray-500">
                          {typeInfo.name}
                        </p>
                      </div>
                    </div>
                    
                    <div className="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                      <div className="text-sm text-gray-600">
                        {cart.item_count || 0} Product
                      </div>
                      <div className="text-sm font-medium text-gray-900">
                        ${(parseFloat(String(cart.total || 0))).toFixed(2)}
                      </div>
                    </div>
                    
                    <motion.button
                      className="mt-4 w-full bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-full text-sm font-medium transition-colors duration-300"
                      whileHover={{ scale: 1.02 }}
                      whileTap={{ scale: 0.98 }}
                      onClick={(e) => {
                        e.stopPropagation();
                        onSelectCart(cart.id);
                      }}
                    >
                      check the details
                    </motion.button>
                  </div>
                </motion.div>
              </AnimatedElement>
            );
          })}
          
          {/* 创建新购物车卡片 */}
          <AnimatedElement
            type="slide-up"
            options={{ 
              delay: carts.length * 0.1 + 0.1,
              duration: 0.5
            }}
          >
            <motion.div
              variants={itemVariants}
              className="bg-gray-50 rounded-2xl border border-dashed border-gray-200 flex flex-col items-center justify-center p-6 cursor-pointer min-h-[250px]"
              onClick={() => setShowCreateModal(true)}
              whileHover={{ 
                y: -5, 
                boxShadow: "0 10px 25px -5px rgba(0, 0, 0, 0.05)",
                backgroundColor: "#f9fafb"
              }}
            >
              <div className="w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                <PlusIcon className="w-6 h-6 text-gray-500" />
              </div>
              <h3 className="text-base font-medium text-gray-800 mb-1">Create a new shopping cart</h3>
              <p className="text-sm text-gray-500 text-center mb-4">
                Create exclusive shopping carts for different needs
              </p>
            </motion.div>
          </AnimatedElement>
        </div>
      )}
      
      {/* 创建购物车模态框 */}
      <AnimatePresence>
        {showCreateModal && (
          <div className="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center">
            <motion.div 
              className="fixed inset-0 bg-black bg-opacity-40"
              variants={overlayVariants}
              initial="hidden"
              animate="visible"
              exit="exit"
              onClick={() => setShowCreateModal(false)}
            />
            
            <motion.div
              className="bg-white rounded-xl w-full max-w-md mx-4 z-10 overflow-hidden shadow-xl"
              variants={modalVariants}
              initial="hidden"
              animate="visible"
              exit="exit"
            >
              <div className="px-6 py-4 border-b border-gray-100">
                <h3 className="text-lg font-medium text-gray-800">Create a new shopping cart</h3>
              </div>
              
              <div className="p-6">
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Shopping cart name
                  </label>
                  <input
                    type="text"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="For example: daily shopping, quarterly restocking"
                    value={newCartName}
                    onChange={(e) => setNewCartName(e.target.value)}
                  />
                </div>
                
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Shopping cart type
                  </label>
                  <select
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    value={newCartType}
                    onChange={(e) => setNewCartType(e.target.value)}
                  >
                    {cartTypes.map(type => (
                      <option key={type.id} value={type.id}>
                        {type.name}
                      </option>
                    ))}
                  </select>
                </div>
                
                <div className="mb-6">
                  <label className="flex items-center">
                    <input
                      type="checkbox"
                      className="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      checked={isDefault}
                      onChange={(e) => setIsDefault(e.target.checked)}
                    />
                    <span className="ml-2 text-sm text-gray-700">Set as default shopping cart</span>
                  </label>
                </div>
                
                <div className="flex justify-end space-x-3">
                  <button
                    className="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-200"
                    onClick={() => setShowCreateModal(false)}
                    disabled={loadingAction}
                  >
                    取消
                  </button>
                  <button
                    className="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    onClick={handleCreateCart}
                    disabled={loadingAction || !newCartName.trim()}
                  >
                    {loadingAction ? (
                      <>
                        <LoadingSpinner size="small" className="mr-2" />
                        Creating...
                      </>
                    ) : (
                      <>Create a shopping cart</>
                    )}
                  </button>
                </div>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
      
      {/* 编辑购物车模态框 */}
      <AnimatePresence>
        {showEditModal && (
          <div className="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center">
            <motion.div 
              className="fixed inset-0 bg-black bg-opacity-40"
              variants={overlayVariants}
              initial="hidden"
              animate="visible"
              exit="exit"
              onClick={() => setShowEditModal(false)}
            />
            
            <motion.div
              className="bg-white rounded-xl w-full max-w-md mx-4 z-10 overflow-hidden shadow-xl"
              variants={modalVariants}
              initial="hidden"
              animate="visible"
              exit="exit"
            >
              <div className="px-6 py-4 border-b border-gray-100">
                <h3 className="text-lg font-medium text-gray-800">Edit shopping cart</h3>
              </div>
              
              <div className="p-6">
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Shopping cart name
                  </label>
                  <input
                    type="text"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="例如：日常购物、季度补货"
                    value={editCartName}
                    onChange={(e) => setEditCartName(e.target.value)}
                  />
                </div>
                
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Shopping cart type
                  </label>
                  <select
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    value={editCartType}
                    onChange={(e) => setEditCartType(e.target.value)}
                  >
                    {cartTypes.map(type => (
                      <option key={type.id} value={type.id}>
                        {type.name}
                      </option>
                    ))}
                  </select>
                </div>
                
                <div className="mb-6">
                  <label className="flex items-center">
                    <input
                      type="checkbox"
                      className="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      checked={editIsDefault}
                      onChange={(e) => setEditIsDefault(e.target.checked)}
                    />
                    <span className="ml-2 text-sm text-gray-700">Set as default shopping cart</span>
                  </label>
                </div>
                
                <div className="flex justify-end space-x-3">
                  <button
                    className="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-200"
                    onClick={() => setShowEditModal(false)}
                    disabled={loadingAction}
                  >
                    取消
                  </button>
                  <button
                    className="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    onClick={handleUpdateCart}
                    disabled={loadingAction || !editCartName.trim()}
                  >
                    {loadingAction ? (
                      <>
                        <LoadingSpinner size="small" className="mr-2" />
                        Updating...
                      </>
                    ) : (
                      <>Save changes</>
                    )}
                  </button>
                </div>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
      
      {/* 购物车教程 */}
      {showTutorial && (
        <CartTutorial onClose={() => setShowTutorial(false)} />
      )}
    </motion.div>
  );

  return (
    <AnimatePresence mode="wait">
      {currentView === 'list' ? (
        renderCartList()
      ) : (
        <motion.div
          key="cart-details"
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          exit={{ opacity: 0, x: -20 }}
          transition={{ duration: 0.3 }}
        >
          {currentCart && (
            <div className="rounded-lg overflow-hidden relative">
              <motion.button
                className="absolute top-0 left-0 p-2 m-4 z-10 bg-white rounded-full shadow-md flex items-center justify-center group hover:bg-gray-100 transition-colors duration-200"
                onClick={onBackToList}
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                aria-label="Back to cart list"
              >
                <ArrowLeftIcon className="w-5 h-5 text-gray-600 group-hover:text-gray-800" />
              </motion.button>
              
              <CartDetails
                cart={currentCart}
                onBackToList={onBackToList}
                onQuantityChange={onQuantityChange}
                onRemoveItem={onRemoveItem}
                onClearCart={onClearCart || (() => {})}
                onCheckout={onCheckout}
                cartLoading={cartLoading}
                selectedItems={selectedItems}
                onSelectItem={onSelectItem}
                updatingItemId={updatingItemId}
              />
            </div>
          )}
        </motion.div>
      )}
    </AnimatePresence>
  );
};

export default IntegratedCartView; 