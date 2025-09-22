import React from 'react';

const AddressSkeleton: React.FC = () => {
  return (
    <div className="animate-pulse space-y-3">
      {/* Simulate Name line */}
      <div className="flex items-center">
        <div className="h-4 w-4 bg-gray-300 rounded mr-2"></div>
        <div className="h-4 bg-gray-300 rounded w-1/3"></div>
      </div>
      {/* Simulate Street line */}
      <div className="h-4 bg-gray-300 rounded w-3/4"></div>
      {/* Simulate City/Postal Code line */}
      <div className="h-4 bg-gray-300 rounded w-1/2"></div>
      {/* Simulate Country line */}
      <div className="h-4 bg-gray-300 rounded w-1/4"></div>
      {/* Simulate Phone line */}
      <div className="h-4 bg-gray-300 rounded w-2/5 mt-1"></div>
    </div>
  );
};

export default AddressSkeleton; 