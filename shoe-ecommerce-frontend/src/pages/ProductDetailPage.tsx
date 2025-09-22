import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store';
import { 
  fetchProductDetails, 
  fetchProductStock, 
  fetchProductReviews,
  clearCurrentProduct,
  ProductStock,
  ProductReview as Review
} from '../store/slices/productSlice';
import LoadingSpinner from '../components/LoadingSpinner';
import { toast } from 'react-toastify';
import { useSelector } from 'react-redux';
import { fetchCart, addToCart, selectActiveCart, Cart } from '../store/slices/cartSlice';
import { apiService } from '../services/api';
import CartLink from '../components/cart/CartLink';
import cartService from '../services/cartService';
import { Product } from '../types';
import { AxiosError } from 'axios';

const ProductDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  
  // Get product details from Redux
  const { 
    currentProduct, 
    productStock, 
    productReviews,
    loading,
    error 
  } = useAppSelector(state => state.products);
  
  // Local state
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [quantity, setQuantity] = useState(1);
  const [activeTab, setActiveTab] = useState('description');
  const [activeImageIndex, setActiveImageIndex] = useState(0);
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [imageErrors, setImageErrors] = useState<Record<string, boolean>>({});
  const [showCartDropdown, setShowCartDropdown] = useState(false);
  const [loadingCarts, setLoadingCarts] = useState(false);
  const [availableCarts, setAvailableCarts] = useState<Cart[]>([]);
  const activeCart = useSelector(selectActiveCart);
  const [isAddingToCart, setIsAddingToCart] = useState(false);

  // Fetch data when component mounts
  useEffect(() => {
    if (id) {
      const productId = parseInt(id);
      if (!isNaN(productId)) {
        dispatch(fetchProductDetails(productId));
        dispatch(fetchProductStock(productId));
        dispatch(fetchProductReviews(productId));
      } else {
        console.error('Invalid product ID:', id);
        navigate('/not-found', { replace: true });
      }
    }
    
    // Clear product when component unmounts
    return () => {
      dispatch(clearCurrentProduct());
    };
  }, [dispatch, id, navigate]);

  useEffect(() => {
    if (currentProduct && currentProduct.images && currentProduct.images.length > 0 && !selectedImage) {
      setSelectedImage(currentProduct.images[0]);
    }
    // Set default size and color only if they haven't been selected yet and product data is available
    if (currentProduct && !selectedSize && currentProduct.sizes && currentProduct.sizes.length > 0) {
      setSelectedSize(currentProduct.sizes[0]);
    }
    if (currentProduct && !selectedColor && currentProduct.colors && currentProduct.colors.length > 0) {
      setSelectedColor(currentProduct.colors[0]);
      }
  }, [currentProduct, selectedImage, selectedSize, selectedColor]);

  // Handle quantity change
  const handleQuantityChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value) && value > 0) {
      setQuantity(value);
    }
  };

  // Increase quantity
  const increaseQuantity = () => {
    setQuantity(prev => prev + 1);
  };

  // Decrease quantity
  const decreaseQuantity = () => {
    setQuantity(prev => (prev > 1 ? prev - 1 : 1));
  };

  // Load carts
  const loadCarts = async () => {
    setLoadingCarts(true);
    try {
      const response = await apiService.cart.get();
      if (response?.success && response.data?.carts) {
        setAvailableCarts(response.data.carts);
      } else {
        setAvailableCarts([]);
      }
    } catch (error) {
      console.error('Failed to load carts', error);
      setAvailableCarts([]);
    } finally {
      setLoadingCarts(false);
    }
  };

  // Handle add to cart
  const handleAddToCart = async () => {
    if (!selectedSize && currentProduct?.sizes?.length) {
      toast.error('Please select a size');
      return;
    }
    
    if (!selectedColor && currentProduct?.colors?.length) {
      toast.error('Please select a color');
      return;
    }
    
    // Ensure currentProduct exists before proceeding
    if (!currentProduct) {
      toast.error('Product details not available.');
      return;
    }
    
    try {
      setIsAddingToCart(true);
      
      // Use the cart service to add product
      const response = await cartService.addToCart({
        product_id: currentProduct.id,
        quantity: quantity,
        size: selectedSize || undefined,
        color: selectedColor || undefined
      });
      
    console.log('Add to cart:', {
      product: currentProduct,
      size: selectedSize,
      color: selectedColor,
      quantity
    });
      
      if (response?.success) {
        toast.success(`Added ${quantity} ${currentProduct?.name ?? 'product'} to cart`);
        // Reload cart data
        dispatch(fetchCart());
      } else {
        toast.error(response?.message || 'Failed to add to cart');
      }
    } catch (error) {
      console.error('Add to cart error:', error);
      toast.error('An error occurred while adding to cart');
    } finally {
      setIsAddingToCart(false);
      setShowCartDropdown(false);
    }
  };

  // Handle add to specific cart
  const handleAddToSpecificCart = async (cartId: number) => {
    if (!selectedSize && currentProduct?.sizes?.length) {
      toast.error('Please select a size');
      return;
    }
      
    if (!selectedColor && currentProduct?.colors?.length) {
      toast.error('Please select a color');
      return;
    }
    
    // Ensure currentProduct exists before proceeding
    if (!currentProduct) {
      toast.error('Product details not available.');
      return;
    }
    
    try {
      setIsAddingToCart(true);
      
      // Use the cart service to add product to specific cart
      const response = await cartService.addToCart({
        product_id: currentProduct.id,
        quantity: quantity,
        size: selectedSize || undefined,
        color: selectedColor || undefined,
        cart_id: cartId
      });
      
      console.log('Add to specific cart:', {
        product: currentProduct,
        size: selectedSize,
        color: selectedColor,
        quantity,
        cart_id: cartId
      });
      
      if (response?.success) {
        const cartName = availableCarts.find(cart => cart.id === cartId)?.name || 'selected cart';
        toast.success(`Added ${quantity} ${currentProduct?.name ?? 'product'} to ${cartName}`);
        // Reload cart data
        dispatch(fetchCart());
      } else {
        toast.error(response?.message || 'Failed to add to cart');
      }
    } catch (error) {
      console.error('Add to cart error:', error);
      toast.error('An error occurred while adding to cart');
    } finally {
      setIsAddingToCart(false);
      setShowCartDropdown(false);
    }
  };

  // Check stock status
  const checkStockStatus = () => {
    if (loading) return 'Checking stock...';
    
    if (!Array.isArray(productStock) || productStock.length === 0) return 'No stock information available';
    
    const totalStock = productStock.reduce((sum, item) => sum + (item?.quantity ?? 0), 0);
    
    if (totalStock > 0) {
      if (totalStock > 10) {
        return 'In Stock';
      } else {
        return `Only ${totalStock} left`;
      }
    } else {
      return 'Out of Stock';
    }
  };

  // Get stock status color
  const getStockStatusColor = () => {
    if (loading) return 'text-gray-500';
    
    if (!Array.isArray(productStock) || productStock.length === 0) return 'text-gray-500';
    
    const totalStock = productStock.reduce((sum, item) => sum + (item?.quantity ?? 0), 0);
    
    if (totalStock > 0) {
      if (totalStock > 10) {
        return 'text-green-600';
      } else {
        return 'text-orange-500';
      }
    } else {
      return 'text-red-600';
    }
  };

  // Render rating stars
  const renderStars = (rating: number) => {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    // Full stars
    for (let i = 0; i < fullStars; i++) {
      stars.push(
        <svg key={`full-${i}`} className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      );
    }
    
    // Half star
    if (hasHalfStar) {
      stars.push(
        <svg key="half" className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
          <defs>
            <linearGradient id="half-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="50%" stopColor="currentColor" />
              <stop offset="50%" stopColor="#D1D5DB" />
            </linearGradient>
          </defs>
          <path fill="url(#half-gradient)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      );
    }
    
    // Empty stars
    const emptyStars = Math.max(0, 5 - fullStars - (hasHalfStar ? 1 : 0));
    for (let i = 0; i < emptyStars; i++) {
      stars.push(
        <svg key={`empty-${i}`} className="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      );
    }
    
    return stars;
  };

  const handleImageError = (imageSrc: string) => {
    if (!imageErrors[imageSrc]) {
      setImageErrors(prev => ({
        ...prev,
        [imageSrc]: true
      }));
    }
    // If the currently selected main image fails, try to select the next available one
    if (selectedImage === imageSrc) {
      // Ensure images is an array before filtering
      const imagesArray = Array.isArray(currentProduct?.images) ? currentProduct.images : [];
      const availableImages = imagesArray.filter((img): img is string => !!img && !imageErrors[img] && img !== imageSrc);
      if (availableImages.length > 0) {
        setSelectedImage(availableImages[0]);
        setActiveImageIndex(imagesArray.findIndex(img => img === availableImages[0]) ?? 0);
      }
    }
  };

  if (loading) {
    return (
      <>
        <div className="flex justify-center items-center h-96">
          <LoadingSpinner />
        </div>
      </>
    );
  }

  if (error || !currentProduct) {
    return (
      <>
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold text-red-600 mb-4">Error Loading Product</h2>
          <p className="text-gray-600 mb-6">{error}</p>
          <Link to="/products" className="btn-primary">
            Return to Products
          </Link>
        </div>
      </>
    );
  }

  return (
    <>
      <div className="container mx-auto px-4 py-8">
        {/* Breadcrumbs */}
        <nav className="text-sm mb-6">
          <ol className="list-none p-0 inline-flex">
            <li className="flex items-center">
              <Link to="/" className="text-gray-500 hover:text-blue-600">Home</Link>
              <span className="mx-2 text-gray-500">/</span>
            </li>
            <li className="flex items-center">
              <Link to="/products" className="text-gray-500 hover:text-blue-600">Products</Link>
              <span className="mx-2 text-gray-500">/</span>
            </li>
            <li className="text-gray-700">{currentProduct.name}</li>
          </ol>
        </nav>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
          {/* Product Images */}
          <div>
            <div className="mb-4 border border-gray-200 rounded-lg overflow-hidden bg-white">
              {selectedImage && !imageErrors[selectedImage] ? (
                <img 
                  src={selectedImage} 
                  alt={currentProduct.name} 
                  className="w-full h-auto object-contain aspect-square"
                  onError={() => handleImageError(selectedImage)}
                />
              ) : (
                <div className="w-full aspect-square flex items-center justify-center bg-gray-100">
                  <div className="text-gray-400 text-center p-4">
                    <div className="text-6xl mb-2">👟</div>
                    <p className="text-sm">{currentProduct.name}</p>
                  </div>
                </div>
              )}
            </div>
            
            {/* Thumbnail Images */}
            {Array.isArray(currentProduct.images) && currentProduct.images.length > 1 && (
              <div className="grid grid-cols-5 gap-2 mt-4">
                {currentProduct.images.map((img: string, index: number) => (
                  <img 
                    key={index}
                    src={img} 
                    alt={`Thumbnail ${index + 1}`} 
                    className={`w-full h-20 object-cover cursor-pointer rounded-md border-2 ${activeImageIndex === index ? 'border-blue-500' : 'border-transparent'} hover:border-blue-400 transition-colors`}
                    onClick={() => setSelectedImage(img)}
                    onError={(e) => handleImageError(img)}
                  />
                ))}
              </div>
            )}
          </div>

          {/* Product Info */}
          <div>
            <div className="mb-6">
              <h1 className="text-3xl font-bold text-neutral-800 mb-2">
                {currentProduct.name}
              </h1>
              
              <div className="flex items-center mb-4">
                {productReviews && typeof productReviews.average_rating === 'number' && typeof productReviews.total === 'number' && (
                  <>
                    <div className="flex items-center mr-2">
                      {renderStars(productReviews.average_rating ?? 0)}
                    </div>
                    <span className="text-sm text-neutral-600">
                      {(productReviews.average_rating ?? 0).toFixed(1)} ({productReviews.total ?? 0} reviews)
                    </span>
                  </>
                )}
              </div>
              
              <div className="mb-4">
                <span className="text-2xl font-bold text-blue-600">
                  ${currentProduct.sale_price ? currentProduct.sale_price.toFixed(2) : currentProduct.price.toFixed(2)}
                </span>
                {currentProduct.sale_price && (
                  <span className="ml-2 text-lg text-neutral-500 line-through">
                    ${currentProduct.price.toFixed(2)}
                  </span>
                )}
              </div>
              
              <div className="mb-4">
                <p className="text-neutral-600">
                  {(currentProduct.short_description || currentProduct.description)?.substring(0, 150)}...
                </p>
              </div>
              
              <div className="mb-4 flex items-center">
                <span className="mr-2 text-sm font-medium">Stock Status:</span>
                <span className={`text-sm font-medium ${getStockStatusColor()}`}>
                  {checkStockStatus()}
                </span>
              </div>
            </div>
            
            {/* Size Selection */}
            {Array.isArray(currentProduct.sizes) && currentProduct.sizes.length > 0 && (
              <div className="mb-6">
                <h3 className="text-sm font-medium text-neutral-700 mb-2">
                  Size
                </h3>
                <div className="flex flex-wrap gap-2">
                  {currentProduct.sizes.map((size: string) => (
                    <button
                      key={size}
                      onClick={() => setSelectedSize(size)}
                      className={`px-4 py-2 border rounded-md transition-colors ${
                        selectedSize === size
                          ? 'border-blue-600 bg-blue-50 text-blue-600'
                          : 'border-neutral-300 hover:border-blue-600'
                      }`}
                    >
                      {size}
                    </button>
                  ))}
                </div>
              </div>
            )}
            
            {/* Color Selection */}
            {Array.isArray(currentProduct.colors) && currentProduct.colors.length > 0 && (
              <div className="mb-6">
                <h3 className="text-sm font-medium text-neutral-700 mb-2">
                  Color
                </h3>
                <div className="flex flex-wrap gap-2">
                  {currentProduct.colors.map((color: string) => (
                    <button
                      key={color}
                      onClick={() => setSelectedColor(color)}
                      className={`w-10 h-10 rounded-full border-2 transition-all ${
                        selectedColor === color
                          ? 'border-blue-600 ring-2 ring-blue-200'
                          : 'border-transparent'
                      }`}
                      style={{ backgroundColor: color }}
                      title={color}
                    />
                  ))}
                </div>
              </div>
            )}
            
            {/* Quantity Selection */}
            <div className="mb-6">
              <h3 className="text-sm font-medium text-neutral-700 mb-2">
                Quantity
              </h3>
              <div className="flex items-center">
                <button
                  onClick={decreaseQuantity}
                  className="w-10 h-10 border border-neutral-300 rounded-l-md flex items-center justify-center hover:bg-neutral-100 transition-colors"
                >
                  <svg className="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
                  </svg>
                </button>
                <input
                  type="number"
                  min="1"
                  value={quantity}
                  onChange={handleQuantityChange}
                  className="w-16 h-10 border-t border-b border-neutral-300 text-center focus:outline-none focus:ring-1 focus:ring-blue-600"
                />
                <button
                  onClick={increaseQuantity}
                  className="w-10 h-10 border border-neutral-300 rounded-r-md flex items-center justify-center hover:bg-neutral-100 transition-colors"
                >
                  <svg className="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>
            
            {/* Action Buttons */}
            <div className="flex flex-col sm:flex-row gap-4 mb-8">
              <div className="relative flex-1">
              <button
                onClick={handleAddToCart}
                  className="w-full px-6 py-3 bg-orange-500 text-white font-semibold rounded-md hover:bg-orange-600 transition-colors flex items-center justify-center"
                  disabled={!currentProduct || loading || isAddingToCart}
                >
                  {isAddingToCart ? (
                    <span className="flex items-center">
                      <svg className="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Adding...
                    </span>
                  ) : (
                    <>
                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                  Add to {activeCart ? activeCart.name : 'Cart'}
                    </>
                  )}
                </button>
                <button
                  onClick={() => { setShowCartDropdown(!showCartDropdown); if(!showCartDropdown) loadCarts(); }}
                  disabled={!currentProduct || loading || isAddingToCart}
                  className="absolute inset-y-0 right-0 flex items-center px-2 rounded-r-md bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                  <svg className={`h-5 w-5 text-white transition-transform duration-200 ${showCartDropdown ? 'transform rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                
                {/* Cart dropdown menu */}
                {showCartDropdown && (
                  <div className="absolute z-10 right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                    <div className="py-1 max-h-60 overflow-auto">
                      <div className="px-4 py-2 text-sm font-medium text-gray-800 border-b border-gray-200 sticky top-0 bg-gray-50">
                        Select a Cart
                      </div>
                      
                      {loadingCarts ? (
                        <div className="px-4 py-3 text-sm text-gray-500 italic flex items-center justify-center">
                          <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                          Loading carts...
                        </div>
                      ) : availableCarts.length === 0 ? (
                        <div className="px-4 py-3 text-sm text-gray-500 italic">
                          No carts available
                        </div>
                      ) : (
                        <>
                          {availableCarts.map(cart => (
                            <button
                              key={cart.id}
                              className={`w-full text-left px-4 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 flex items-center justify-between ${
                                activeCart?.id === cart.id ? 'bg-blue-50 text-blue-700' : 'text-gray-700'
                              }`}
                              onClick={() => handleAddToSpecificCart(cart.id)}
                            >
                              <span className="flex items-center">
                                <svg className={`mr-2 h-4 w-4 ${activeCart?.id === cart.id ? 'text-blue-500' : 'text-gray-500'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  {cart.type === 'wishlist' ? (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                  ) : cart.type === 'saveforlater' ? (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                  ) : (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                  )}
                                </svg>
                                {cart.name}
                                {cart.item_count > 0 && <span className="ml-1 text-xs text-gray-500">({cart.item_count})</span>}
                              </span>
                              {cart.is_default && (
                                <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                  Default
                                </span>
                              )}
                            </button>
                          ))}
                          
                          <div className="border-t border-gray-100 my-1"></div>
                          
                          <button
                            className="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 flex items-center"
                            onClick={() => {
                              // Navigate to cart management page
                              navigate('/cart-management');
                              setShowCartDropdown(false);
                            }}
                          >
                            <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create New Cart
              </button>
                        </>
                      )}
                    </div>
                  </div>
                )}
              </div>
              <button 
                className="px-6 py-3 border border-blue-600 text-blue-600 font-semibold rounded-md hover:bg-blue-50 transition-colors flex-1 flex items-center justify-center"
                onClick={() => {
                  // Find wishlist cart or create one
                  const wishlist = availableCarts.find(cart => cart.type === 'wishlist');
                  if (wishlist) {
                    handleAddToSpecificCart(wishlist.id);
                  } else {
                    toast.info('Please create a wishlist first');
                    navigate('/cart-management');
                  }
                }}
              >
                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                Add to Wishlist
              </button>
            </div>
            
            {/* Product Tags */}
            {Array.isArray(currentProduct.tags) && currentProduct.tags.length > 0 && (
              <div className="mb-6">
                <h3 className="text-sm font-medium text-neutral-700 mb-2">
                  Tags
                </h3>
                <div className="flex flex-wrap gap-2">
                  {currentProduct.tags.map((tag: string) => (
                    <span
                      key={tag}
                      className="px-3 py-1 bg-neutral-100 text-neutral-600 text-sm rounded-full"
                    >
                      {tag}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
        
        {/* Product Detail Tabs */}
        <div className="mb-12">
          <div className="border-b border-neutral-200">
            <nav className="flex -mb-px">
              <button
                onClick={() => setActiveTab('description')}
                className={`py-4 px-6 font-medium text-sm border-b-2 transition-colors ${
                  activeTab === 'description'
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'
                }`}
              >
                Description
              </button>
              <button
                onClick={() => setActiveTab('specifications')}
                className={`py-4 px-6 font-medium text-sm border-b-2 transition-colors ${
                  activeTab === 'specifications'
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'
                }`}
              >
                Specifications
              </button>
              <button
                onClick={() => setActiveTab('reviews')}
                className={`py-4 px-6 font-medium text-sm border-b-2 transition-colors ${
                  activeTab === 'reviews'
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'
                }`}
              >
                Reviews ({productReviews?.total ?? 0})
              </button>
            </nav>
          </div>
          
          <div className="py-6">
            {activeTab === 'description' && (
              <div className="prose max-w-none">
                <div dangerouslySetInnerHTML={{ __html: currentProduct.description || 'No detailed description available' }} />
                
                {/* Promotional Page Link Button */}
                {currentProduct.promo_page_url && (
                  <div className="mt-6">
                    <Link 
                      to={currentProduct.promo_page_url} 
                      className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      View Full Promotional Details
                      <svg className="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fillRule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </Link>
                  </div>
                )}
              </div>
            )}
            
            {activeTab === 'specifications' && (
              <div>
                {Array.isArray(currentProduct.specifications) && currentProduct.specifications.length > 0 ? (
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {currentProduct.specifications.map((spec: { name: string; value: string }, index: number) => (
                      <div key={index} className="flex border-b border-neutral-200 py-3">
                        <div className="w-1/3 font-medium text-neutral-600">{spec.name}</div>
                        <div className="w-2/3 text-neutral-800">{spec.value}</div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-neutral-600">No specifications available</p>
                )}
              </div>
            )}
            
            {activeTab === 'reviews' && (
              <div>
                {loading ? (
                  <div className="flex justify-center py-8">
                    <LoadingSpinner />
                  </div>
                ) : productReviews && Array.isArray(productReviews.data) && productReviews.data.length > 0 ? (
                  <div>
                    {/* Reviews Summary */}
                    <div className="bg-neutral-50 p-6 rounded-lg mb-8 shadow-sm">
                      <div className="flex flex-col md:flex-row md:items-center">
                        <div className="md:w-1/4 mb-6 md:mb-0 text-center">
                          <div className="text-5xl font-bold text-blue-600 mb-2">
                            {(productReviews.average_rating ?? 0).toFixed(1)}
                          </div>
                          <div className="flex justify-center mb-1">
                            {renderStars(productReviews.average_rating ?? 0)}
                          </div>
                          <div className="text-sm text-neutral-600">
                            Based on {productReviews.total ?? 0} reviews
                          </div>
                        </div>
                        
                        <div className="md:w-3/4 md:pl-8 border-t md:border-t-0 md:border-l border-neutral-200 pt-6 md:pt-0 md:pl-8">
                          {/* Rating Distribution */}
                          <div className="space-y-2">
                            {[5, 4, 3, 2, 1].map((rating) => {
                              const count = productReviews.data.filter(review => 
                                Math.floor(review?.rating ?? 0) === rating
                              ).length;
                              
                              const percentage = productReviews.total > 0
                                ? (count / productReviews.total) * 100
                                : 0;
                              
                              return (
                                <div key={rating} className="flex items-center">
                                  <div className="w-12 text-sm text-neutral-600">
                                    {rating} stars
                                  </div>
                                  <div className="flex-1 mx-3">
                                    <div className="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                      <div
                                        className="h-full bg-blue-600"
                                        style={{ width: `${percentage}%` }}
                                      />
                                    </div>
                                  </div>
                                  <div className="w-12 text-sm text-neutral-600 text-right">
                                    {count}
                                  </div>
                                </div>
                              );
                            })}
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    {/* Reviews List */}
                    <div className="space-y-6">
                      {productReviews.data.map((review) => (
                        <div key={review.id} className="border-b border-neutral-200 pb-6">
                          <div className="flex justify-between mb-2">
                            <div className="flex items-center">
                              <div className="font-medium text-neutral-800">
                                {review?.user_name ?? 'Anonymous'}
                              </div>
                              <div className="text-sm text-neutral-500 ml-4">
                                {review?.created_at ? new Date(review.created_at).toLocaleDateString() : '-'}
                              </div>
                            </div>
                            <div className="flex">
                              {renderStars(review?.rating ?? 0)}
                            </div>
                          </div>
                          
                          <p className="text-neutral-600 mb-4">
                            {review?.comment ?? 'No comment provided.'}
                          </p>
                        </div>
                      ))}
                    </div>
                  </div>
                ) : (
                  <p className="text-neutral-600">No reviews yet</p>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
};

export default ProductDetailPage; 