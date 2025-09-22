import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { CheckCircleIcon, ExclamationTriangleIcon, InformationCircleIcon, CalendarDaysIcon, TruckIcon } from '@heroicons/react/24/outline'; // Example icons
import Spinner from '../components/ui/loading/Spinner';
// Assume an orderService exists or will be created
// import orderService from '../services/orderService'; 
import { api } from '../services/api'; // Or use api directly if service not created yet
import orderService from '../services/orderService'; // Import the service
import { format, parseISO } from 'date-fns'; // For date formatting
import { getDisplayStatus } from '../utils/orderStatusHelper'; // <-- Import shared helper
import ShippingTimeline from '../components/order/ShippingTimeline'; // <-- Import the timeline

// --- Interfaces (Simplified Address) ---
export interface OrderItem { 
  id: number;
  product_id: number;
  product_name: string;
  product_sku?: string;
  quantity: number;
  unit_price: string | number;
  subtotal: string | number;
  specifications?: Record<string, string> | null;
  image_url?: string; 
}

export interface OrderDetails { 
  id: number;
  order_number: string;
  order_date: string; // Formatted date from API (e.g., from toArray() in controller)
  order_date_raw: string; // Raw ISO date string <-- Added
  status: string; // Overall order status
  shipping_status: string; // Specific shipping status <-- Make sure this is fetched
  payment_status: string; // Should be 'paid' now
  payment_method?: string;
  shipping_method?: string;
  subtotal: string | number;
  tax_amount: string | number;
  shipping_cost: string | number;
  discount_amount?: string | number;
  total_amount: string | number;
  estimated_arrival_date: string | null; // Formatted date <-- Added/Ensure present
  estimated_arrival_date_raw: string | null; // Raw YYYY-MM-DD <-- Added
  items: OrderItem[];
  // Simplified address fields
  contact_name?: string;
  contact_phone?: string;
  address_details?: string;
  // Removed separate billing/shipping fields
  customer?: {
    id: number;
    name: string;
    email: string;
  };
}

// Update the type definition to match the helper function's return type
type DisplayStatusType = { text: string; bgColor: string; textColor: string; icon: React.ElementType };

