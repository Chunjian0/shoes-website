import React from 'react';
import { Category } from '../../types/category';
import AnimatedElement from '../animations/AnimatedElement';

interface CategoryGridSectionProps {
  categories: Category[];
  title?: string;
  subtitle?: string;
  cols?: 2 | 3 | 4;
  isLoading?: boolean;
  error?: string | null;
}

const CategoryGridSection: React.FC<CategoryGridSectionProps> = ({
  categories,
  title = "Browse Categories",
  subtitle = "Explore our wide range of footwear",
  cols = 4,
  isLoading = false,
  error = null
}) => {
  if (isLoading) {
    return (
      <section className="py-12 md:py-16">
        <div className="container mx-auto px-4">
          <div className="text-center mb-8">
            <div className="h-8 bg-gray-200 rounded w-64 mx-auto mb-3 animate-pulse"></div>
            <div className="h-4 bg-gray-100 rounded w-96 mx-auto animate-pulse"></div>
          </div>
          <div className={`grid grid-cols-1 sm:grid-cols-2 md:grid-cols-${cols} gap-6 animate-pulse`}>
            {[...Array(cols * 2)].map((_, i) => (
              <div key={i} className="bg-gray-200 rounded-lg h-40"></div>
            ))}
          </div>
        </div>
      </section>
    );
  }

  if (error) {
    return (
      <section className="py-12">
        <div className="container mx-auto px-4">
          <div className="text-center text-red-500">
            <p>{error}</p>
          </div>
        </div>
      </section>
    );
  }

  return (
    <section className="py-12 md:py-16">
      <div className="container mx-auto px-4">
        <div className="text-center mb-10">
          <AnimatedElement type="fade-in">
            <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-3">{title}</h2>
            <p className="text-gray-600 max-w-2xl mx-auto">{subtitle}</p>
          </AnimatedElement>
        </div>

        <div className={`grid grid-cols-1 sm:grid-cols-2 md:grid-cols-${cols} gap-6`}>
          {categories.map((category, index) => (
            <AnimatedElement 
              key={category.id} 
              type="fade-in" 
              options={{ delay: 0.1 * index }}
            >
              <a 
                href={`/categories/${category.slug}`}
                className="block relative group overflow-hidden rounded-lg shadow-sm hover:shadow-md transition-shadow"
              >
                {/* Background Image */}
                <div className="aspect-w-16 aspect-h-9 bg-gray-200">
                  {category.image_url && (
                    <img 
                      src={category.image_url} 
                      alt={category.name}
                      className="object-cover w-full h-full transition-transform duration-500 group-hover:scale-105"
                    />
                  )}
                </div>
                
                {/* Content Overlay */}
                <div className="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent flex items-end p-4">
                  <div>
                    <h3 className="text-white font-bold text-lg">{category.name}</h3>
                    <p className="text-white/80 text-sm mt-1">
                      {category.product_count || 0} Products
                    </p>
                  </div>
                </div>
              </a>
            </AnimatedElement>
          ))}
        </div>
      </div>
    </section>
  );
};

export default CategoryGridSection; 