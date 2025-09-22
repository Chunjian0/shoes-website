import React, { useState, useEffect } from 'react';
import { Dialog, Transition } from '@headlessui/react'; // Using Headless UI for accessibility

interface CreateCartModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (cartData: { name: string; type: string; is_default?: boolean }) => Promise<void>;
}

const CreateCartModal: React.FC<CreateCartModalProps> = ({ isOpen, onClose, onSubmit }) => {
  const [cartName, setCartName] = useState('');
  const [cartType, setCartType] = useState<'default' | 'wishlist' | 'saveforlater'>('default');
  const [isDefault, setIsDefault] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Reset form when modal opens or closes
  useEffect(() => {
    if (isOpen) {
      setCartName('');
      setCartType('default');
      setIsDefault(false);
      setIsSubmitting(false);
      setError(null);
    }
  }, [isOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!cartName.trim()) {
      setError('Cart name cannot be empty.');
      return;
    }
    setError(null);
    setIsSubmitting(true);
    try {
      await onSubmit({ 
        name: cartName.trim(), 
        type: cartType, 
        is_default: isDefault && cartType === 'default' // is_default only makes sense for 'default' type
      });
      // Let the parent handle closing on success
    } catch (err: any) {
      setError(err.message || 'An unexpected error occurred during submission.');
      // Keep modal open on error
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <Transition show={isOpen} as={React.Fragment}>
      <Dialog as="div" className="relative z-50" onClose={onClose}>
        <Transition.Child
          as={React.Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />
        </Transition.Child>

        <div className="fixed inset-0 z-10 overflow-y-auto">
          <div className="flex min-h-full items-center justify-center p-4 text-center">
            <Transition.Child
              as={React.Fragment}
              enter="ease-out duration-300"
              enterFrom="opacity-0 scale-95"
              enterTo="opacity-100 scale-100"
              leave="ease-in duration-200"
              leaveFrom="opacity-100 scale-100"
              leaveTo="opacity-0 scale-95"
            >
              <Dialog.Panel className="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <Dialog.Title
                  as="h3"
                  className="text-lg font-medium leading-6 text-gray-900 mb-4"
                >
                  Create New Cart
                </Dialog.Title>
                
                <form onSubmit={handleSubmit}>
                  <div className="space-y-4">
                    {/* Cart Name */}
                    <div>
                      <label htmlFor="cartName" className="block text-sm font-medium text-gray-700">
                        Cart Name <span className="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        id="cartName"
                        value={cartName}
                        onChange={(e) => setCartName(e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                        disabled={isSubmitting}
                      />
                    </div>

                    {/* Cart Type */}
                    <div>
                      <label htmlFor="cartType" className="block text-sm font-medium text-gray-700">
                        Cart Type
                      </label>
                      <select
                        id="cartType"
                        value={cartType}
                        onChange={(e) => setCartType(e.target.value as 'default' | 'wishlist' | 'saveforlater')}
                        className="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                        disabled={isSubmitting}
                      >
                        <option value="default">Default Shopping Cart</option>
                        <option value="wishlist">Wishlist</option>
                        <option value="saveforlater">Save for Later</option>
                      </select>
                    </div>

                    {/* Is Default (only for 'default' type) */}
                    {cartType === 'default' && (
                      <div className="flex items-center">
                        <input
                          id="isDefault"
                          type="checkbox"
                          checked={isDefault}
                          onChange={(e) => setIsDefault(e.target.checked)}
                          className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                          disabled={isSubmitting}
                        />
                        <label htmlFor="isDefault" className="ml-2 block text-sm text-gray-900">
                          Set as my default active cart
                        </label>
                      </div>
                    )}

                    {/* Error Message */}
                    {error && (
                      <p className="text-sm text-red-600">{error}</p>
                    )}
                  </div>

                  {/* Action Buttons */}
                  <div className="mt-6 flex justify-end space-x-3">
                    <button
                      type="button"
                      className="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                      onClick={onClose}
                      disabled={isSubmitting}
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      className="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                      disabled={isSubmitting || !cartName.trim()}
                    >
                      {isSubmitting ? 'Creating...' : 'Create Cart'}
                    </button>
                  </div>
                </form>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition>
  );
};

export default CreateCartModal; 