import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import orderService, { OrderSummary, PaginationInfo } from '../../services/orderService';
import Spinner from '../../components/ui/loading/Spinner';
import { motion } from 'framer-motion'; // Import motion
import { getDisplayStatus } from '../../utils/orderStatusHelper'; // <-- Import the helper function
import { ExclamationCircleIcon, ShoppingBagIcon, CalendarDaysIcon, TruckIcon } from '@heroicons/react/24/outline'; // Keep icons for error/empty states

// --- Animation Variants ---
const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.08, // Slightly faster stagger
        },
    },
};

const itemVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.4, ease: "easeOut" } },
};

const OrderHistoryPage: React.FC = () => {
    const [orders, setOrders] = useState<OrderSummary[]>([]);
    const [pagination, setPagination] = useState<PaginationInfo | null>(null);
    const [isLoading, setIsLoading] = useState<boolean>(true);
    const [error, setError] = useState<string | null>(null);
    const [currentPage, setCurrentPage] = useState<number>(1);

    const currencySymbol = 'RM';

    const fetchOrders = useCallback(async (page: number) => {
        setIsLoading(true);
        setError(null);
        try {
            const { orders: fetchedOrders, pagination: fetchedPagination } = await orderService.getOrderHistory(page);
            setOrders(fetchedOrders);
            setPagination(fetchedPagination);
        } catch (err: any) {
            setError(err.message || 'Failed to load order history.');
        } finally {
            setIsLoading(false);
        }
    }, []); // No dependencies needed if fetchOrders doesn't rely on external changing state

    useEffect(() => {
        fetchOrders(currentPage);
    }, [currentPage, fetchOrders]); // Include fetchOrders in dependency array

    const handlePageChange = (newPage: number) => {
        if (newPage >= 1 && pagination && newPage <= pagination.last_page) {
            setCurrentPage(newPage);
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll to top on page change
        }
    };

    // --- Render Logic ---
    if (isLoading && orders.length === 0) { // Show initial loading spinner prominently
        return (
             <div className="container mx-auto px-4 py-16 flex justify-center items-center min-h-[60vh]">
                <Spinner size="lg" />
             </div>
        );
    }

    if (error) {
        return (
            <div className="container mx-auto px-4 py-16 text-center">
                <ExclamationCircleIcon className="h-12 w-12 text-red-400 mx-auto mb-4" />
                 <p className="text-lg text-red-600">Error loading order history</p>
                 <p className="text-sm text-gray-500 mt-2">{error}</p>
                 <motion.button
                     onClick={() => fetchOrders(currentPage)} // Retry button
                     className="mt-6 inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                     whileHover={{ scale: 1.05 }}
                     whileTap={{ scale: 0.95 }}
                 >
                    Retry
                 </motion.button>
            </div>
        );
    }

    return (
         <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
            <motion.h1 
                className="text-3xl font-bold tracking-tight text-gray-900 mb-8 lg:mb-10"
                initial={{ opacity: 0, y: -10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.3 }}
            >
                Order History
            </motion.h1>

            {orders.length === 0 && !isLoading ? ( // Show no orders message only if not loading
                <motion.div 
                    className="text-center text-gray-500 py-16 bg-white rounded-lg shadow border border-gray-200"
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.3 }}
                >
                    <ShoppingBagIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <p className="text-lg font-medium">You haven't placed any orders yet.</p>
                    <p className="text-sm mt-1">Ready to find something amazing?</p>
                    <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}>
                        <Link to="/" className="mt-6 inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Start Shopping
                        </Link>
                    </motion.div>
                </motion.div>
            ) : (
                <motion.div
                    className="space-y-6"
                    variants={containerVariants}
                    initial="hidden"
                    animate="visible" // Animate on load/page change
                    key={currentPage} // Re-trigger animation on page change
                >
                    {orders.map((order) => {
                        const displayStatus = getDisplayStatus(order.shipping_status, order.order_date_raw, order.estimated_arrival_date_raw);
                        const IconComponent = displayStatus.icon; // Get the icon component
                        return (
                            <motion.div
                                key={order.id}
                                className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300 group"
                                variants={itemVariants} // Apply item animation
                                whileHover={{ y: -4, transition: { duration: 0.2 } }} // Subtle lift on hover
                            >
                                <div className="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5">
                                    {/* Image */}
                                    <Link to={`/order/confirmation/${order.id}`} className="flex-shrink-0 block relative rounded-lg overflow-hidden">
                                         <motion.img
                                            src={order.first_item_image_url}
                                            alt="Order Item"
                                            className="w-24 h-24 sm:w-28 sm:h-28 object-cover border border-gray-200 rounded-lg group-hover:scale-105 transition-transform duration-300 ease-in-out" // Scale image on card hover
                                         />
                                         {/* Optional: Add a subtle overlay on hover? */}
                                         {/* <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity duration-300"></div> */} 
                                     </Link>

                                    {/* Order Info */}
                                    <div className="flex-grow min-w-0">
                                        <div className="flex justify-between items-center flex-wrap gap-x-4 gap-y-1 mb-2">
                                             <Link to={`/order/confirmation/${order.id}`} className="text-base font-semibold text-indigo-600 hover:text-indigo-800 truncate group-hover:underline" title={`Order #${order.order_number}`}>
                                                 Order #{order.order_number}
                                             </Link>
                                             <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${displayStatus.bgColor} ${displayStatus.textColor}`}> 
                                                 <IconComponent className="h-3.5 w-3.5 mr-1.5" aria-hidden="true" /> 
                                                {displayStatus.text}
                                             </span>
                                        </div>
                                        <div className="flex items-center text-xs text-gray-500 mb-2 gap-x-4 flex-wrap">
                                            <span className="inline-flex items-center whitespace-nowrap"> 
                                                 <CalendarDaysIcon className="h-3.5 w-3.5 mr-1 text-gray-400" aria-hidden="true" /> 
                                                 Placed: {order.order_date}
                                            </span>
                                            {order.estimated_arrival_date && (
                                                 <span className="inline-flex items-center whitespace-nowrap"> 
                                                     <TruckIcon className="h-3.5 w-3.5 mr-1 text-gray-400" aria-hidden="true" /> 
                                                     Est. Arrival: {order.estimated_arrival_date}
                                                 </span>
                                            )}
                                        </div>
                                         <p className="text-sm font-medium text-gray-900">Total: <span className="font-semibold">{currencySymbol}{order.total_amount}</span></p>
                                    </div>

                                     {/* Action Button */} 
                                    <motion.div
                                         className="w-full sm:w-auto mt-4 sm:mt-0 sm:ml-6 flex-shrink-0" // Increased margin left
                                         whileHover={{ scale: 1.03 }}
                                         whileTap={{ scale: 0.98 }}
                                    >
                                        <Link
                                            to={`/order-confirmation/${order.id}`}
                                            className="inline-flex items-center justify-center w-full sm:w-auto px-5 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                                        >
                                            View Details
                                        </Link>
                                     </motion.div>
                                </div>
                            </motion.div>
                        );
                    })}

                     {/* Pagination Controls - Enhanced Styling */}
                     {pagination && pagination.last_page > 1 && (
                         <motion.div
                             className="mt-10 flex justify-center items-center space-x-2 sm:space-x-3"
                             initial={{ opacity: 0 }}
                             animate={{ opacity: 1 }}
                             transition={{ delay: 0.3 }} // Delay pagination appearance slightly less if list is short
                         >
                             <motion.button
                                 onClick={() => handlePageChange(currentPage - 1)}
                                 disabled={currentPage === 1}
                                 className="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 shadow-sm"
                                 whileHover={{ scale: 1.05, transition: { duration: 0.1 } }}
                                 whileTap={{ scale: 0.95 }}
                             >
                                 &laquo; Prev
                             </motion.button>
                             {/* Page Numbers (Simple display) */}
                             <span className="text-sm text-gray-700 px-2">
                                 Page {pagination.current_page} of {pagination.last_page}
                             </span>
                             <motion.button
                                 onClick={() => handlePageChange(currentPage + 1)}
                                 disabled={currentPage === pagination.last_page}
                                 className="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 shadow-sm"
                                 whileHover={{ scale: 1.05, transition: { duration: 0.1 } }}
                                 whileTap={{ scale: 0.95 }}
                             >
                                 Next &raquo;
                             </motion.button>
                         </motion.div>
                     )}
                </motion.div>
            )}
        </div>
    );
};

export default OrderHistoryPage; 