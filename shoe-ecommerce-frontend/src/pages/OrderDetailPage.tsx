import { useState, useEffect } from 'react';
import { Link, useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { apiService } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner';
import { AxiosError } from 'axios';
import ConfirmDialog from '../components/common/ConfirmDialog';

interface OrderItem {
  id: number;
  product_id: number;
  product_name: string;
  product_image: string;
  sku: string;
  price: number;
  quantity: number;
  attributes: Record<string, string>;
}

interface Order {
  id: number;
  order_number: string;
  status: string;
  total_amount: number;
  subtotal: number;
  shipping_fee: number;
  tax: number;
  discount: number;
  created_at: string;
  updated_at: string;
  payment_method: string;
  shipping_method: string;
  shipping_address: {
    name: string;
    phone: string;
    address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
  };
  billing_address: {
    name: string;
    phone: string;
    address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
  };
  items: OrderItem[];
}

const OrderDetailPage = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [cancelLoading, setCancelLoading] = useState(false);
  const [showCancelConfirm, setShowCancelConfirm] = useState(false);

  // 获取订单详情
  const fetchOrderDetail = async () => {
    if (!id) return;
    const orderId = parseInt(id);
    if (isNaN(orderId)) {
      setError('无效的订单 ID');
      setLoading(false);
      return;
    }
    
    try {
      setLoading(true);
      setError(null);
      
      const response = await apiService.orders.getById(orderId);
      setOrder(response);
    } catch (error) {
      console.error('Failed to fetch order details:', error);
      const message = 
        error instanceof AxiosError && error.response?.data?.message
        ? error.response.data.message 
        : error instanceof Error ? error.message : 'Failed to get order details';
      setError(message);
      toast.error(message);
    } finally {
      setLoading(false);
    }
  };

  // 初始化加载
  useEffect(() => {
    fetchOrderDetail();
  }, [id]);

  // 取消订单 - 打开确认对话框
  const handleCancelOrder = () => {
    if (!id || !order || order.status !== 'pending') return;
    setShowCancelConfirm(true);
  };

  // 确认取消订单
  const confirmCancelOrder = async () => {
    if (!id) return;
    const orderId = parseInt(id);
    if (isNaN(orderId)) return;
    
    try {
      setCancelLoading(true);
      await apiService.orders.cancel(orderId);
      toast.success('Order has been successfully cancelled');
      fetchOrderDetail();
    } catch (error) {
      console.error('Failed to cancel order:', error);
      const message = 
        error instanceof AxiosError && error.response?.data?.message
        ? error.response.data.message 
        : error instanceof Error ? error.message : 'Order cancellation failure';
      toast.error(message);
    } finally {
      setCancelLoading(false);
      setShowCancelConfirm(false);
    }
  };

  // 获取订单状态标签样式
  const getStatusBadgeClass = (status: string) => {
    switch (status) {
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'processing':
        return 'bg-blue-100 text-blue-800';
      case 'shipped':
        return 'bg-purple-100 text-purple-800';
      case 'delivered':
        return 'bg-green-100 text-green-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-neutral-100 text-neutral-800';
    }
  };

  // 获取订单状态中文名称
  const getStatusName = (status: string) => {
    const statusMap: Record<string, string> = {
      'pending': 'Pending Payment',
      'processing': 'Processing',
      'shipped': 'Shipped',
      'delivered': 'Delivered',
      'cancelled': 'Cancelled'
    };
    
    return statusMap[status] || status;
  };

  // 格式化日期
  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // 格式化金额
  const formatCurrency = (amount: number) => {
    return `RM${amount.toFixed(2)}`;
  };

  // 格式化地址
  const formatAddress = (address: Order['shipping_address']) => {
    return `${address.name}, ${address.phone}, ${address.address}, ${address.city}, ${address.state}, ${address.postal_code}, ${address.country}`;
  };

  if (loading) {
    return (
      <div className="container py-8 flex justify-center">
        <LoadingSpinner size="large" />
      </div>
    );
  }

  if (error || !order) {
    return (
      <div className="container py-8">
        <div className="bg-danger-light text-danger p-4 rounded-md">
          {error || 'Order not found'}
          <button
            onClick={() => navigate('/orders')}
            className="ml-4 text-sm underline"
          >
            Back to Order List
          </button>
        </div>
      </div>
    );
  }

  return (
    <>
      <div className="container py-8">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Order Details</h1>
          <Link to="/orders" className="text-primary hover:text-primary-dark">
            Back to Order List
          </Link>
        </div>
        
        {/* 订单概览 */}
        <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div className="flex flex-wrap justify-between mb-4">
            <div>
              <h2 className="text-lg font-medium mb-1">Order Number: {order.order_number}</h2>
              <p className="text-sm text-neutral-500">Order Time: {formatDate(order.created_at)}</p>
            </div>
            <div className="flex items-center">
              <span className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusBadgeClass(order.status)}`}>
                {getStatusName(order.status)}
              </span>
              
              {/* 只有待付款状态的订单可以取消 */}
              {order.status === 'pending' && (
                <button
                  onClick={handleCancelOrder}
                  disabled={cancelLoading}
                  className="ml-4 text-sm text-danger hover:text-danger-dark"
                >
                  {cancelLoading ? <LoadingSpinner size="small" /> : 'Cancel Order'}
                </button>
              )}
            </div>
          </div>
        </div>
        
        {/* 订单商品 */}
        <div className="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
          <div className="px-6 py-4 border-b border-neutral-200">
            <h3 className="font-medium">Order Items</h3>
          </div>
          
          <div className="divide-y divide-neutral-200">
            {order.items.map((item) => (
              <div key={item.id} className="p-6 flex items-center">
                <div className="flex-shrink-0 w-16 h-16 bg-neutral-100 rounded overflow-hidden">
                  {item.product_image ? (
                    <img
                      src={item.product_image}
                      alt={item.product_name}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-neutral-400">
                      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                  )}
                </div>
                
                <div className="ml-4 flex-1">
                  <h4 className="font-medium">{item.product_name}</h4>
                  
                  {/* 商品属性 */}
                  {Object.keys(item.attributes).length > 0 && (
                    <div className="mt-1 text-sm text-neutral-500">
                      {Object.entries(item.attributes).map(([key, value]) => (
                        <span key={key} className="mr-4">
                          {key}: {value}
                        </span>
                      ))}
                    </div>
                  )}
                  
                  <div className="mt-1 text-sm text-neutral-500">
                    SKU: {item.sku}
                  </div>
                </div>
                
                <div className="text-right">
                  <div className="font-medium">
                    {formatCurrency(item.price)}
                  </div>
                  <div className="text-sm text-neutral-500">
                    x{item.quantity}
                  </div>
                  <div className="font-medium mt-1">
                    {formatCurrency(item.price * item.quantity)}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* 配送信息 */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h3 className="font-medium mb-4">Shipping Information</h3>
            
            <div className="space-y-3">
              <div>
                <div className="text-sm text-neutral-500">Shipping Method</div>
                <div>{order.shipping_method}</div>
              </div>
              
              <div>
                <div className="text-sm text-neutral-500">Shipping Address</div>
                <div>{formatAddress(order.shipping_address)}</div>
              </div>
            </div>
          </div>
          
          {/* 支付信息 */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h3 className="font-medium mb-4">Payment Information</h3>
            
            <div className="space-y-3">
              <div>
                <div className="text-sm text-neutral-500">Payment Method</div>
                <div>{order.payment_method}</div>
              </div>
              
              <div>
                <div className="text-sm text-neutral-500">Billing Address</div>
                <div>{formatAddress(order.billing_address)}</div>
              </div>
            </div>
          </div>
        </div>
        
        {/* 订单汇总 */}
        <div className="bg-white rounded-lg shadow-sm p-6 mt-6">
          <h3 className="font-medium mb-4">Order Summary</h3>
          
          <div className="space-y-2">
            <div className="flex justify-between">
              <span className="text-neutral-600">Subtotal</span>
              <span>{formatCurrency(order.subtotal)}</span>
            </div>
            
            <div className="flex justify-between">
              <span className="text-neutral-600">Shipping Fee</span>
              <span>{formatCurrency(order.shipping_fee)}</span>
            </div>
            
            {order.tax > 0 && (
              <div className="flex justify-between">
                <span className="text-neutral-600">Tax</span>
                <span>{formatCurrency(order.tax)}</span>
              </div>
            )}
            
            {order.discount > 0 && (
              <div className="flex justify-between">
                <span className="text-neutral-600">Discount</span>
                <span>-{formatCurrency(order.discount)}</span>
              </div>
            )}
            
            <div className="border-t border-neutral-200 pt-2 mt-2 flex justify-between font-medium">
              <span>Total</span>
              <span className="text-lg">{formatCurrency(order.total_amount)}</span>
            </div>
          </div>
        </div>
      </div>

      {/* 取消订单确认对话框 */}
      <ConfirmDialog
        isOpen={showCancelConfirm}
        onCancel={() => setShowCancelConfirm(false)}
        onConfirm={confirmCancelOrder}
        title="Confirm Order Cancellation"
        message="Are you sure you want to cancel this order? This action cannot be undone."
        confirmText="Confirm Cancellation"
        confirmButtonClass="bg-red-600 hover:bg-red-700 focus:ring-red-500"
      />
    </>
  );
};

export default OrderDetailPage; 