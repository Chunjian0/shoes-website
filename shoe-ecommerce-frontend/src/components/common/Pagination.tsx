import React from 'react';

interface PaginationProps {
  currentPage: number;
  totalPages: number;
  onPageChange: (page: number) => void;
  siblingCount?: number;
}

const Pagination: React.FC<PaginationProps> = ({
  currentPage,
  totalPages,
  onPageChange,
  siblingCount = 1
}) => {
  // 如果只有一页，不显示分页
  if (totalPages <= 1) return null;
  
  // 生成页码范围
  const generatePageRange = () => {
    const range = [];
    
    // 始终包含第一页
    range.push(1);
    
    // 计算显示范围
    const leftSiblingIndex = Math.max(2, currentPage - siblingCount);
    const rightSiblingIndex = Math.min(totalPages - 1, currentPage + siblingCount);
    
    // 是否需要显示左省略号
    const showLeftDots = leftSiblingIndex > 2;
    // 是否需要显示右省略号
    const showRightDots = rightSiblingIndex < totalPages - 1;
    
    // 处理左侧页码
    if (showLeftDots) {
      // 如果当前页靠近前面，显示较多前面的页码
      if (currentPage - siblingCount <= 3) {
        for (let i = 2; i < leftSiblingIndex; i++) {
          range.push(i);
        }
      } else {
        range.push('...');
      }
    } else {
      // 如果左侧页码少，全部显示
      for (let i = 2; i < leftSiblingIndex; i++) {
        range.push(i);
      }
    }
    
    // 中间页码范围
    for (let i = leftSiblingIndex; i <= rightSiblingIndex; i++) {
      range.push(i);
    }
    
    // 处理右侧页码
    if (showRightDots) {
      // 如果当前页靠近末尾，显示较多后面的页码
      if (totalPages - (currentPage + siblingCount) <= 2) {
        for (let i = rightSiblingIndex + 1; i < totalPages; i++) {
          range.push(i);
        }
      } else {
        range.push('...');
      }
    } else {
      // 如果右侧页码少，全部显示
      for (let i = rightSiblingIndex + 1; i < totalPages; i++) {
        range.push(i);
      }
    }
    
    // 始终包含最后一页
    if (totalPages > 1) {
      range.push(totalPages);
    }
    
    return range;
  };
  
  const pages = generatePageRange();
  
  return (
    <nav className="flex justify-center">
      <ul className="flex items-center space-x-1">
        {/* 上一页按钮 */}
        <li>
          <button
            onClick={() => onPageChange(currentPage - 1)}
            disabled={currentPage === 1}
            className={`px-3 py-2 rounded-md text-sm font-medium 
              ${currentPage === 1 
                ? 'text-gray-400 cursor-not-allowed' 
                : 'text-gray-700 hover:bg-gray-100'}`}
            aria-label="上一页"
          >
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
        </li>
        
        {/* 页码 */}
        {pages.map((page, index) => (
          <li key={index}>
            {page === '...' ? (
              <span className="px-4 py-2 text-gray-500">...</span>
            ) : (
              <button
                onClick={() => onPageChange(page as number)}
                className={`px-4 py-2 rounded-md text-sm font-medium
                  ${currentPage === page
                    ? 'bg-blue-600 text-white' 
                    : 'text-gray-700 hover:bg-gray-100'}`}
                aria-current={currentPage === page ? 'page' : undefined}
              >
                {page}
              </button>
            )}
          </li>
        ))}
        
        {/* 下一页按钮 */}
        <li>
          <button
            onClick={() => onPageChange(currentPage + 1)}
            disabled={currentPage === totalPages}
            className={`px-3 py-2 rounded-md text-sm font-medium 
              ${currentPage === totalPages 
                ? 'text-gray-400 cursor-not-allowed' 
                : 'text-gray-700 hover:bg-gray-100'}`}
            aria-label="下一页"
          >
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </li>
      </ul>
    </nav>
  );
};

export default Pagination; 