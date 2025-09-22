import React, { useState } from 'react';
import { LayoutTemplate } from '../../types/homepage';
import AnimatedElement from '../animations/AnimatedElement';

// 内联：模板配置接口
interface TemplateConfig {
  name: string;
  description: string;
  previewImage: string;
  colors: {
    primary: string;
    secondary: string;
    accent: string;
    background: string;
    text: string;
  };
  layout: {
    banner: {
      height: 'small' | 'medium' | 'tall' | 'full';
    };
    featuredProducts: {
      layout: 'grid' | 'slider' | 'showcase';
      columns: 2 | 3 | 4 | 5;
    };
  };
  useAnimations: boolean;
}

// 内联：模板配置数据
const templateConfigs: TemplateConfig[] = [
  {
    name: "Classic",
    description: "A traditional e-commerce layout with a focus on product visibility and clear navigation",
    previewImage: "https://images.unsplash.com/photo-1573855619003-97b4799dcd8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80",
    colors: {
      primary: 'blue',
      secondary: 'gray',
      accent: 'yellow',
      background: 'white',
      text: 'gray-900'
    },
    layout: {
      banner: {
        height: 'medium'
      },
      featuredProducts: {
        layout: 'grid',
        columns: 4
      }
    },
    useAnimations: true
  },
  {
    name: "Modern",
    description: "A contemporary design with large hero areas, parallax effects, and visual depth",
    previewImage: "https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80",
    colors: {
      primary: 'indigo',
      secondary: 'purple',
      accent: 'pink',
      background: 'white',
      text: 'gray-900'
    },
    layout: {
      banner: {
        height: 'tall'
      },
      featuredProducts: {
        layout: 'showcase',
        columns: 3
      }
    },
    useAnimations: true
  },
  {
    name: "Minimal",
    description: "A clean, minimalist approach with ample white space and focus on typography",
    previewImage: "https://images.unsplash.com/photo-1539185441755-769473a23570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80",
    colors: {
      primary: 'black',
      secondary: 'gray',
      accent: 'white',
      background: 'white',
      text: 'gray-900'
    },
    layout: {
      banner: {
        height: 'medium'
      },
      featuredProducts: {
        layout: 'grid',
        columns: 4
      }
    },
    useAnimations: false
  },
  {
    name: "Bold",
    description: "High-contrast design with vibrant colors, large typography, and striking visuals",
    previewImage: "https://images.unsplash.com/photo-1595341595379-cf1cd0fb7fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80",
    colors: {
      primary: 'red',
      secondary: 'yellow',
      accent: 'black',
      background: 'white',
      text: 'gray-900'
    },
    layout: {
      banner: {
        height: 'tall'
      },
      featuredProducts: {
        layout: 'grid',
        columns: 2
      }
    },
    useAnimations: true
  }
];

// 内联：获取模板配置函数
const getTemplateConfig = (template: LayoutTemplate): TemplateConfig => {
  const config = templateConfigs.find(
    t => t.name.toLowerCase() === template.toLowerCase()
  );
  return config || templateConfigs[0];
};

// LayoutSelector组件的属性接口
interface LayoutSelectorProps {
  activeTemplate: LayoutTemplate;
  onTemplateChange: (template: LayoutTemplate) => void;
  showPreview?: boolean;
  className?: string;
}

// 模板选择器组件
export const LayoutSelector: React.FC<LayoutSelectorProps> = ({
  activeTemplate,
  onTemplateChange,
  showPreview = true,
  className = ''
}) => {
  // 悬停状态
  const [hoveredTemplate, setHoveredTemplate] = useState<LayoutTemplate | null>(null);

  // 处理模板选择
  const handleSelectTemplate = (template: LayoutTemplate) => {
    onTemplateChange(template);
  };

  return (
    <div className={`mt-6 ${className}`}>
      <h3 className="text-lg font-medium text-gray-900 mb-4">选择布局模板</h3>
      
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {Object.values(LayoutTemplate).map((template) => {
          const config = getTemplateConfig(template);
          const isActive = activeTemplate === template;
          const isHovered = hoveredTemplate === template;
          
          return (
            <div
              key={template}
              className={`relative rounded-lg overflow-hidden border-2 transition-all duration-200 cursor-pointer ${
                isActive ? 'border-blue-500 shadow-md' : 'border-gray-200 hover:border-blue-300'
              }`}
              onClick={() => handleSelectTemplate(template)}
              onMouseEnter={() => setHoveredTemplate(template)}
              onMouseLeave={() => setHoveredTemplate(null)}
            >
              <div className="aspect-w-16 aspect-h-9">
                <img 
                  src={config.previewImage} 
                  alt={config.name} 
                  className="object-cover w-full h-full"
                />
                <div className={`absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center transition-opacity duration-200 ${
                  isHovered || isActive ? 'opacity-100' : 'opacity-0'
                }`}>
                  <span className="text-white font-medium">{config.name}</span>
                </div>
              </div>
              
              {isActive && (
                <div className="absolute top-2 right-2 w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center">
                  <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                </div>
              )}
            </div>
          );
        })}
      </div>
      
      {showPreview && hoveredTemplate && (
        <AnimatedElement type="fade-in" className="mt-6">
          <div className="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 className="font-medium text-gray-900">{getTemplateConfig(hoveredTemplate).name}</h4>
            <p className="text-gray-600 text-sm mt-1">{getTemplateConfig(hoveredTemplate).description}</p>
            
            <div className="mt-4 grid grid-cols-2 gap-4">
              <div>
                <h5 className="text-sm font-medium text-gray-700">布局样式</h5>
                <ul className="mt-1 text-xs text-gray-600 space-y-1">
                  <li>• 英雄横幅: {getTemplateConfig(hoveredTemplate).layout.banner.height}</li>
                  <li>• 产品网格: {getTemplateConfig(hoveredTemplate).layout.featuredProducts.layout}, {getTemplateConfig(hoveredTemplate).layout.featuredProducts.columns}列</li>
                </ul>
              </div>
              
              <div>
                <h5 className="text-sm font-medium text-gray-700">视觉风格</h5>
                <ul className="mt-1 text-xs text-gray-600 space-y-1">
                  <li>• 主色调: {getTemplateConfig(hoveredTemplate).colors.primary}</li>
                  <li>• 动画效果: {getTemplateConfig(hoveredTemplate).useAnimations ? '启用' : '禁用'}</li>
                </ul>
              </div>
            </div>
          </div>
        </AnimatedElement>
      )}
    </div>
  );
}; 