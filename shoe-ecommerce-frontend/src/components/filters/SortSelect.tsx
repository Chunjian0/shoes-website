import React from 'react';
import { motion } from 'framer-motion';

interface SortSelectProps {
  value: string;
  onChange: (value: string) => void;
}

const SortSelect: React.FC<SortSelectProps> = ({ value, onChange }) => {
  // 排序选项
  const sortOptions = [
    { value: 'newest', label: 'Newest First' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'name_asc', label: 'Name: A to Z' },
    { value: 'discount', label: 'Biggest Discount' },
    { value: 'popularity', label: 'Popularity' },
  ];
  
  // 找到当前选中选项的标签
  const selectedLabel = sortOptions.find(option => option.value === value)?.label || 'Sort By';
  
  return (
    <div className="relative">
      <label htmlFor="sort-select" className="block text-sm mb-2" style={{
        fontFamily: 'Playfair Display, serif',
        fontSize: '14px',
        letterSpacing: '-0.02em',
        color: '#0A0A0A'
      }}>
        Sort By
      </label>
      
      <div className="relative">
        <select
          id="sort-select"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className="appearance-none w-full py-2.5 pr-10 pl-3 focus:outline-none text-sm"
          style={{
            fontFamily: 'Montserrat, sans-serif',
            border: 'none',
            borderBottom: '1px solid rgba(212, 175, 55, 0.3)',
            background: 'transparent',
            color: '#0A0A0A',
            cursor: 'pointer',
            transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
          }}
          onFocus={(e) => {
            e.target.style.borderBottom = '2px solid #D4AF37';
          }}
          onBlur={(e) => {
            e.target.style.borderBottom = '1px solid rgba(212, 175, 55, 0.3)';
          }}
        >
          {sortOptions.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        
        {/* 自定义下拉箭头 */}
        <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2">
          <motion.svg 
            width="12" 
            height="12" 
            viewBox="0 0 12 12"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className="transform transition-transform duration-300"
            animate={{ rotate: 0 }}
            style={{ color: '#D4AF37' }}
          >
            <path
              d="M2.5 4.5L6 8L9.5 4.5"
              stroke="currentColor"
              strokeWidth="1.5"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </motion.svg>
        </div>
      </div>
    </div>
  );
};

export default SortSelect; 