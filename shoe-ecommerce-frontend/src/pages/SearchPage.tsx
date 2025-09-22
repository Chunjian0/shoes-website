import React, { useState, useEffect, useCallback } from 'react';
import { useLocation, useNavigate, Link } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { Helmet } from 'react-helmet-async';
import LoadingSpinner from '../components/LoadingSpinner';
import { ProductTemplate } from '../types/apiTypes';
import PriceRangeSlider from '../components/filters/PriceRangeSlider';
import ProductCard from '../components/ProductCard';

// Components for UI
const SearchFilters: React.FC<{
  categories: string[];
  selectedCategory: string;
  onCategoryChange: (category: string) => void;
  priceRange: [number, number];
  onPriceRangeChange: (range: [number, number]) => void;
  maxPrice: number;
}> = ({ categories, selectedCategory, onCategoryChange, priceRange, onPriceRangeChange, maxPrice }) => {
  
  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-lg font-semibold mb-4">Filters</h2>
      
      {/* Category Filter */}
      <div className="mb-6">
        <h3 className="text-sm font-medium text-gray-700 mb-3">Category</h3>
        <div className="space-y-2">
          <div className="flex items-center">
            <input
              id="category-all"
              name="category"
              type="radio"
              checked={selectedCategory === ""}
              onChange={() => onCategoryChange("")}
              className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label htmlFor="category-all" className="ml-3 text-sm text-gray-700">
              All Categories
            </label>
          </div>
          
          {categories.map((category) => (
            <div key={category} className="flex items-center">
              <input
                id={`category-${category}`}
                name="category"
                type="radio"
                checked={selectedCategory === category}
                onChange={() => onCategoryChange(category)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label htmlFor={`category-${category}`} className="ml-3 text-sm text-gray-700">
                {category}
              </label>
            </div>
          ))}
        </div>
      </div>
      
      {/* Price Range Filter */}
      <div>
        <h3 className="text-sm font-medium text-gray-700 mb-3">Price Range</h3>
        <PriceRangeSlider
          min={0}
          max={1000}
          step={10}
          minValue={priceRange[0]}
          maxValue={priceRange[1]}
          onChange={(min, max) => {
            if (min === '' || max === '') return;
            onPriceRangeChange([Number(min), Number(max)]);
          }}
        />
      </div>
    </div>
  );
};

// SearchResults component
const SearchResults: React.FC<{
  results: ProductTemplate[];
  loading: boolean;
  searchTerm: string;
}> = ({ results, loading, searchTerm }) => {
  
  // Animation variants
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1
      }
    }
  };
  
  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: { type: 'spring', stiffness: 100 }
    }
  };
  
  if (loading) {
    return (
      <div className="flex justify-center items-center py-20">
        <LoadingSpinner size="large" />
        <p className="ml-4 text-lg text-gray-600">Searching...</p>
      </div>
    );
  }
  
  if (results.length === 0) {
    return (
      <div className="py-12 text-center">
        <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
          <svg className="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </div>
        <h3 className="text-lg font-medium text-gray-900 mb-2">No results found</h3>
        <p className="text-gray-500 max-w-md mx-auto">
          We couldn't find any templates matching "{searchTerm}". 
          Try adjusting your search terms or filters.
        </p>
      </div>
    );
  }
  
  return (
    <motion.div 
      className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
      variants={containerVariants}
      initial="hidden"
      animate="visible"
    >
      {results.map((template) => {
        let imageUrl: string | undefined;
        if (template.images && Array.isArray(template.images) && template.images.length > 0) {
          const firstImage = template.images[0];
          if (typeof firstImage === 'string') {
            imageUrl = firstImage;
          } else if (typeof firstImage === 'object') {
            imageUrl = firstImage.url || firstImage.thumbnail || firstImage.image_url;
          }
        } else if (typeof template.images === 'string') {
          imageUrl = template.images;
        }

        return (
          <motion.div
            key={template.id}
            className="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300"
            variants={itemVariants}
          >
            <Link to={`/templates/${template.id}`}>
              <div className="aspect-w-3 aspect-h-2 w-full overflow-hidden">
                <img 
                  src={imageUrl}
                  alt={template.title}
                  className="w-full h-full object-center object-cover transform hover:scale-105 transition-transform duration-500"
                />
              </div>
              <div className="p-4">
                <h3 className="text-lg font-medium text-gray-900 mb-1">{template.title}</h3>
                {template.category && (
                  <p className="text-sm text-blue-600 mb-2">{template.category.name}</p>
                )}
                <p className="text-sm text-gray-500 line-clamp-2 mb-3">
                  {template.description?.replace(/<[^>]*>/g, '') || ''}
                </p>
                <div className="flex items-center justify-between">
                  <p className="font-medium text-gray-900">
                    {template.price ? `$${template.price.toFixed(2)}` : 'Custom Pricing'}
                  </p>
                  <span className="text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                    Template
                  </span>
                </div>
              </div>
            </Link>
          </motion.div>
        );
      })}
    </motion.div>
  );
};

