import React from 'react';
import { FiEdit2, FiMapPin, FiPhone } from 'react-icons/fi'; // Import icons
import { motion } from 'framer-motion';

// 直接定义UserAddress接口，而不是从CheckoutPage导入
interface UserAddress {
  id?: number;
  name: string;
  phone: string;
  address: string;
}

interface AddressDisplayProps {
  address: UserAddress;
  onEdit: () => void; // Callback function to trigger edit mode
}

const AddressDisplay: React.FC<AddressDisplayProps> = ({ address, onEdit }) => {
  return (
    <motion.div 
      className="bg-gray-50 p-4 rounded-md border border-gray-200"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.3 }}
    >
      <div className="flex justify-between items-start">
        <div className="space-y-1">
          {/* Display Name */}
          <div className="flex items-center">
              <FiMapPin className="w-4 h-4 text-gray-500 mr-2 flex-shrink-0" />
              <p className="text-sm font-medium text-gray-800">{address.name}</p>
          </div>
          {/* Display Address Lines */}
          <p className="text-sm text-gray-600 pl-6">{address.address}</p>
          {/* Display Country (Implicitly Malaysia) */}
          <p className="text-sm text-gray-600 pl-6">Malaysia</p>
          {/* Display Phone */}
          {address.phone && (
             <div className="flex items-center pt-1">
                <FiPhone className="w-4 h-4 text-gray-500 mr-2 flex-shrink-0 pl-1" />
                <p className="text-sm text-gray-600">{address.phone}</p>
             </div>
          )}
        </div>
        {/* Edit Button */}
        <button 
          onClick={onEdit}
          className="text-sm text-indigo-600 hover:text-indigo-800 transition-colors flex items-center p-1 rounded hover:bg-indigo-50 ml-2 flex-shrink-0"
          aria-label="Edit Address"
        >
            <FiEdit2 className="w-4 h-4" />
        </button>
      </div>
    </motion.div>
  );
};

export default AddressDisplay; 