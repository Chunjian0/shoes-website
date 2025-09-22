import React, { useState, useEffect, useRef } from 'react';
import { useLocation, useNavigate, useSearchParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { useAppSelector, useAppDispatch } from '../store';
import { clearCart } from '../store/slices/cartSlice';
import { useSelector } from 'react-redux';
import { RootState } from '../store';
import { motion } from 'framer-motion';
import Spinner from '../components/ui/loading/Spinner';
import cartService from '../services/cartService';
import { api } from '../services/api';
import authService from '../services/authService';
import AddressForm from '../components/checkout/AddressForm';
import AddressDisplay from '../components/checkout/AddressDisplay';
import AddressSkeleton from '../components/checkout/AddressSkeleton';

interface ShippingInfo {
  fullName: string;
  phone: string;
  address: string;
}

interface PaymentInfo {
  cardNumber: string;
  cardHolder: string;
  expiryDate: string;
  cvv: string;
}

interface DirectPurchaseItem {
  productId: number;
  quantity: number;
  name: string;
  price: number;
  image: string;
  specifications: Record<string, string>;
}

interface UserAddress {
  id?: number;
  name: string;
  phone: string;
  address: string;
}

interface CartItemDetails {
  id: number;
  product_id: number;
  quantity: number;
  name?: string;
  price?: number;
  image?: string;
  subtotal?: number;
  specifications?: Record<string, string>;
  product?: any;
}

const CheckoutPage: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const [searchParams] = useSearchParams();
  const { isAuthenticated, loading: authLoading, user } = useSelector((state: RootState) => state.auth);
  const [isLoading, setIsLoading] = useState(true);
  const [step, setStep] = useState(1);
  const [shippingMethod, setShippingMethod] = useState('standard');
  const [paymentMethod, setPaymentMethod] = useState('credit_card');
  const [cartId, setCartId] = useState<number | null>(null);
  
  const [shippingInfo, setShippingInfo] = useState<ShippingInfo>({
    fullName: user?.name || '',
    phone: user?.phone || '',
    address: '',
  });
  
  const [paymentInfo, setPaymentInfo] = useState<PaymentInfo>({
    cardNumber: '',
    cardHolder: '',
    expiryDate: '',
    cvv: '',
  });
  
  const [showAddressForm, setShowAddressForm] = useState(false);
  const [address, setAddress] = useState<UserAddress | null>(null);
  const [totalAmount, setTotalAmount] = useState<number>(0);
  const [error, setError] = useState<string | null>(null);
  const [isPlacingOrder, setIsPlacingOrder] = useState<boolean>(false);
  const addressSectionRef = useRef<HTMLDivElement>(null);
  const [checkoutItems, setCheckoutItems] = useState<CartItemDetails[]>([]);
  
  const currencySymbol = 'RM';
  
  const subtotal = totalAmount;
  const standardShippingCost = 5;
  const expressShippingCost = 15;
  const shippingCost = shippingMethod === 'express' ? expressShippingCost : standardShippingCost;
  const taxRate = 0.08;
  const taxAmount = subtotal * taxRate;
  const orderTotal = subtotal + shippingCost + taxAmount;
  
  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      console.log("[CheckoutPage Auth Effect] Not authenticated, redirecting.");
      navigate('/login?returnUrl=' + encodeURIComponent(location.pathname + location.search), { replace: true });
    }
  }, [authLoading, isAuthenticated, navigate, location.pathname, location.search]);
  
  useEffect(() => {
    if (!isAuthenticated) return;

    const loadCheckoutData = async () => {
      setIsLoading(true);
      setError(null);
      setCheckoutItems([]);
      setTotalAmount(0);
      // Reset address states at the beginning of load
      setAddress(null);
      setShowAddressForm(false);
      console.log("[CheckoutPage Load Data] Starting. URL Search Params:", searchParams.toString());

      try {
        console.log("[CheckoutPage Load Data] Entering main try block.");
        const cartIdParam = searchParams.get('cart_id');
        const orderIdParam = searchParams.get('orderId');
        const itemIdsParam = searchParams.getAll('items[]');
        
        const parsedCartId = cartIdParam ? parseInt(cartIdParam) : null;
        const parsedOrderId = orderIdParam ? parseInt(orderIdParam) : null;
        const itemIds = itemIdsParam.map(id => parseInt(id)).filter(id => !isNaN(id));
        setCartId(parsedCartId);

        console.log("[CheckoutPage Load Data] Parsed cartId:", parsedCartId, "Parsed orderId:", parsedOrderId, "Parsed itemIds:", itemIds);

        let fetchedItems: CartItemDetails[] = [];
        let calculatedTotal = 0;

        if (parsedOrderId) {
          console.log(`[CheckoutPage Load Data] 检测到立即购买流程 (orderId: ${parsedOrderId})`);
          try {
            const orderResponse = await api.get(`/orders/${parsedOrderId}`);
            console.log("[CheckoutPage Load Data] 临时订单响应:", orderResponse);
            
            if (orderResponse.data && orderResponse.data.status === "success") {
              const orderData = orderResponse.data.data;
              
              if (orderData.items && orderData.items.length > 0) {
                fetchedItems = orderData.items.map((item: any) => {
                  return {
                    id: item.id,
                    product_id: item.product_id,
                    quantity: parseInt(item.quantity || 1),
                    name: item.product_name || '商品名称未知',
                    price: parseFloat(item.unit_price || 0),
                    subtotal: parseFloat(item.subtotal || 0),
                    specifications: item.specifications || {},
                    image: item.image_url || 'https://via.placeholder.com/100'
                  };
                });
                
                calculatedTotal = parseFloat(orderData.subtotal || orderData.total_amount || 0);
                console.log("[CheckoutPage Load Data] 从临时订单加载的商品:", fetchedItems);
                console.log("[CheckoutPage Load Data] 从临时订单计算的总金额:", calculatedTotal);
              } else {
                console.error("[CheckoutPage Load Data] 临时订单中没有商品");
                throw new Error("临时订单中没有商品");
              }
            } else {
              console.error("[CheckoutPage Load Data] 获取临时订单失败:", orderResponse);
              throw new Error(orderResponse.data?.message || "无法获取临时订单详情");
            }
          } catch (error: any) {
            console.error("[CheckoutPage Load Data] 获取临时订单时出错:", error);
            throw new Error(error.message || "获取临时订单失败，请稍后再试");
          }
        } else if (parsedCartId) {
          console.log(`[CheckoutPage Load Data] 常规购物车结账流程 (cartId: ${parsedCartId})`);
          let cartResponse: any;
          try {
              cartResponse = await cartService.getCart({ cart_id: parsedCartId }, true);
              console.log("[CheckoutPage Load Data] Raw cart service response (data part):", JSON.stringify(cartResponse, null, 2));
          } catch (serviceError: any) {
               console.error("[CheckoutPage Load Data] Error calling cartService.getCart:", serviceError);
               throw new Error(serviceError.message || "Error calling cart service. Please check connection or try again later.");
          }

          if (cartResponse?.success && cartResponse.data?.carts?.length > 0) {
            const cart = cartResponse.data.carts[0];
            
            if (itemIds.length > 0) {
                console.log("[CheckoutPage Load Data] Filtering cart items based on URL itemIds:", itemIds);
                fetchedItems = cart.items
                    .filter((item: any) => itemIds.includes(item.id))
                    .map((item: any) => {
                        const rawImageUrl = item.images?.[0] || item.product?.images?.[0];
                        console.log(`[CheckoutPage Map Item ${item.id}] Raw Img URL: ${rawImageUrl}, Top Level Images:`, item.images, `Product Images:`, item.product?.images);
                        let finalImageUrl = 'https://via.placeholder.com/100';
                        if (typeof rawImageUrl === 'string') {
                            const cleanedUrl = rawImageUrl.replace(/\\/g, '/');
                            if (cleanedUrl.startsWith('http')) {
                                finalImageUrl = cleanedUrl;
                            } else {
                                console.warn(`[CheckoutPage Map Item ${item.id}] Invalid or relative URL detected after cleaning: ${cleanedUrl}`);
                            }
                        }
                        console.log(`[CheckoutPage Map Item ${item.id}] Final Img URL: ${finalImageUrl}`);
                        return { 
                            ...item,
                            id: item.id,
                            image: finalImageUrl,
                            subtotal: item.price * item.quantity,
                            name: item.product?.name || item.name || 'Product Name Unavailable'
                        };
                    });
                calculatedTotal = fetchedItems.reduce((sum, item) => sum + (item.subtotal || 0), 0);
                console.log("[CheckoutPage Load Data] Filtered items:", fetchedItems);
                console.log("[CheckoutPage Load Data] Recalculated total for selected items:", calculatedTotal);
            } else {
                console.log("[CheckoutPage Load Data] No itemIds in URL, checking out all items in cart.");
                fetchedItems = cart.items.map((item: any) => {
                    const rawImageUrl = item.images?.[0] || item.product?.images?.[0];
                    console.log(`[CheckoutPage Map Item ${item.id}] Raw Img URL: ${rawImageUrl}, Top Level Images:`, item.images, `Product Images:`, item.product?.images);
                    let finalImageUrl = 'https://via.placeholder.com/100';
                    if (typeof rawImageUrl === 'string') {
                        const cleanedUrl = rawImageUrl.replace(/\\/g, '/');
                        if (cleanedUrl.startsWith('http')) {
                            finalImageUrl = cleanedUrl;
                        } else {
                            console.warn(`[CheckoutPage Map Item ${item.id}] Invalid or relative URL detected after cleaning: ${cleanedUrl}`);
                        }
                    }
                    console.log(`[CheckoutPage Map Item ${item.id}] Final Img URL: ${finalImageUrl}`);
                    return { 
                        ...item,
                        id: item.id,
                        image: finalImageUrl,
                        subtotal: item.price * item.quantity,
                        name: item.product?.name || item.name || 'Product Name Unavailable'
                    };
                });
                calculatedTotal = cart.total;
                console.log("[CheckoutPage Load Data] All cart items:", fetchedItems);
                console.log("[CheckoutPage Load Data] Cart total from response:", calculatedTotal);
            }

            if (fetchedItems.length === 0) {
               console.error("[CheckoutPage Load Data] Error: No valid items found for checkout after filtering.");
               throw new Error("No valid items found for checkout.");
            }

          } else {
             console.error("[CheckoutPage Load Data] API reported failure or cart data is missing/empty:", cartResponse);
             throw new Error(cartResponse?.message || "Could not retrieve cart details or cart is empty.");
          }
        } else {
          console.error("[CheckoutPage Load Data] 错误: 缺少购物车ID或订单ID");
          throw new Error("请提供购物车ID或订单ID才能进行结账");
        }

        console.log("[CheckoutPage Load Data] Cart details fetched. Setting checkout items...");
        console.log("[CheckoutPage Load Data] Setting checkout items:", JSON.stringify(fetchedItems, null, 2));
        setCheckoutItems(fetchedItems);
        console.log("[CheckoutPage Load Data] Setting total amount:", calculatedTotal);
        setTotalAmount(calculatedTotal);

        console.log("[CheckoutPage Load Data] Attempting to fetch user profile/address...");
        let fetchedAddress: UserAddress | null = null;
        try {
            console.log("[CheckoutPage Load Data] Calling authService.getUserProfile()...");
            const profileResponse = await authService.getUserProfile();
            console.log("[CheckoutPage Load Data] authService.getUserProfile() call succeeded. Profile response:", profileResponse);
            
            if (profileResponse?.status === 'success' && profileResponse.data) {
                const userProfile = profileResponse.data; 
                // Check if customer data exists within data object
                const customerData = userProfile.customer || userProfile; // Handle if data object IS the customer or contains customer

                // Always update shippingInfo state from profile
                setShippingInfo({
                    fullName: customerData.name || '',
                    phone: customerData.contact_number || customerData.phone || '',
                    address: customerData.address || ''
                });
                console.log("[CheckoutPage Load Data] Updated shippingInfo state:", { fullName: customerData.name || '', phone: customerData.contact_number || customerData.phone || '', address: customerData.address || '' });
                
                // Check if the profile has a usable address string
                const hasValidAddress = customerData.address && customerData.address.trim() !== '';
                console.log(`[CheckoutPage Load Data] Profile address ('${customerData.address}') is valid: ${hasValidAddress}`);
                
                if (hasValidAddress) {
                    fetchedAddress = {
                        id: customerData.id,
                        name: customerData.name || '',
                        phone: customerData.contact_number || customerData.phone || '',
                        address: customerData.address || ''
                    };
                    console.log("[CheckoutPage Load Data] Constructed fetchedAddress object:", fetchedAddress);
                    setAddress(fetchedAddress);
                    console.log("[CheckoutPage Load Data] Called setAddress with fetchedAddress.");
                } else {
                    console.log("[CheckoutPage Load Data] User profile address is empty or invalid, leaving address state as null.");
                    setAddress(null);
                }
            } else {
                console.warn("Failed to fetch user profile or profile data missing/invalid status.", profileResponse); // Log the full response on failure
                setAddress(null);
            }
        } catch (profileError: any) {
            console.error("[CheckoutPage Load Data] Error fetching user profile specifically:", profileError);
            // Ensure address state is null on error
            setAddress(null);
        } finally {
            // Log the final value of fetchedAddress before setting showAddressForm
            console.log("[CheckoutPage Load Data] Final fetchedAddress before setting form visibility:", fetchedAddress);
            // Show address form ONLY if fetchedAddress is still null
            const shouldShowForm = !fetchedAddress;
            setShowAddressForm(shouldShowForm);
            console.log(`[CheckoutPage Load Data] Setting showAddressForm to: ${shouldShowForm}`);
        }

      } catch (err: any) {
        console.error("[CheckoutPage Load Data] Error loading checkout data (outer catch):", err);
        setError(err.message || "Failed to load checkout information.");
      } finally {
        setIsLoading(false);
        console.log("[CheckoutPage Load Data] Finished loading data.");
      }
    };

    loadCheckoutData();
  }, [searchParams, isAuthenticated, dispatch]);
  
  const handleShippingInfoChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setShippingInfo(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handlePaymentInfoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setPaymentInfo(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const validateShippingInfo = () => {
    const { fullName, phone, address: combinedAddress } = shippingInfo;
    
    if (!fullName || !phone || !combinedAddress) {
      toast.error('Please fill in all shipping information fields.');
      return false;
    }
    return true;
  };
  
  const validatePaymentInfo = () => {
    if (paymentMethod === 'credit_card') {
      const { cardNumber, cardHolder, expiryDate, cvv } = paymentInfo;
      if (!cardNumber || !cardHolder || !expiryDate || !cvv) {
        toast.error('Please fill in all payment information fields.');
        return false;
      }
      
      if (cardNumber.replace(/\s/g, '').length < 13) {
        toast.error('Invalid card number.');
        return false;
      }
      if (!/^(0[1-9]|1[0-2])\/(\d{2})$/.test(expiryDate)) {
          toast.error('Expiry date format must be MM/YY.');
          return false;
      }
      if (cvv.length < 3 || cvv.length > 4) {
        toast.error('CVV must be 3 or 4 digits.');
        return false;
      }
    }
    return true;
  };
  
  const handleNextStep = () => {
    if (step === 1 && !address && !showAddressForm) {
        setStep(2);
    } else if (step === 1 && showAddressForm && validateShippingInfo()) {
      setStep(2);
    } else if (step === 2 && validatePaymentInfo()) {
        handlePlaceOrder();
    } else if (step === 1 && address && !showAddressForm) {
        setStep(2);
    }
  };
  
  const handlePreviousStep = () => {
    setStep(prev => prev - 1);
  };
  
  const handlePlaceOrder = async () => {
    if (!validateShippingInfo() || !validatePaymentInfo()) {
      return;
    }

    const orderIdParam = searchParams.get('orderId');
    const parsedOrderId = orderIdParam ? parseInt(orderIdParam) : null;
    const parsedCartId = cartId;

    if (!parsedCartId && !parsedOrderId) {
        toast.error('订单ID或购物车ID缺失，无法完成结账。');
        setError('订单ID或购物车ID缺失。');
        setIsPlacingOrder(false);
        return;
    }

    // 确保所有价格字段都是数字
    const calculatedSubtotal = parseFloat(String(totalAmount));
    const calculatedShippingCost = shippingMethod === 'express' ? expressShippingCost : standardShippingCost;
    const calculatedTaxAmount = calculatedSubtotal * taxRate;
    const calculatedOrderTotal = calculatedSubtotal + calculatedShippingCost + calculatedTaxAmount;

    setIsPlacingOrder(true);
    setError(null);
    console.log("[CheckoutPage Place Order] 正在处理订单...");

    try {
      const orderData = {
        cart_id: parsedCartId,
        order_id: parsedOrderId,
        shipping_address: !address ? {
            name: shippingInfo.fullName,
            contact_number: shippingInfo.phone,
            address: shippingInfo.address,
        } : null,
        items: checkoutItems.map(item => ({
            product_id: item.product_id,
            quantity: Number(item.quantity),
            price: Number(item.price || 0),
            specifications: item.specifications
        })),
        payment_method: paymentMethod,
        shipping_method: shippingMethod,
        subtotal: calculatedSubtotal,
        shipping_cost: calculatedShippingCost,
        tax_amount: calculatedTaxAmount,
        total_amount: calculatedOrderTotal,
        contact_name: address ? address.name : shippingInfo.fullName,
        contact_phone: address ? address.phone : shippingInfo.phone,
        address_details: address ? address.address : shippingInfo.address,
      };

      console.log("[CheckoutPage Place Order] 订单数据:", JSON.stringify(orderData, null, 2));

      const response = await api.post('/checkout/process', orderData);

      console.log("[CheckoutPage Place Order] API响应:", response);

      if (response.data?.success) {
        toast.success(response.data.message || '订单提交成功！');
        navigate(`/order-confirmation/${response.data.order_id}`);
      } else {
        throw new Error(response.data?.message || '订单提交失败。');
      }
    } catch (err: any) {
      console.error("[CheckoutPage Place Order] 订单提交出错:", err);
      const errorMsg = err.response?.data?.message || err.message || '提交订单时发生错误，请稍后再试。';
      if (err.response?.data?.errors) {
        const validationErrors = Object.values(err.response.data.errors).flat().join(' ');
        setError(`验证错误: ${validationErrors}`);
        toast.error(`验证错误: ${validationErrors}`);
      } else {
        setError(errorMsg);
        toast.error(errorMsg);
      }
    } finally {
      setIsPlacingOrder(false);
    }
  };
  
  const handleAddressSubmit = async (submittedAddressData: UserAddress) => {
    console.log("Address info submitted for profile update:", submittedAddressData);
    // Indicate loading state if needed
    // setIsLoadingAddress(true);
    try {
      // Call API to update customer profile
      const response = await api.put('/customer/profile', {
          name: submittedAddressData.name,
          // Assuming backend CustomerController->updateProfile handles 'phone' or 'contact_number'
          phone: submittedAddressData.phone,
          contact_number: submittedAddressData.phone, // Send both if unsure which backend expects
          address: submittedAddressData.address,
          // Do not send is_default, as it applies to the customer record directly
      });

      // Correctly check for backend status and data structure
      if (response.data?.status === 'success' && response.data?.data?.customer) {
          const updatedCustomer = response.data.data.customer; // Get customer data correctly
          console.log("Customer profile updated successfully:", updatedCustomer);

          // Update shippingInfo state based on the response
          setShippingInfo({
              fullName: updatedCustomer.name || submittedAddressData.name,
              phone: updatedCustomer.contact_number || updatedCustomer.phone || submittedAddressData.phone,
              address: updatedCustomer.address || submittedAddressData.address
          });

          // Update address state (or maybe remove it if profile is the source of truth)
          // We need an address-like object for AddressDisplay, create one from profile
          setAddress({
              id: updatedCustomer.id, // Customer ID, not address ID
              name: updatedCustomer.name || submittedAddressData.name,
              phone: updatedCustomer.contact_number || updatedCustomer.phone || submittedAddressData.phone,
              address: updatedCustomer.address || submittedAddressData.address
          });

          setShowAddressForm(false);
          toast.success("Shipping information updated successfully."); // Success message
          addressSectionRef.current?.scrollIntoView({ behavior: 'smooth' });
      } else {
          // Handle case where API reports non-success status or data is missing
          // Use the message from the backend response if available
          throw new Error(response.data?.message || 'Failed to update profile. Unexpected response format.');
      }
    } catch (error: any) {
        console.error("Failed to update profile:", error);
        // Use the error message thrown in the try block or from the Axios error response
        const errorMsg = error.message || error.response?.data?.message || 'An unknown error occurred while updating profile.';
        // Display validation errors if available from Axios error
        if (error.response?.data?.errors) {
          const validationErrors = Object.values(error.response.data.errors).flat().join(' ');
          toast.error(`Validation Error: ${validationErrors}`);
        } else {
          // Display the generic error message
          toast.error(`${errorMsg}`); // Simplified error toast
        }
        // Optionally, keep the form open if update fails
        // setShowAddressForm(true);
    } finally {
        // setIsLoadingAddress(false);
    }
  };
  
  if (isLoading || authLoading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <Spinner size="lg" />
      </div>
    );
  }
  
  if (!isAuthenticated) {
    return null;
  }
  
  if (error && !isLoading) {
  return (
          <div className="container mx-auto px-4 py-12 text-center">
              <h1 className="text-2xl font-semibold text-red-600 mb-4">An Error Occurred!</h1>
              <p className="text-red-500 mb-6">{error}</p>
              <button 
                  onClick={() => navigate('/cart')} 
                  className="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
              >
                  Return to Cart
              </button>
          </div>
      );
  }
  
  return (
    <motion.div 
      className="container mx-auto px-4 py-8 lg:py-12"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.5 }}
    >
      <h1 className="text-3xl lg:text-4xl font-bold text-center text-gray-800 mb-8 lg:mb-12">Checkout</h1>
      
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
        <div className="lg:col-span-2 space-y-8">
          <motion.div 
             ref={addressSectionRef}
             className="bg-white p-6 rounded-lg shadow-md"
             initial={{ opacity: 0, y: 20 }}
             animate={{ opacity: 1, y: 0 }}
             transition={{ delay: 0.1 }}
          >
            <h2 className="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">1. Shipping Address</h2>
            {isLoading ? (
              <AddressSkeleton />
            ) : showAddressForm ? (
              <AddressForm 
                onSubmit={handleAddressSubmit} 
                onCancel={() => { if(address) setShowAddressForm(false); }}
                initialData={shippingInfo}
              />
            ) : address ? (
                <div>
                   <AddressDisplay address={address} onEdit={() => setShowAddressForm(true)} />
                   <button 
                     onClick={() => setShowAddressForm(true)} 
                     className="mt-4 text-sm font-medium text-indigo-600 hover:text-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                   >
                     Change Address
                   </button>
                </div>
            ) : (
                 <button onClick={() => setShowAddressForm(true)} className="text-indigo-600 hover:underline font-medium">
                    Add Shipping Address
                 </button>
            )}
          </motion.div>

          <motion.div 
            className="bg-white p-6 rounded-lg shadow-md"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: step >= 1 ? 1 : 0, y: step >= 1 ? 0 : 20 }}
            transition={{ delay: 0.2 }}
          >
            <h2 className="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">2. Shipping Method</h2>
                  <div className="space-y-3">
               <label className="flex items-center p-3 border rounded-md hover:border-blue-500 cursor-pointer transition-colors">
                      <input
                        type="radio"
                        name="shippingMethod"
                        value="standard"
                        checked={shippingMethod === 'standard'}
                    onChange={(e) => setShippingMethod(e.target.value)}
                    className="form-radio h-5 w-5 text-blue-600"
                      />
                      <div className="ml-3">
                   <span className="block font-medium text-gray-800">Standard Shipping</span>
                   <span className="text-sm text-gray-500">Est. 5-7 working days - {currencySymbol}{standardShippingCost.toFixed(2)}</span>
                      </div>
                    </label>
               <label className="flex items-center p-3 border rounded-md hover:border-blue-500 cursor-pointer transition-colors">
                      <input
                        type="radio"
                        name="shippingMethod"
                        value="express"
                        checked={shippingMethod === 'express'}
                    onChange={(e) => setShippingMethod(e.target.value)}
                    className="form-radio h-5 w-5 text-blue-600"
                      />
                      <div className="ml-3">
                   <span className="block font-medium text-gray-800">Express Shipping</span>
                   <span className="text-sm text-gray-500">Est. 1-3 working days - {currencySymbol}{expressShippingCost.toFixed(2)}</span>
                      </div>
                    </label>
                  </div>
          </motion.div>
              
          <motion.div 
            className="bg-white p-6 rounded-lg shadow-md"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: step >= 1 ? 1 : 0, y: step >= 1 ? 0 : 20 }}
            transition={{ delay: 0.3 }}
          >
             <h2 className="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">3. Payment Method</h2>
             <div className="space-y-3 mb-4">
                <label className="flex items-center p-3 border rounded-md hover:border-blue-500 cursor-pointer transition-colors">
                    <input
                      type="radio"
                      name="paymentMethod"
                      value="credit_card"
                      checked={paymentMethod === 'credit_card'}
                      onChange={(e) => setPaymentMethod(e.target.value)}
                      className="form-radio h-5 w-5 text-blue-600"
                    />
                   <span className="ml-3 font-medium text-gray-800">Credit Card</span>
                  </label>
              </div>
              
              {paymentMethod === 'credit_card' && (
                <div className="space-y-4 border-t pt-4 mt-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                         <label htmlFor="cardNumber" className="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                    <input
                      type="text"
                      id="cardNumber"
                      name="cardNumber"
                      value={paymentInfo.cardNumber}
                      onChange={handlePaymentInfoChange}
                           placeholder="**** **** **** ****"
                           className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      required
                    />
                  </div>
                      <div>
                         <label htmlFor="cardHolder" className="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                    <input
                      type="text"
                      id="cardHolder"
                      name="cardHolder"
                      value={paymentInfo.cardHolder}
                      onChange={handlePaymentInfoChange}
                           placeholder="Full Name on Card" 
                           className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      required
                    />
                  </div>
                   </div>
                   <div className="grid grid-cols-2 gap-4">
                  <div>
                         <label htmlFor="expiryDate" className="block text-sm font-medium text-gray-700 mb-1">Expiry Date (MM/YY)</label>
                    <input
                      type="text"
                      id="expiryDate"
                      name="expiryDate"
                      value={paymentInfo.expiryDate}
                      onChange={handlePaymentInfoChange}
                      placeholder="MM/YY"
                           className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      required
                    />
                  </div>
                  <div>
                         <label htmlFor="cvv" className="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                    <input
                      type="text"
                      id="cvv"
                      name="cvv"
                      value={paymentInfo.cvv}
                      onChange={handlePaymentInfoChange}
                      placeholder="123"
                           className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      required
                    />
                  </div>
                </div>
                </div>
              )}
          </motion.div>
            </div>

        <div className="lg:col-span-1">
           <div className="bg-white p-6 rounded-lg shadow-md sticky top-24">
             <h2 className="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Order Summary</h2>
              
             <div className="space-y-3 mb-6 max-h-60 overflow-y-auto pr-2">
               {checkoutItems.map((item) => (
                 <div key={item.id || item.product_id} className="flex items-center space-x-3 border-b pb-3 last:border-b-0">
                   <img src={item.image} alt={item.name || 'Product Image'} className="w-16 h-16 object-cover rounded" />
                   <div className="flex-grow">
                     <p className="text-sm font-medium text-gray-800 line-clamp-2">{item.name || 'Unknown Product'}</p>
                     <p className="text-xs text-gray-500">Qty: {item.quantity}</p>
                     {item.specifications && Object.entries(item.specifications).length > 0 && (
                         <div className="text-xs text-gray-500 mt-1">
                         {Object.entries(item.specifications).map(([key, value]) => ( 
                             <span key={key} className="mr-2">{key}: {value}</span>
                         ))}
              </div>
                  )}
                </div>
                   <p className="text-sm font-medium text-gray-800">{currencySymbol}{parseFloat(String(item.subtotal || 0)).toFixed(2)}</p>
                    </div>
                  ))}
             </div>

             <div className="space-y-2 border-t pt-4">
               <div className="flex justify-between text-sm text-gray-600">
                 <span>Subtotal</span>
                 <span>{currencySymbol}{parseFloat(String(subtotal)).toFixed(2)}</span>
               </div>
               <div className="flex justify-between text-sm text-gray-600">
                 <span>Shipping ({shippingMethod === 'standard' ? 'Standard' : 'Express'})</span>
                 <span>{currencySymbol}{parseFloat(String(shippingCost)).toFixed(2)}</span>
               </div>
               <div className="flex justify-between text-sm text-gray-600">
                 <span>Tax ({(parseFloat(String(taxRate)) * 100).toFixed(0)}%)</span>
                 <span>{currencySymbol}{parseFloat(String(taxAmount)).toFixed(2)}</span>
               </div>
               <div className="flex justify-between text-lg font-semibold text-gray-800 border-t pt-2 mt-2">
                 <span>Total</span>
                 <span>{currencySymbol}{parseFloat(String(orderTotal)).toFixed(2)}</span>
                </div>
              </div>
              
             {error && (
                <p className="text-red-500 text-sm mt-4 text-center">{error}</p>
             )}

                <button
                  onClick={handlePlaceOrder}
                disabled={isPlacingOrder} 
                className={`w-full mt-6 py-3 px-4 rounded-md text-white font-semibold transition-colors ${isPlacingOrder ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700'}`} 
             >
               {isPlacingOrder ? (
                 <Spinner size="sm" />
                  ) : (
                    'Place Order'
                  )}
                </button>
          </div>
        </div>
      </div>
    </motion.div>
  );
};

export default CheckoutPage; 