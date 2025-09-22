import React from 'react';

interface TextureBackgroundProps {
  variant?: 'subtle' | 'medium' | 'strong';
  color?: 'gold' | 'black' | 'gradient';
  opacity?: number;
  className?: string;
  children?: React.ReactNode;
  overlay?: boolean;
  pattern?: 'noise' | 'dots' | 'lines' | 'grid';
}

/**
 * 添加微妙的背景纹理组件，提升页面层次感
 * 符合奢华高端设计系统
 */
const TextureBackground: React.FC<TextureBackgroundProps> = ({
  variant = 'subtle',
  color = 'black',
  opacity = 0.05,
  className = '',
  children,
  overlay = false,
  pattern = 'noise',
}) => {
  // 控制纹理的强度
  const intensityMap = {
    subtle: 'opacity-[0.03]',
    medium: 'opacity-[0.05]',
    strong: 'opacity-[0.08]',
  };
  
  // 背景颜色样式
  const bgColorStyles = {
    black: 'bg-black dark:bg-gray-900',
    gold: 'bg-amber-100 dark:bg-amber-900',
    gradient: 'bg-gradient-to-r from-gray-50 to-amber-50 dark:from-gray-900 dark:to-gray-800',
  };
  
  // 纹理模式样式 (改为样式对象)
  const patternStyles: Record<string, React.CSSProperties> = {
    noise: {
      backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E")`,
    },
    dots: {
      backgroundImage: 'radial-gradient(#000 0.5px, transparent 0.5px)',
      backgroundSize: '12px 12px',
    },
    lines: {
      backgroundImage: 'linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px)',
      backgroundSize: '24px 24px',
    },
    grid: {
      backgroundImage: `linear-gradient(rgba(0,0,0,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px)`,
      backgroundSize: '24px 24px',
    },
  };

  // 用户指定的不透明度
  const opacityStyle: React.CSSProperties = opacity !== undefined ? 
    { opacity: opacity } : 
    {};

  return (
    <div className={`relative ${className}`}>
      {children}
      
      <div 
        className={`
          ${overlay ? 'absolute inset-0 pointer-events-none' : 'fixed inset-0 -z-10'}
          ${intensityMap[variant]}
          ${color === 'gradient' ? bgColorStyles.gradient : ''}
        `}
        style={{
          ...(opacity !== undefined && color !== 'gradient' ? { opacity: opacity } : {}),
          ...(pattern === 'noise' ? { filter: 'contrast(150%) brightness(1000%)' } : {}),
        }}
      >
        <div 
          className={`w-full h-full ${color !== 'gradient' ? bgColorStyles[color] : ''}`}
          style={{ 
            ...patternStyles[pattern],
            ...(opacity !== undefined && color === 'gradient' ? { opacity: opacity } : {}),
          }}
        />
      </div>
    </div>
  );
};

export default TextureBackground;
