import React from 'react';
import { motion } from 'framer-motion';
import { Link } from 'react-router-dom';
import { Cart } from '../../store/slices/cartSlice';

interface CartListProps {
  carts: Cart[];
  onSelectCart: (cartId: number) => void;
  activeCartId: number | null;
}

const CartList: React.FC<CartListProps> = ({ 
  carts, 
  onSelectCart, 
  activeCartId 
}) => {
  // Get cart type display name
  const getCartTypeName = (type: string) => {
    switch(type) {
      case 'wishlist': return 'Wishlist';
      case 'saveforlater': return 'Save for Later';
      default: return 'Shopping Cart';
    }
  };

  // Get icon based on cart type
  const getCartIcon = (type: string) => {
    switch(type) {
      case 'wishlist': 
        return (
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        );
      case 'saveforlater':
        return (
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        );
      default:
        return (
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        );
    }
  };

  return (
    <div className="w-full">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="text-xl font-bold text-gray-800 mb-6"
      >
        My Shopping Carts
      </motion.div>

      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        {carts.map((cart, index) => (
          <motion.div
            key={cart.id}
            className="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 border border-gray-100"
            initial={{ opacity: 0, y: 20 }}
            animate={{ 
              opacity: 1, 
              y: 0,
              transition: { delay: index * 0.1 }
            }}
            whileHover={{ scale: 1.03 }}
            whileTap={{ scale: 0.98 }}
            onClick={() => onSelectCart(cart.id)}
          >
            <div className="p-6">
              <div className="flex items-center mb-4">
                <div className={`w-12 h-12 rounded-full flex items-center justify-center ${
                  cart.type === 'wishlist' 
                    ? 'bg-pink-100 text-pink-600' 
                    : cart.type === 'saveforlater'
                    ? 'bg-purple-100 text-purple-600'
                    : 'bg-blue-100 text-blue-600'
                }`}>
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {getCartIcon(cart.type)}
                  </svg>
                </div>
                <div className="ml-4">
                  <h3 className="text-lg font-semibold text-gray-800">
                    {cart.name}
                  </h3>
                  <p className="text-sm text-gray-500">
                    {getCartTypeName(cart.type)}
                  </p>
                </div>
                {cart.is_default && (
                  <span className="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">
                    Default
                  </span>
                )}
              </div>

              <div className="flex justify-between items-center border-t border-gray-100 pt-4">
                <div className="text-sm text-gray-500">
                  <span className="font-medium text-gray-700">{cart.item_count}</span> items
                </div>
                <div className="text-sm text-gray-500">
                  Total: <span className="font-medium text-blue-600">${cart.total.toFixed(2)}</span>
                </div>
              </div>

              <div className="mt-4">
                <button
                  className="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-shadow duration-300"
                  onClick={(e) => {
                    e.stopPropagation();
                    onSelectCart(cart.id);
                  }}
                >
                  View Details
                </button>
              </div>
            </div>
          </motion.div>
        ))}

        <motion.div
          className="bg-gray-50 rounded-xl border border-dashed border-gray-300 flex flex-col items-center justify-center p-6 min-h-[200px]"
          initial={{ opacity: 0, y: 20 }}
          animate={{ 
            opacity: 1, 
            y: 0,
            transition: { delay: carts.length * 0.1 }
          }}
          whileHover={{ scale: 1.03, boxShadow: "0 4px 8px rgba(0,0,0,0.1)" }}
        >
          <div className="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-4">
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </div>
          <h3 className="text-lg font-semibold text-gray-800 mb-2">Create New Cart</h3>
          <p className="text-sm text-gray-500 text-center mb-4">
            Create a new cart for different shopping needs
          </p>
          <Link 
            to="/cart-management"
            className="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            Manage Carts →
          </Link>
        </motion.div>
      </div>
    </div>
  );
};

export default CartList; 