import React from 'react';
import ErrorCard from './ui/feedback/ErrorCard';

interface ErrorDisplayProps {
  message: string;
  title?: string;
  code?: string | number;
}

/**
 * 错误展示组件
 * 使用新的ErrorCard组件显示错误信息
 */
const ErrorDisplay: React.FC<ErrorDisplayProps> = ({ message, title, code }) => {
  // 页面重新加载函数
  const handleRetry = () => {
    window.location.reload();
  };
  
  return (
    <section className="py-12 md:py-16 bg-white dark:bg-gray-900">
      <div className="container mx-auto px-4">
        <div className="max-w-lg mx-auto">
          <ErrorCard
            title={title || 'Error Loading Content'}
            message={message}
            code={code}
            retryAction={handleRetry}
            retryLabel="Retry"
            variant="default"
            className="w-full"
          />
        </div>
      </div>
    </section>
  );
};

export default ErrorDisplay; 