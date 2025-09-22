import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '../store';
import { 
  fetchCart, 
  createCart, 
  updateCart, 
  deleteCart, 
  setActiveCart,
  Cart
} from '../store/slices/cartSlice';
import LoadingSpinner from '../components/LoadingSpinner';
import { toast } from 'react-toastify';

const CartManagementPage: React.FC = () => {
  const dispatch = useDispatch<AppDispatch>();
  const navigate = useNavigate();
  const { isAuthenticated, loading: authLoading } = useSelector((state: RootState) => state.auth);
  const { carts, loading, error, activeCartId } = useSelector((state: RootState) => state.cart);

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

  // 组件加载时获取购物车数据
  useEffect(() => {
    dispatch(fetchCart());
  }, [dispatch]);

  // 检查用户是否已登录
  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      navigate('/login?returnUrl=' + encodeURIComponent('/cart-management'), { replace: true });
    }
  }, [isAuthenticated, authLoading, navigate]);

  // 处理创建新购物车
  const handleCreateCart = async () => {
    if (!newCartName.trim()) {
      toast.error('请输入购物车名称');
      return;
    }

    setLoadingAction(true);
    try {
      await dispatch(createCart({
        name: newCartName.trim(),
        type: newCartType,
        is_default: isDefault
      })).unwrap();
      
      // 刷新购物车列表
      dispatch(fetchCart());
      
      // 重置表单
      setNewCartName('');
      setNewCartType('default');
      setIsDefault(false);
      setShowCreateModal(false);
      
      toast.success('购物车创建成功');
    } catch (error) {
      const message = error instanceof Error ? error.message : '创建购物车失败';
      toast.error(message);
    } finally {
      setLoadingAction(false);
    }
  };

  // 打开编辑购物车模态框
  const handleOpenEditModal = (cart: Cart) => {
    setEditCartId(cart.id);
    setEditCartName(cart.name);
    setEditCartType(cart.type ?? 'default');
    setEditIsDefault(cart.is_default ?? false);
    setShowEditModal(true);
  };

  // 处理编辑购物车
  const handleUpdateCart = async () => {
    if (!editCartName.trim()) {
      toast.error('请输入购物车名称');
      return;
    }

    if (!editCartId) return;

    setLoadingAction(true);
    try {
      await dispatch(updateCart({
        id: editCartId,
        data: {
          name: editCartName.trim(),
          type: editCartType,
          is_default: editIsDefault
        }
      })).unwrap();
      
      // 刷新购物车列表
      dispatch(fetchCart());
      
      // 重置表单
      setEditCartId(null);
      setEditCartName('');
      setEditCartType('');
      setEditIsDefault(false);
      setShowEditModal(false);
      
      toast.success('购物车更新成功');
    } catch (error) {
      const message = error instanceof Error ? error.message : '更新购物车失败';
      toast.error(message);
    } finally {
      setLoadingAction(false);
    }
  };

  // 处理删除购物车
  const handleDeleteCart = async (id: number) => {
    if (!window.confirm('确定要删除此购物车吗？购物车中的所有商品将被删除。')) {
      return;
    }

    setLoadingAction(true);
    try {
      await dispatch(deleteCart({ id })).unwrap();
      
      // 刷新购物车列表
      dispatch(fetchCart());
      
      toast.success('购物车删除成功');
    } catch (error) {
      const message = error instanceof Error ? error.message : '删除购物车失败';
      toast.error(message);
    } finally {
      setLoadingAction(false);
    }
  };

  // 处理设置活动购物车
  const handleSetActiveCart = (id: number) => {
    dispatch(setActiveCart(id));
    toast.success('默认购物车已设置');
  };

  // 查看购物车详情
  const handleViewCart = (id: number) => {
    navigate(`/cart?cart_id=${id}`);
  };

  if (authLoading) {
    return (
      <div className="flex justify-center items-center h-screen">
        <LoadingSpinner size="large" />
      </div>
    );
  }

  if (!isAuthenticated) {
    return null;
  }

  return (
    <div className="container max-w-6xl mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold">购物车管理</h1>
        <button 
          onClick={() => setShowCreateModal(true)}
          className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          创建新购物车
        </button>
      </div>

      {loading && carts.length === 0 ? (
        <div className="flex justify-center py-12">
          <LoadingSpinner size="large" />
        </div>
      ) : error ? (
        <div className="text-center py-8 text-neutral-600">
          <div className="mb-4 text-red-500">
            加载购物车时出错。请稍后再试。
          </div>
          <div>{typeof error === 'string' ? error : (error ? JSON.stringify(error) : '未知错误')}</div>
        </div>
      ) : carts.length === 0 ? (
        <div className="text-center py-12 bg-white rounded-lg shadow p-8">
          <div className="mb-6">
            <svg className="w-20 h-20 mx-auto text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
          <h2 className="text-2xl font-bold text-neutral-700 mb-4">
            没有购物车
          </h2>
          <p className="text-neutral-600 mb-6">
            您还没有创建任何购物车。创建一个购物车来开始购物！
          </p>
          <button
            onClick={() => setShowCreateModal(true)}
            className="bg-blue-600 text-white py-2 px-6 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            创建购物车
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {carts.map(cart => (
            <div 
              key={cart.id} 
              className={`bg-white rounded-lg shadow overflow-hidden border-2 ${
                cart.id === activeCartId ? 'border-blue-500' : 'border-transparent'
              }`}
            >
              <div className="p-6">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <h2 className="text-lg font-semibold text-neutral-800">{cart.name}</h2>
                    <div className="flex items-center mt-1">
                      <span className={`text-sm px-2 py-1 rounded-full ${
                        cart.type === 'default' ? 'bg-blue-100 text-blue-800' : 
                        cart.type === 'wishlist' ? 'bg-pink-100 text-pink-800' : 
                        'bg-purple-100 text-purple-800'
                      }`}>
                        {cart.type === 'default' ? '购物车' : 
                         cart.type === 'wishlist' ? '愿望单' : 
                         '保存以后'}
                      </span>
                      {cart.is_default && (
                        <span className="ml-2 text-sm text-green-600 bg-green-50 px-2 py-1 rounded-full">
                          默认
                        </span>
                      )}
                    </div>
                  </div>
                  <div className="flex">
                    <button
                      onClick={() => handleOpenEditModal(cart)}
                      className="text-neutral-500 hover:text-neutral-700 p-1"
                      title="编辑"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button
                      onClick={() => handleDeleteCart(cart.id)}
                      className="text-red-500 hover:text-red-700 p-1 ml-1"
                      title="删除"
                      disabled={loadingAction}
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <div className="mb-4">
                  <div className="text-sm text-neutral-600 mb-1">
                    商品数量: <span className="font-medium">{cart.item_count || 0}</span>
                  </div>
                  <div className="text-sm text-neutral-600">
                    总价: <span className="font-semibold text-blue-600">¥{cart.total?.toFixed(2) || '0.00'}</span>
                  </div>
                </div>
                
                <div className="flex gap-2">
                  <button
                    onClick={() => handleViewCart(cart.id)}
                    className="flex-1 bg-blue-600 text-white px-3 py-2 text-sm rounded hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  >
                    查看详情
                  </button>
                  
                  {!cart.is_default && (
                    <button
                      onClick={() => handleSetActiveCart(cart.id)}
                      className="flex-1 border border-blue-600 text-blue-600 px-3 py-2 text-sm rounded hover:bg-blue-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                      设为默认
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* 创建购物车模态框 */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-bold">Create New Cart</h2>
              <button
                onClick={() => setShowCreateModal(false)}
                className="text-neutral-500 hover:text-neutral-700"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div className="mb-4">
              <label className="block text-sm font-medium text-neutral-700 mb-1">
                购物车名称
              </label>
              <input
                type="text"
                value={newCartName}
                onChange={(e) => setNewCartName(e.target.value)}
                className="w-full p-2 border border-neutral-300 rounded focus:ring-blue-500 focus:border-blue-500"
                placeholder="例如：日常购物、礼物、办公用品等"
              />
            </div>

            <div className="mb-4">
              <label className="block text-sm font-medium text-neutral-700 mb-1">
                购物车类型
              </label>
              <select
                value={newCartType}
                onChange={(e) => setNewCartType(e.target.value)}
                className="w-full p-2 border border-neutral-300 rounded focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="default">默认购物车</option>
                <option value="wishlist">愿望单</option>
                <option value="saveforlater">稍后购买</option>
              </select>
            </div>

            <div className="mb-6">
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={isDefault}
                  onChange={(e) => setIsDefault(e.target.checked)}
                  className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-neutral-300 rounded"
                />
                <span className="ml-2 text-sm text-neutral-700">设为默认购物车</span>
              </label>
            </div>

            <div className="flex justify-end gap-3">
              <button
                onClick={() => setShowCreateModal(false)}
                className="px-4 py-2 border border-neutral-300 rounded-md text-neutral-700 hover:bg-neutral-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                取消
              </button>
              <button
                onClick={handleCreateCart}
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                disabled={loadingAction}
              >
                {loadingAction ? (
                  <span className="flex items-center">
                    <LoadingSpinner size="small" />
                    <span className="ml-2">In Progress...</span>
                  </span>
                ) : 'Create Cart'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 编辑购物车模态框 */}
      {showEditModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-bold">Edit Cart</h2>
              <button
                onClick={() => setShowEditModal(false)}
                className="text-neutral-500 hover:text-neutral-700"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div className="mb-4">
              <label className="block text-sm font-medium text-neutral-700 mb-1">
                购物车名称
              </label>
              <input
                type="text"
                value={editCartName}
                onChange={(e) => setEditCartName(e.target.value)}
                className="w-full p-2 border border-neutral-300 rounded focus:ring-blue-500 focus:border-blue-500"
                placeholder="例如：日常购物、礼物、办公用品等"
              />
            </div>

            <div className="mb-4">
              <label className="block text-sm font-medium text-neutral-700 mb-1">
                购物车类型
              </label>
              <select
                value={editCartType}
                onChange={(e) => setEditCartType(e.target.value)}
                className="w-full p-2 border border-neutral-300 rounded focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="default">默认购物车</option>
                <option value="wishlist">愿望单</option>
                <option value="saveforlater">稍后购买</option>
              </select>
            </div>

            <div className="mb-6">
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={editIsDefault}
                  onChange={(e) => setEditIsDefault(e.target.checked)}
                  className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-neutral-300 rounded"
                />
                <span className="ml-2 text-sm text-neutral-700">设为默认购物车</span>
              </label>
            </div>

            <div className="flex justify-end gap-3">
              <button
                onClick={() => setShowEditModal(false)}
                className="px-4 py-2 border border-neutral-300 rounded-md text-neutral-700 hover:bg-neutral-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                取消
              </button>
              <button
                onClick={handleUpdateCart}
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                disabled={loadingAction}
              >
                {loadingAction ? (
                  <span className="flex items-center">
                    <LoadingSpinner size="small" />
                    <span className="ml-2">处理中...</span>
                  </span>
                ) : '保存修改'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default CartManagementPage; 