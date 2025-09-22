import React, { useState, useRef, useEffect } from 'react';
import DOMPurify from 'dompurify';
import './TemplateDescription.css'; // 引入单独的CSS文件

interface TemplateDescriptionOptions {
  maxHeight?: number;
  responsive?: boolean;
  className?: string;
  summaryLength?: number; // 摘要显示的长度，单位是字符
}

// 组件接口
interface TemplateDescriptionProps {
  htmlContent: string;
  options?: TemplateDescriptionOptions;
}

/**
 * 安全渲染HTML内容的模板描述组件
 * 支持折叠/展开长内容，响应式图片和表格
 */
const TemplateDescription: React.FC<TemplateDescriptionProps> = ({ 
  htmlContent, 
  options = {}
}) => {
  const { 
    maxHeight = 300, 
    responsive = true, 
    className = "",
    summaryLength = 0
  } = options;
  
  const [expanded, setExpanded] = useState(false);
  const [needsExpansion, setNeedsExpansion] = useState(false);
  const contentRef = useRef<HTMLDivElement>(null);
  
  // 清理HTML内容
  const sanitizedHtml = DOMPurify.sanitize(htmlContent, {
    USE_PROFILES: { html: true },
    ALLOWED_TAGS: [
      'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 
      'p', 'br', 'a', 'ul', 'ol', 'li', 
      'strong', 'em', 'blockquote', 'span', 'div',
      'table', 'thead', 'tbody', 'tr', 'th', 'td',
      'img'
    ],
    ALLOWED_ATTR: [
      'href', 'target', 'rel', 'src', 'alt', 'width', 'height',
      'style', 'class', 'id', 'title'
    ]
  });
  
  // 摘要内容 - 如果指定了summaryLength
  const getSummary = () => {
    if (!summaryLength) return sanitizedHtml;
    
    // 创建临时元素以提取文本
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = sanitizedHtml;
    const text = tempDiv.textContent || tempDiv.innerText;
    
    if (text.length <= summaryLength) return sanitizedHtml;
    
    return text.substr(0, summaryLength) + '...';
  };
  
  // 检查内容高度是否超过限制
  useEffect(() => {
    if (contentRef.current) {
      const contentHeight = contentRef.current.scrollHeight;
      setNeedsExpansion(contentHeight > maxHeight);
    }
  }, [sanitizedHtml, maxHeight]);
  
  // 计算内容样式
  const getContentStyle = () => {
    if (!needsExpansion || expanded) {
      return {};
    }
    return { maxHeight: `${maxHeight}px` };
  };
  
  // 获取内容区域类名
  const getContentClassName = () => {
    let classes = 'template-description-content';
    
    // 添加响应式类
    if (responsive) {
      classes += ' template-description-responsive';
    }
    
    // 添加自定义类
    if (className) {
      classes += ` ${className}`;
    }
    
    // 添加展开状态类
    if (expanded) {
      classes += ' template-description-expanded';
    }
    
    return classes;
  };
  
  // 显示"阅读更多"按钮的条件
  const showReadMoreButton = needsExpansion && (summaryLength === 0);
  
  // 计算渐变遮罩类名
  const gradientMaskClass = (!expanded && needsExpansion) ? 'template-description-gradient-mask' : '';
  
  return (
    <div className="template-description">
      <div 
        ref={contentRef}
        className={getContentClassName()}
        style={getContentStyle()}
        dangerouslySetInnerHTML={{ __html: expanded || !summaryLength ? sanitizedHtml : getSummary() }}
      >
      </div>
      
      {gradientMaskClass && <div className={gradientMaskClass}></div>}
      
      {showReadMoreButton && (
        <button 
          onClick={() => setExpanded(!expanded)} 
          className="template-description-read-more"
        >
          {expanded ? '收起' : '阅读更多'} 
          <svg 
            xmlns="http://www.w3.org/2000/svg" 
            className="h-4 w-4 ml-1" 
            viewBox="0 0 20 20" 
            fill="currentColor"
          >
            <path 
              fillRule="evenodd" 
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" 
              clipRule="evenodd" 
            />
          </svg>
        </button>
      )}
      
      {summaryLength > 0 && needsExpansion && (
        <button 
          onClick={() => setExpanded(!expanded)} 
          className="template-description-read-more"
        >
          {expanded ? '收起' : '查看完整描述'}
        </button>
      )}
    </div>
  );
};

export default TemplateDescription; 