// Main SearchPage component
const SearchPage: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const queryParams = new URLSearchParams(location.search);
  const queryTerm = queryParams.get('q') || '';
  
  // State
  const [searchTerm, setSearchTerm] = useState(queryTerm);
  const [loading, setLoading] = useState(false);
  const [results, setResults] = useState<ProductTemplate[]>([]);
  const [filteredResults, setFilteredResults] = useState<ProductTemplate[]>([]);
  const [categories, setCategories] = useState<string[]>([]);
  const [selectedCategory, setSelectedCategory] = useState('');
  const [priceRange, setPriceRange] = useState<[number, number]>([0, 1000]);
  const [maxPrice, setMaxPrice] = useState(1000);
  
  // Load search results
  useEffect(() => {
    const fetchSearchResults = async () => {
      if (!queryTerm) return;
      
      setLoading(true);
      
      try {
        // In a real application, this would be an API call
        // For this example, we'll simulate a delay and generate mock data
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Mock search results
        const mockResults: ProductTemplate[] = Array.from({ length: 12 }, (_, i) => ({
          id: i + 1,
          title: `${queryTerm.charAt(0).toUpperCase() + queryTerm.slice(1)} Template ${i + 1}`,
          description: `This is a premium template for ${queryTerm} featuring customizable design options and high-quality materials.`,
          category: {
            id: (i % 4) + 1,
            name: ['Running', 'Casual', 'Athletic', 'Fashion'][i % 4]
          },
          images: [
            {
              id: i + 1,
              url: `https://source.unsplash.com/random/400x300?shoes,${i+1}`,
              order: 1
            }
          ],
          price: 49.99 + (i * 10)
        }));
        
        setResults(mockResults);
        
        // Extract categories
        const uniqueCategories = Array.from(
          new Set(mockResults.map(template => template.category?.name || ''))
        ).filter(Boolean);
        
        setCategories(uniqueCategories as string[]);
        
        // Find max price
        const highestPrice = Math.max(...mockResults.map(template => template.price || 0));
        setMaxPrice(Math.ceil(highestPrice / 100) * 100);
        setPriceRange([0, Math.ceil(highestPrice / 100) * 100]);
        
      } catch (error) {
        console.error('Error fetching search results:', error);
      } finally {
        setLoading(false);
      }
    };
    
    fetchSearchResults();
  }, [queryTerm]);
  
  // Handle price range change
  const handlePriceRangeChange = (newRange: [number, number]) => {
    // Validate range values
    const [newMin, newMax] = newRange;
    
    // Ensure min is not greater than max
    if (newMin > newMax) {
      setPriceRange([newMax, newMax]);
      return;
    }
    
    // Apply new price range
    setPriceRange(newRange);
  };
  
  // Filter results based on selected filters
  useEffect(() => {
    if (results.length === 0) return;
    
    let filtered = [...results];
    
    // Apply category filter
    if (selectedCategory) {
      filtered = filtered.filter(template => template.category?.name === selectedCategory);
    }
    
    // Apply price filter with validation
    filtered = filtered.filter(template => {
      const price = template.price || 0;
      const [minPrice, maxPrice] = priceRange;
      
      // Check if price is within range
      return price >= minPrice && price <= maxPrice;
    });
    
    setFilteredResults(filtered);
  }, [results, selectedCategory, priceRange]);
  
  // Handle search form submission
  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchTerm) {
      navigate(`/search?q=${encodeURIComponent(searchTerm)}`);
    }
  };
  
  // Handle category filter change
  const handleCategoryChange = (category: string) => {
    setSelectedCategory(category);
  };
  
  return (
    <>
      <Helmet>
        <title>{queryTerm ? `Search Results for "${queryTerm}" | YCE Shoes` : 'Search | YCE Shoes'}</title>
        <meta name="description" content={`Search results for ${queryTerm} - Find the perfect shoe template for your needs.`} />
      </Helmet>
      
      {/* Search Header */}
      <section className="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-12">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl md:text-4xl font-bold mb-6 text-center">
            {queryTerm ? `Search Results for "${queryTerm}"` : 'Search Templates'}
          </h1>
          
          <form onSubmit={handleSearchSubmit} className="max-w-3xl mx-auto relative">
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Search for templates..."
              className="w-full px-5 py-4 pr-16 rounded-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-white shadow-lg"
            />
            <motion.button
              type="submit"
              className="absolute right-2 top-1/2 transform -translate-y-1/2 rounded-full bg-blue-600 p-3 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </motion.button>
          </form>
        </div>
      </section>
      
      {/* Search Results Section */}
      <section className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <div className="flex flex-col lg:flex-row gap-8">
            {/* Filters */}
            <div className="w-full lg:w-1/4">
              <div className="sticky top-24">
                <SearchFilters
                  categories={categories}
                  selectedCategory={selectedCategory}
                  onCategoryChange={handleCategoryChange}
                  priceRange={priceRange}
                  onPriceRangeChange={handlePriceRangeChange}
                  maxPrice={maxPrice}
                />
              </div>
            </div>
            
            {/* Results Grid */}
            <div className="w-full lg:w-3/4">
              <div className="mb-6 flex justify-between items-center">
                <p className="text-gray-600">
                  {loading ? 'Searching...' : `Showing ${filteredResults.length} results`}
                </p>
              </div>
              
              {/* Results */}
              <SearchResults
                results={filteredResults}
                loading={loading}
                searchTerm={queryTerm}
              />
            </div>
          </div>
        </div>
      </section>
      
      {/* Related Searches (Only show if we have results) */}
      {filteredResults.length > 0 && (
        <section className="py-12 bg-white">
          <div className="container mx-auto px-4">
            <h2 className="text-2xl font-bold mb-6">Related Searches</h2>
            <div className="flex flex-wrap gap-3">
              {['Custom shoes', 'Designer templates', 'Performance footwear', 'Eco-friendly shoes'].map((term) => (
                <Link
                  key={term}
                  to={`/search?q=${encodeURIComponent(term)}`}
                  className="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-full text-gray-700 text-sm transition-colors"
                >
                  {term}
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  );
};

export default SearchPage; 