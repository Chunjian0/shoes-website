import { Variants } from 'framer-motion';

/**
 * 通用动画变体集合
 * 提供常用的动画效果，可用于各种UI组件
 */

// 淡入动画变体
export const fadeInVariants: Variants = {
  hidden: { opacity: 0 },
  visible: { 
    opacity: 1,
    transition: { 
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 向上淡入动画变体
export const fadeInUpVariants: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { 
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    y: 20,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 向下淡入动画变体
export const fadeInDownVariants: Variants = {
  hidden: { opacity: 0, y: -20 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { 
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    y: -20,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 向左淡入动画变体
export const fadeInLeftVariants: Variants = {
  hidden: { opacity: 0, x: 20 },
  visible: { 
    opacity: 1, 
    x: 0,
    transition: { 
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    x: 20,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 向右淡入动画变体
export const fadeInRightVariants: Variants = {
  hidden: { opacity: 0, x: -20 },
  visible: { 
    opacity: 1, 
    x: 0,
    transition: { 
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    x: -20,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 缩放动画变体
export const scaleVariants: Variants = {
  hidden: { opacity: 0, scale: 0.9 },
  visible: { 
    opacity: 1, 
    scale: 1,
    transition: { 
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    scale: 0.9,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 弹跳动画变体
export const bounceVariants: Variants = {
  hidden: { opacity: 0, y: -20, scale: 0.95 },
  visible: { 
    opacity: 1, 
    y: 0,
    scale: 1,
    transition: { 
      type: 'spring',
      stiffness: 400,
      damping: 15,
    }
  },
  exit: { 
    opacity: 0, 
    y: -20,
    scale: 0.95,
    transition: { 
      duration: 0.3,
    }
  }
};

// 脉动动画变体
export const pulseVariants: Variants = {
  hidden: { opacity: 0 },
  visible: { 
    opacity: 1,
    scale: [1, 1.05, 1],
    transition: { 
      duration: 1.5,
      ease: "easeInOut",
      repeat: Infinity,
      repeatType: "loop",
    }
  },
  exit: { 
    opacity: 0,
    transition: { 
      duration: 0.3,
    }
  }
};

// 旋转淡入动画变体
export const rotateVariants: Variants = {
  hidden: { opacity: 0, rotate: -10, scale: 0.95 },
  visible: { 
    opacity: 1, 
    rotate: 0,
    scale: 1,
    transition: { 
      duration: 0.5,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    rotate: 10,
    scale: 0.95,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 抖动动画变体
export const shakeVariants: Variants = {
  hidden: { opacity: 0 },
  visible: { 
    opacity: 1,
    x: [0, -5, 5, -5, 5, 0],
    transition: { 
      duration: 0.6,
      ease: "easeInOut",
    }
  },
  exit: { 
    opacity: 0,
    transition: { 
      duration: 0.3,
    }
  }
};

// 3D翻转动画变体 - X轴
export const flipXVariants: Variants = {
  hidden: { opacity: 0, rotateX: 90 },
  visible: { 
    opacity: 1, 
    rotateX: 0,
    transition: { 
      duration: 0.6,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    rotateX: 90,
    transition: { 
      duration: 0.4,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 3D翻转动画变体 - Y轴
export const flipYVariants: Variants = {
  hidden: { opacity: 0, rotateY: 90 },
  visible: { 
    opacity: 1, 
    rotateY: 0,
    transition: { 
      duration: 0.6,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0, 
    rotateY: 90,
    transition: { 
      duration: 0.4,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 模态框/对话框动画变体
export const modalVariants: Variants = {
  hidden: { opacity: 0, y: 50, scale: 0.95 },
  visible: { 
    opacity: 1, 
    y: 0,
    scale: 1,
    transition: { 
      type: 'spring',
      damping: 25,
      stiffness: 300,
    }
  },
  exit: { 
    opacity: 0, 
    y: 30,
    scale: 0.95,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 抽屉/侧边栏动画变体 - 从右侧滑入
export const drawerRightVariants: Variants = {
  hidden: { x: '100%' },
  visible: { 
    x: 0,
    transition: { 
      type: 'spring',
      damping: 30,
      stiffness: 300,
    }
  },
  exit: { 
    x: '100%',
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 抽屉/侧边栏动画变体 - 从左侧滑入
export const drawerLeftVariants: Variants = {
  hidden: { x: '-100%' },
  visible: { 
    x: 0,
    transition: { 
      type: 'spring',
      damping: 30,
      stiffness: 300,
    }
  },
  exit: { 
    x: '-100%',
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 列表项动画 - 用于列表容器
export const listContainerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: { 
    opacity: 1,
    transition: { 
      when: "beforeChildren",
      staggerChildren: 0.1,
      delayChildren: 0.1,
    }
  },
  exit: { 
    opacity: 0,
    transition: { 
      when: "afterChildren",
      staggerChildren: 0.05,
    }
  }
};

// 列表项动画 - 用于列表项
export const listItemVariants: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { 
      duration: 0.4,
      ease: [0.33, 1, 0.68, 1],
    }
  },
  exit: { 
    opacity: 0,
    y: 20,
    transition: { 
      duration: 0.3,
      ease: [0.33, 1, 0.68, 1], 
    }
  }
};

// 闪光效果动画变体
export const shimmerVariants: Variants = {
  hidden: { x: '-100%', opacity: 0 },
  visible: { 
    x: '100%',
    opacity: [0, 0.5, 0],
    transition: { 
      repeat: Infinity,
      duration: 1.5,
      ease: "linear",
    }
  }
};

// 工具函数 - 创建延迟序列
export const createDelaySequence = (
  items: any[],
  baseDelay: number = 0.1,
  increment: number = 0.1
): number[] => {
  return items.map((_, index) => baseDelay + index * increment);
};

// 工具函数 - 创建交错子元素动画
export const createStaggerConfig = (
  staggerTime: number = 0.1,
  delayChildren: number = 0
) => ({
  when: "beforeChildren" as const,
  staggerChildren: staggerTime,
  delayChildren,
});

// 工具函数 - 创建自定义缓动动画
export const createCustomEasing = (
  type: 'ease-in' | 'ease-out' | 'ease-in-out' | 'luxury'
) => {
  const easings = {
    'ease-in': [0.4, 0, 1, 1],
    'ease-out': [0, 0, 0.2, 1],
    'ease-in-out': [0.4, 0, 0.2, 1],
    'luxury': [0.33, 1, 0.68, 1]
  };
  
  return easings[type];
}; 