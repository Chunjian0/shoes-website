import { api } from './api';
// Assuming interfaces are defined in OrderConfirmationPage and exported, or move them to a shared types file
import { OrderDetails } from '../pages/OrderConfirmationPage'; 

// Define a more specific type for the API response structure if needed
interface ApiResponse<T> {
  status: string; // 'success' or 'error'
  data?: T;
  message?: string;
  success?: boolean; // Keep for backward compatibility if backend might use it
}

// Interface for the summary data expected from the API index endpoint
export interface OrderSummary {
    id: number;
    order_number: string;
    order_date: string; // Formatted date
    order_date_raw: string; // Raw ISO date string
    total_amount: string; // Formatted amount
    shipping_status: string;
    estimated_arrival_date: string | null; // Formatted date
    estimated_arrival_date_raw: string | null; // Raw date string (YYYY-MM-DD)
    first_item_image_url: string;
    item_count: number;
}

// Interface for the pagination data from the API
export interface PaginationInfo {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
}

// Interface for the API response structure for the history endpoint
interface OrderHistoryResponse {
    status: string;
    data: OrderSummary[];
    pagination: PaginationInfo;
}

const getOrderDetails = async (orderId: string): Promise<OrderDetails> => {
  console.log(`[orderService] Fetching details for order ID: ${orderId}`);
  try {
    const response = await api.get<ApiResponse<OrderDetails>>(`/orders/${orderId}`);
    console.log("[orderService] API Response:", response);

    // Check backend response structure
    if ((response.data?.success || response.data?.status === 'success') && response.data?.data) {
      return response.data.data; // Return the order data directly
    } else {
      // Throw an error with the message from the backend if available
      throw new Error(response.data?.message || 'Failed to fetch order details or order not found.');
    }
  } catch (error: any) {
    console.error("[orderService] Error fetching order details:", error);
    // Re-throw a cleaned-up error message
    const errorMsg = error.response?.data?.message || error.message || 'An error occurred while fetching order details.';
    throw new Error(errorMsg);
  }
};

// Function to get order history
const getOrderHistory = async (page: number = 1, perPage: number = 10): Promise<{ orders: OrderSummary[], pagination: PaginationInfo }> => {
    console.log(`Fetching order history: page ${page}, perPage ${perPage}`);
    try {
        const response = await api.get<OrderHistoryResponse>(`/orders`, {
             params: {
                 page: page,
                 per_page: perPage
             }
        });

        if (response.data && response.data.status === 'success') {
             console.log('Order history fetched successfully:', response.data);
            return {
                orders: response.data.data,
                pagination: response.data.pagination
            };
        } else {
            // OrderHistoryResponse doesn't have message, use a generic error
            console.error('Failed to fetch order history. Status:', response.data?.status);
            throw new Error('Failed to fetch order history (API status not success)');
        }
    } catch (error: any) {
        console.error('Error in getOrderHistory service:', error);
         const errorMessage = error.response?.data?.message || error.message || 'An unexpected error occurred while fetching order history.';
        if (error.response) {
             console.error('API Error Response:', error.response.data);
         }
        throw new Error(errorMessage);
    }
};

const orderService = {
  getOrderDetails,
  getOrderHistory,
  // Add other order-related functions here later (e.g., getOrderHistory)
};

export default orderService; 