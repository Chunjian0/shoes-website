import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import AnimatedElement from '../animations/AnimatedElement';
import { 
  QuestionMarkCircleIcon, 
  XMarkIcon, 
  ShoppingBagIcon, 
  HeartIcon, 
  BookmarkIcon,
  PlusIcon,
  MinusIcon,
  ArrowPathIcon,
  CheckCircleIcon
} from '@heroicons/react/24/outline';

interface CartTutorialProps {
  onClose: () => void;
}

const CartTutorial: React.FC<CartTutorialProps> = ({ onClose }) => {
  const [currentStep, setCurrentStep] = useState(1);
  const totalSteps = 4;
  
  // 教程步骤内容
  const tutorialSteps = [
    {
      title: "管理多个购物车",
      description: "您可以创建多个购物车，例如日常购物、礼物清单或偶尔购买。每个购物车都可以独立管理。",
      icon: ShoppingBagIcon,
    },
    {
      title: "创建愿望清单",
      description: "使用不同类型的购物车来组织您的购物意图。愿望清单可以保存您想要但暂不购买的商品。",
      icon: HeartIcon,
    },
    {
      title: "稍后购买",
      description: "将商品添加到'稍后购买'购物车，以便在准备好时再决定是否购买。",
      icon: BookmarkIcon,
    },
    {
      title: "结账和量化调整",
      description: "您可以轻松调整商品数量、移除商品，并在准备好时结账。您的数据会即时同步。",
      icon: CheckCircleIcon,
    }
  ];
  
  // 获取当前步骤内容
  const currentStepData = tutorialSteps[currentStep - 1];
  
  // 进入下一步或完成教程
  const handleNext = () => {
    if (currentStep < totalSteps) {
      setCurrentStep(currentStep + 1);
    } else {
      onClose();
    }
  };
  
  // 返回上一步
  const handlePrev = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    }
  };
  
  // 动画变体
  const overlayVariants = {
    hidden: { opacity: 0 },
    visible: { opacity: 1 },
    exit: { opacity: 0 }
  };
  
  const modalVariants = {
    hidden: { opacity: 0, y: 50, scale: 0.95 },
    visible: { 
      opacity: 1, 
      y: 0, 
      scale: 1,
      transition: {
        type: "spring",
        damping: 25,
        stiffness: 300
      }
    },
    exit: { 
      opacity: 0, 
      y: 30, 
      scale: 0.95,
      transition: { duration: 0.2 }
    }
  };
  
  // 步骤指示器项的变体
  const stepIndicatorVariants = {
    inactive: { scale: 1, backgroundColor: "rgb(229, 231, 235)" },
    active: { 
      scale: 1.2, 
      backgroundColor: "rgb(59, 130, 246)",
      transition: { type: "spring", stiffness: 300, damping: 20 } 
    }
  };
  
  const IconComponent = currentStepData.icon;
  
  return (
    <AnimatePresence>
      <motion.div
        className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        variants={overlayVariants}
        initial="hidden"
        animate="visible"
        exit="exit"
        onClick={onClose}
      >
        <motion.div
          className="bg-white rounded-2xl max-w-lg w-full mx-auto overflow-hidden"
          variants={modalVariants}
          initial="hidden"
          animate="visible"
          exit="exit"
          onClick={(e) => e.stopPropagation()}
        >
          {/* 关闭按钮 */}
          <button
            className="absolute top-4 right-4 text-gray-500 hover:text-gray-700 focus:outline-none z-10"
            onClick={onClose}
          >
            <XMarkIcon className="w-6 h-6" />
          </button>
          
          {/* 教程内容 */}
          <div className="p-8">
            <div className="flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mx-auto mb-6">
              <IconComponent className="w-10 h-10 text-blue-600" />
            </div>
            
            <AnimatePresence mode="wait">
              <motion.div
                key={currentStep}
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                transition={{ duration: 0.3 }}
                className="text-center"
              >
                <h2 className="text-2xl font-bold text-gray-800 mb-3">
                  {currentStepData.title}
                </h2>
                <p className="text-gray-600 mb-8">
                  {currentStepData.description}
                </p>
              </motion.div>
            </AnimatePresence>
            
            {/* 步骤指示器 */}
            <div className="flex justify-center space-x-2 mb-8">
              {Array.from({ length: totalSteps }).map((_, index) => (
                <motion.div
                  key={index}
                  className="w-3 h-3 rounded-full"
                  variants={stepIndicatorVariants}
                  animate={currentStep === index + 1 ? "active" : "inactive"}
                  onClick={() => setCurrentStep(index + 1)}
                  style={{ cursor: "pointer" }}
                />
              ))}
            </div>
            
            {/* 导航按钮 */}
            <div className="flex justify-between">
              <button
                className={`px-4 py-2 rounded-md text-sm font-medium transition-colors ${
                  currentStep > 1 
                  ? "text-gray-700 hover:bg-gray-100" 
                  : "text-gray-400 cursor-not-allowed"
                }`}
                onClick={handlePrev}
                disabled={currentStep === 1}
              >
                上一步
              </button>
              
              <button
                className="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
                onClick={handleNext}
              >
                {currentStep < totalSteps ? "下一步" : "完成"}
              </button>
            </div>
          </div>
        </motion.div>
      </motion.div>
    </AnimatePresence>
  );
};

export default CartTutorial; 