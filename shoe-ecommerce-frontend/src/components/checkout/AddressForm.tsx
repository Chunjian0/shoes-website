import React, { useState, FormEvent, useEffect } from 'react';
import { motion, useAnimation } from 'framer-motion';
import Spinner from '../ui/loading/Spinner'; // Import Spinner for loading state

// 直接定义UserAddress接口
interface UserAddress {
  id?: number;
  name: string;
  phone: string;
  address: string;
}

interface AddressFormProps {
  initialData?: Partial<UserAddress>; // Use Partial as initial data might be incomplete
  onSubmit: (address: UserAddress) => Promise<void>; // Function to call on successful submission
  onCancel?: () => void; // Optional function to call on cancel
}

const AddressForm: React.FC<AddressFormProps> = ({ initialData, onSubmit, onCancel }) => {
  const [formData, setFormData] = useState<UserAddress>({
    id: initialData?.id, // Keep ID if provided
    name: initialData?.name || '',
    phone: initialData?.phone || '',
    address: initialData?.address || '', // Single address field
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});
  // Animation controls for each field group
  const nameControls = useAnimation();
  const phoneControls = useAnimation();
  const addressControls = useAnimation(); // Renamed from streetControls

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    // Clear specific error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  // Shake animation definition
  const shakeAnimation = {
    x: [0, -6, 6, -6, 6, 0],
    transition: { duration: 0.4, ease: "easeInOut" }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};
    let firstErrorControl: any = null; // To store the first control to shake

    if (!formData.name.trim()) {
        newErrors.name = 'Full name is required';
        if (!firstErrorControl) firstErrorControl = nameControls;
    }
    if (!formData.phone.trim()) {
        newErrors.phone = 'Phone number is required';
        if (!firstErrorControl) firstErrorControl = phoneControls;
    } else if (!/^\+?\d{7,}$/.test(formData.phone.replace(/\s+/g, ''))) {
      newErrors.phone = 'Please enter a valid phone number';
      if (!firstErrorControl) firstErrorControl = phoneControls;
    }
    if (!formData.address.trim()) {
        newErrors.address = 'Address is required';
        if (!firstErrorControl) firstErrorControl = addressControls;
    }

    setErrors(newErrors);
    
    // Trigger shake animation only on the first invalid field
    if (firstErrorControl) {
        firstErrorControl.start(shakeAnimation);
    }

    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);
    try {
      await onSubmit(formData); // Call the passed onSubmit function
      // Success is handled by the parent component (e.g., hiding the form)
    } catch (error) {
      console.error("Error submitting address:", error);
      // Optionally display a general error message here
      setErrors(prev => ({ ...prev, form: 'Failed to save address. Please try again.' }));
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <motion.form 
        onSubmit={handleSubmit} 
        className="space-y-4"
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4 }}
    >
      {errors.form && <p className="text-red-500 text-sm mb-4">{errors.form}</p>}
      
      {/* Form Fields */}
      <motion.div animate={nameControls}>
        <label htmlFor="name" className="block text-sm font-medium text-gray-700">Full Name</label>
        <input 
          type="text" 
          id="name" 
          name="name" 
          value={formData.name}
          onChange={handleChange}
          disabled={isSubmitting}
          className={`mt-1 block w-full border rounded-md shadow-sm p-2 ${errors.name ? 'border-red-500' : 'border-gray-300'} focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200`}
        />
        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
      </motion.div>

      <motion.div animate={phoneControls}>
        <label htmlFor="phone" className="block text-sm font-medium text-gray-700">Phone Number</label>
        <input 
          type="tel" 
          id="phone" 
          name="phone" 
          value={formData.phone}
          onChange={handleChange}
          disabled={isSubmitting}
          className={`mt-1 block w-full border rounded-md shadow-sm p-2 ${errors.phone ? 'border-red-500' : 'border-gray-300'} focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200`}
        />
        {errors.phone && <p className="text-red-500 text-xs mt-1">{errors.phone}</p>}
      </motion.div>
      
      <motion.div animate={addressControls}>
        <label htmlFor="address" className="block text-sm font-medium text-gray-700">Address</label>
        <textarea 
          id="address" 
          name="address" 
          rows={4}
          value={formData.address}
          onChange={handleChange}
          disabled={isSubmitting}
          className={`mt-1 block w-full border rounded-md shadow-sm p-2 ${errors.address ? 'border-red-500' : 'border-gray-300'} focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200`}
        />
        {errors.address && <p className="text-red-500 text-xs mt-1">{errors.address}</p>}
      </motion.div>

      {/* Action Buttons */}
      <div className="flex justify-end space-x-3 pt-2">
        {onCancel && (
          <motion.button
            type="button"
            onClick={onCancel}
            disabled={isSubmitting}
            className="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            whileHover={{ scale: 1.03 }}
            whileTap={{ scale: 0.97 }}
            transition={{ duration: 0.1 }}
          >
            Cancel
          </motion.button>
        )}
        <motion.button
          type="submit" 
          disabled={isSubmitting}
          className="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 flex items-center justify-center"
          whileHover={{ scale: 1.03 }}
          whileTap={{ scale: 0.97 }}
          transition={{ duration: 0.1 }}
        >
          {isSubmitting ? (
            <>
              <Spinner size="xs" className="mr-2" />
              Saving...
            </>
          ) : (
            'Save Address'
          )}
        </motion.button>
      </div>
    </motion.form>
  );
};

export default AddressForm; 