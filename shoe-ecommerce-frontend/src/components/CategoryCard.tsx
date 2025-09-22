import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Category } from '../types/category';

interface CategoryCardProps {
  category: Category;
}

const CategoryCard: React.FC<CategoryCardProps> = ({ category }) => {
  const [imageError, setImageError] = useState(false);

  const handleImageError = () => {
    if (!imageError) {
      setImageError(true);
    }
  };

  return (
    <Link 
      to={`/products?category=${category.id}`}
      className="block group"
    >
      <div className="relative overflow-hidden rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 bg-white">
        <div className="aspect-square overflow-hidden">
          {!imageError ? (
            <img
              src={category.image_url || `https://placehold.co/300x300?text=${category.name}`}
              alt={category.name}
              className="w-full h-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
              onError={handleImageError}
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-gray-100">
              <div className="text-gray-400 text-center p-4">
                <div className="text-4xl mb-2">👞</div>
                <p className="text-sm">{category.name}</p>
              </div>
            </div>
          )}
        </div>
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent flex items-end">
          <div className="p-4 text-white">
            <h3 className="text-lg font-medium">{category.name}</h3>
            {category.product_count !== undefined && (
              <p className="text-sm text-gray-200">{category.product_count} Products</p>
            )}
          </div>
        </div>
      </div>
    </Link>
  );
};

export default CategoryCard; 