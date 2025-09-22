import React from 'react';

interface TemplateSkeletonProps {
  aspectRatio?: '1:1' | '4:3' | '3:2' | '16:9';
}

const TemplateSkeleton: React.FC<TemplateSkeletonProps> = ({
  aspectRatio = '3:2'
}) => {
  // 根据宽高比设置样式
  const aspectRatioClasses = {
    '1:1': 'aspect-w-1 aspect-h-1',
    '4:3': 'aspect-w-4 aspect-h-3',
    '3:2': 'aspect-w-3 aspect-h-2',
    '16:9': 'aspect-w-16 aspect-h-9'
  };

  return (
    <div className="animate-pulse">
      {/* 图片骨架 */}
      <div className={`${aspectRatioClasses[aspectRatio]} w-full mb-2`}>
        <div className="w-full h-full rounded-lg bg-gray-200"></div>
      </div>
      
      {/* 类别骨架 */}
      <div className="h-3 bg-gray-200 rounded w-1/3 mb-2"></div>
      
      {/* 标题骨架 - 两行 */}
      <div className="h-4 bg-gray-200 rounded w-full mb-1"></div>
      <div className="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
      
      {/* 价格骨架 */}
      <div className="flex items-center">
        <div className="h-5 bg-gray-200 rounded w-1/4 mr-2"></div>
        <div className="h-4 bg-gray-200 rounded w-1/5"></div>
      </div>
    </div>
  );
};

export default TemplateSkeleton; 