// --- Component ---
const OrderConfirmationPage: React.FC = () => {
  const { orderId } = useParams<{ orderId: string }>();
  const [order, setOrder] = useState<OrderDetails | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  const currencySymbol = 'RM'; // Or get from config/context

  useEffect(() => {
    const fetchOrderDetails = async () => {
      if (!orderId) {
        setError('Order ID is missing.');
        setIsLoading(false);
        return;
      }

      setIsLoading(true);
      setError(null);
      setOrder(null); // Reset order state on new fetch

      console.log(`Fetching details for order ID: ${orderId}`);

      try {
        // Use the imported service function
        const fetchedOrder = await orderService.getOrderDetails(orderId!);
        console.log("Fetched Order Details:", fetchedOrder);
        
        // Add null checks for potentially missing raw dates before setting state
        setOrder({
          ...fetchedOrder,
          // Provide default valid ISO string if raw date is missing/invalid
          order_date_raw: fetchedOrder.order_date_raw || new Date(0).toISOString(), 
          estimated_arrival_date_raw: fetchedOrder.estimated_arrival_date_raw || null,
        });
      } catch (err: any) {
        // Error handling is now mostly done within the service, 
        // but we still catch here to set the component's error state.
        console.error("Error fetching order details in component:", err);
        // Use the error message provided by the service
        setError(err.message || 'An unexpected error occurred.');
      } finally {
        setIsLoading(false);
      }
    };

    if (orderId) {
        fetchOrderDetails();
    }
  }, [orderId]); // Re-fetch if orderId changes

  // --- Animation Variants ---
  const containerVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { duration: 0.5, ease: "easeOut" }
    },
  };

  const itemVariants = {
    hidden: { opacity: 0, y: 10 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { duration: 0.3, ease: "easeOut" }
    },
  };

  // --- Render Logic ---
  if (isLoading) {
    return (
      <div className="flex justify-center items-center min-h-[60vh]">
        <Spinner size="lg" />
      </div>
    );
  }

  if (error) {
    return (
      <motion.div 
        className="container mx-auto px-4 py-12 text-center"
        initial="hidden"
        animate="visible"
        variants={containerVariants}
      >
        <ExclamationTriangleIcon className="h-16 w-16 text-red-500 mx-auto mb-4" />
        <h1 className="text-2xl font-semibold text-red-600 mb-4">Order Not Found or Error</h1>
        <p className="text-red-500 mb-6">{error}</p>
        <Link 
          to="/" 
          className="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
        >
          Go to Homepage
        </Link>
      </motion.div>
    );
  }

  if (!order) {
    // Should ideally be covered by error state, but as a fallback
    return (
      <motion.div 
        className="container mx-auto px-4 py-12 text-center"
        initial="hidden"
        animate="visible"
        variants={containerVariants}
      >
         <InformationCircleIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
        <h1 className="text-2xl font-semibold text-gray-600 mb-4">Order Details Unavailable</h1>
        <p className="text-gray-500 mb-6">Could not load order details at this time.</p>
         <Link 
          to="/" 
          className="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
        >
          Go to Homepage
        </Link>
      </motion.div>
    );
  }
  
  // --- Get Display Status using Helper --- 
  // Calculate displayStatus only if order exists and necessary fields are present
  const displayStatus: DisplayStatusType = getDisplayStatus(
          order.shipping_status || 'unknown', // Provide fallback for status
          order.order_date_raw, 
          order.estimated_arrival_date_raw
        );
      
  const StatusIcon = displayStatus.icon; // Get icon component

  // Format dates safely using the raw date from state
  // Check if raw date is valid before formatting
  let formattedOrderDate = 'N/A';
  try {
      if (order.order_date_raw && order.order_date_raw !== new Date(0).toISOString()) {
          formattedOrderDate = format(parseISO(order.order_date_raw), 'PPpp');
      }
  } catch (e) {
      console.error("Error parsing order_date_raw:", order.order_date_raw, e);
  }
      
  // Use the formatted date directly from the order object if available
  const formattedEstimatedArrivalDate = order.estimated_arrival_date || 'Not available'; 

  // Helper to safely format numbers
  const formatCurrency = (amount: string | number | undefined | null): string => {
      const num = Number(amount);
      if (isNaN(num)) return `${currencySymbol}0.00`;
      return `${currencySymbol}${num.toFixed(2)}`;
  };

  // --- Main Render ---
  return (
    <motion.div 
      className="container mx-auto px-4 py-8 lg:py-16"
      initial="hidden"
      animate="visible"
      variants={containerVariants}
    >
      {/* Header Section */}
      <motion.div 
        className="text-center mb-10 lg:mb-16"
        variants={itemVariants}
      >
        {/* Use appropriate icon based on status */}
        {displayStatus.text.toLowerCase().includes('delivered') || displayStatus.text.toLowerCase().includes('completed') ?
          <CheckCircleIcon className="h-20 w-20 lg:h-24 lg:w-24 text-green-500 mx-auto mb-4 animate-pulse" />
          : <StatusIcon className={`h-20 w-20 lg:h-24 lg:w-24 ${displayStatus.textColor} mx-auto mb-4`} />
        }
        <h1 className="text-3xl lg:text-4xl font-bold text-gray-800 mb-2">
          {displayStatus.text.toLowerCase().includes('delivered') || displayStatus.text.toLowerCase().includes('completed') ? 'Your Order Has Arrived!' : 'Thank You For Your Order!'}
        </h1>
        <p className="text-lg text-gray-600">
          Order <span className="font-semibold text-indigo-600">#{order.order_number}</span> Status:
          <span className={`ml-2 font-medium ${displayStatus.textColor}`}>{displayStatus.text}</span>
        </p>
        <p className="text-sm text-gray-500 mt-2 flex flex-wrap justify-center items-center gap-x-4 gap-y-1">
          <span className="inline-flex items-center"> 
            <CalendarDaysIcon className="h-4 w-4 mr-1 text-gray-400" /> 
            Placed on: {formattedOrderDate}
          </span>
          {formattedEstimatedArrivalDate !== 'Not available' && (
            <span className="inline-flex items-center"> 
              <TruckIcon className="h-4 w-4 mr-1 text-gray-400" /> 
              Estimated Arrival: {formattedEstimatedArrivalDate}
            </span>
          )}
        </p>
      </motion.div>

      {/* Main Content Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
        
        {/* Left Column: Items, Timeline & Summary */}
        <div className="lg:col-span-2 space-y-8">
          {/* Items Purchased */}
          <motion.div 
            className="bg-white p-5 rounded-lg shadow-md border border-gray-200"
            variants={itemVariants}
          >
            <h2 className="text-xl font-semibold text-gray-700 mb-4 border-b pb-3">Items Purchased</h2>
            <motion.ul 
                className="space-y-4"
                initial="hidden"
                animate="visible"
                variants={{ visible: { transition: { staggerChildren: 0.1 } } }} // Stagger animation
             >
              {order.items.map((item) => (
                <motion.li 
                  key={item.id || item.product_id} 
                  className="flex items-start space-x-4 border-b pb-4 last:border-b-0"
                  variants={itemVariants}
                 >
                  {/* Use the image_url prepared by the backend */}
                  <img 
                     src={item.image_url || 'https://via.placeholder.com/80'} 
                     alt={item.product_name || 'Product Image'} 
                     className="w-20 h-20 object-cover rounded-md border" 
                  />
                  <div className="flex-grow">
                    <p className="text-base font-medium text-gray-800">{item.product_name}</p>
                    {item.product_sku && <p className="text-xs text-gray-500">SKU: {item.product_sku}</p>}
                    <p className="text-sm text-gray-600">Qty: {item.quantity}</p>
                    {/* Display Specifications */}
                     {item.specifications && Object.entries(item.specifications).length > 0 && (
                         <div className="text-xs text-gray-500 mt-1 space-x-2">
                         {Object.entries(item.specifications).map(([key, value]) => ( 
                             <span key={key} className="inline-block bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">{key}: {value}</span>
                         ))}
                        </div>
                     )}
                  </div>
                  <div className="text-right">
                    <p className="text-base font-semibold text-gray-800">{formatCurrency(item.subtotal)}</p>
                    <p className="text-xs text-gray-500">({formatCurrency(item.unit_price)} each)</p>
                   </div>
                </motion.li>
              ))}
            </motion.ul>
          </motion.div>
          
          {/* Shipping Timeline - Add the new component here */}
          <motion.div variants={itemVariants}>
            <ShippingTimeline
              orderDateRaw={order.order_date_raw}
              estimatedArrivalDateRaw={order.estimated_arrival_date_raw}
              dbStatus={order.shipping_status}
            />
          </motion.div>
          
          {/* Address & Payment Summary (Simplified) */}
          <motion.div 
             className="grid grid-cols-1 md:grid-cols-2 gap-6"
             variants={itemVariants}
          >
             {/* Address Info */}
             <div className="bg-white p-5 rounded-lg shadow-md border border-gray-200">
                <h3 className="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Shipping & Billing Address</h3>
                <p className="font-medium text-gray-800">{order.contact_name || 'N/A'}</p>
                <p className="text-sm text-gray-600">{order.address_details || 'N/A'}</p>
                <p className="text-sm text-gray-600 mt-1">Phone: {order.contact_phone || 'N/A'}</p>
                <p className="text-sm text-gray-500 mt-2">Shipping Method: {order.shipping_method || 'Standard'}</p>
                {formattedEstimatedArrivalDate !== 'Not available' && (
                  <p className="text-sm text-gray-500 mt-2 inline-flex items-center"> 
                    <TruckIcon className="h-4 w-4 mr-1.5 text-gray-400" /> 
                    Estimated Arrival: <span className="font-medium ml-1">{formattedEstimatedArrivalDate}</span>
                  </p>
                )}
             </div>
             {/* Payment Info */}
              <div className="bg-white p-5 rounded-lg shadow-md border border-gray-200">
                <h3 className="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Payment Info</h3>
                <p className="text-sm text-gray-600">Method: <span className="font-medium">{order.payment_method?.replace('_', ' ').toUpperCase() || 'N/A'}</span></p>
                <p className={`text-sm mt-1 text-green-600`}>
                   Status: <span className="font-medium">PAID</span>
                </p>
             </div>
          </motion.div>

        </div>

        {/* Right Column: Order Totals */}
        <div className="lg:col-span-1">
          <motion.div 
            className="bg-white p-6 rounded-lg shadow-lg sticky top-24 border border-gray-200" // Enhanced shadow
            variants={itemVariants}
          >
            <h2 className="text-xl font-semibold text-gray-700 mb-4 border-b pb-3">Order Summary</h2>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>{formatCurrency(order.subtotal)}</span>
              </div>
              <div className="flex justify-between text-gray-600">
                <span>Shipping ({order.shipping_method || 'Standard'})</span>
                <span>{formatCurrency(order.shipping_cost)}</span>
              </div>
              <div className="flex justify-between text-gray-600">
                 <span>Tax</span>
                 <span>{formatCurrency(order.tax_amount)}</span>
               </div>
               {/* Optional Discount Display */}
               {Number(order.discount_amount || 0) > 0 && (
                 <div className="flex justify-between text-green-600">
                   <span>Discount</span>
                   <span>-{formatCurrency(order.discount_amount)}</span>
                 </div>
               )}
              <div className="border-t my-3"></div>
              <div className="flex justify-between text-lg font-bold text-gray-800">
                 <span>Order Total</span>
                 <span>{formatCurrency(order.total_amount)}</span>
                </div>
              </div>

              {/* Action Buttons */}
              <motion.div 
                 className="mt-8 space-y-3"
                 initial={{ opacity: 0 }}
                 animate={{ opacity: 1 }}
                 transition={{ delay: 0.5 }}
              >
                <Link 
                  to="/" // Link to homepage
                  className="w-full block text-center px-4 py-2.5 bg-indigo-600 text-white font-semibold rounded-md shadow hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:-translate-y-0.5"
                >
                  Continue Shopping
                </Link>
                 <Link 
                  to="/account/orders" // Link to order history (adjust path as needed)
                  className="w-full block text-center px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-md border border-gray-300 hover:bg-gray-200 transition duration-300 ease-in-out"
                >
                  View Order History
                </Link>
              </motion.div>
          </motion.div>
        </div>

      </div>
    </motion.div>
  );
};

export default OrderConfirmationPage; 