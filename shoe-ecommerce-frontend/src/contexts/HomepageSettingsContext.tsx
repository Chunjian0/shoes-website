import React, { createContext, useContext, useEffect, useState, ReactNode, useCallback } from 'react';
import { HomepageSettings, SettingsUpdateEvent, SettingsUpdateEventType, CarouselSettings, ResponsiveSettings, LayoutTemplate } from '../types/homepage';
import homepageService from '../services/homepageService';
import { toast } from 'react-toastify';

// 默认轮播设置
const defaultCarouselSettings: CarouselSettings = {
  autoplay: true,
  delay: 5000,
  transition: 'slide',
  showNavigation: true,
  showIndicators: true
};

// 默认响应式设置
const defaultResponsiveSettings: ResponsiveSettings = {
  mobile_layout: 'compact',
  tablet_columns: 2,
  desktop_columns: 4,
  show_categories_on_mobile: true,
  enable_touch_gestures: true,
  // 添加缺失的 breakpoints
  breakpoints: {
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280
  }
};

// 初始空设置
const initialSettings: HomepageSettings = {
  active_template: LayoutTemplate.MODERN,
  banners: [{
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
    imageUrl: '',
  }],
  offer: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
    imageUrl: '',
  },
  featuredProducts: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
  },
  newProducts: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
  },
  saleProducts: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
  },
  carousel: {
    autoplay: true,
    delay: 5000,
    transition: 'slide',
    showNavigation: true,
    showIndicators: true
  },
  site_name: 'Optic System',
  show_promotion: true,
  show_brands: true,
  featured_products_count: 8,
  new_products_count: 8,
  sale_products_count: 8,
  templates: {
    featured: [],
    newArrival: [],
    sale: []
  }
};

// Context的类型
interface HomepageSettingsContextProps {
  settings: HomepageSettings | null;
  loading: boolean;
  error: Error | null;
  lastUpdate: SettingsUpdateEvent | null;
  refreshSettings: () => void;
}

// 创建Context
const HomepageSettingsContext = createContext<HomepageSettingsContextProps | undefined>(undefined);

// Provider组件
export const HomepageSettingsProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [settings, setSettings] = useState<HomepageSettings | null>(initialSettings); // 使用初始设置
  const [loading, setLoading] = useState<boolean>(false); // 初始设为 false
  const [error, setError] = useState<Error | null>(null);
  const [lastUpdate, setLastUpdate] = useState<SettingsUpdateEvent | null>(null);
  const [isLoadingAttempted, setIsLoadingAttempted] = useState<boolean>(true); // 假定已加载

  // 注释掉 fetchSettings，因为 homepageService 中没有对应方法
  /*
  const fetchSettings = useCallback(async (updateType: SettingsUpdateEventType) => {
    setLoading(true);
    setError(null);
    try {
      // const data = await homepageService.getHomepageSettings(); // 方法不存在
      const data = initialSettings; // 使用初始设置代替
      setSettings(data);
      setLastUpdate({ type: updateType, timestamp: Date.now() });
    } catch (err) {
      setError(err instanceof Error ? err : new Error(String(err)));
      console.error("Failed to fetch homepage settings:", err);
    } finally {
      setLoading(false);
    }
  }, []);
  */

  // 注释掉 loadSettings，因为它依赖的方法也不存在
  /*
  const loadSettings = async (type: SettingsUpdateEventType = SettingsUpdateEventType.ALL_UPDATED) => {
    if (isLoadingAttempted && type !== SettingsUpdateEventType.ALL_UPDATED) {
      return;
    }
    
    try {
      setLoading(true);
      setError(null);
      
      let homeSettings: HomepageSettings | null = null;
      // let homeSettings = await redisService.get<HomepageSettings>('homepage:formatted_settings');
      
      // if (!homeSettings) {
      //   homeSettings = await homepageService.getHomepageSettingsFromRedis(); // 方法不存在
      // }

      homeSettings = initialSettings; // 使用初始设置
      
      console.log('[HomepageSettingsContext] 加载到的设置:', homeSettings);
      
      const validatedSettings: HomepageSettings = {
        // ... 属性验证 ...
        ...(homeSettings || initialSettings)
      };
      
      setSettings(validatedSettings);
      
      const updateEvent: SettingsUpdateEvent = {
        type,
        // settings: validatedSettings, // 移除 settings 属性
        timestamp: Date.now()
      };
      setLastUpdate(updateEvent);
      
      console.log(`[HomepageSettingsContext] 成功加载设置 (${type})`, validatedSettings);
    } catch (err) {
      console.error(`[HomepageSettingsContext] 加载设置失败 (${type})`, err);
      setError(err instanceof Error ? err : new Error('Failed to load settings'));
      setSettings(initialSettings);
    } finally {
      setLoading(false);
      setIsLoadingAttempted(true);
    }
  };
  */

  // 手动刷新设置 - 暂时不做任何事，因为加载逻辑已注释
  const refreshSettings = useCallback(() => {
    console.log("[HomepageSettingsContext] refreshSettings called, but loading logic is commented out.");
    setLastUpdate({ type: SettingsUpdateEventType.ALL_UPDATED, timestamp: Date.now() }); // 模拟更新事件
    // await loadSettings(SettingsUpdateEventType.ALL_UPDATED);
  }, []); // 移除 loadSettings 依赖

  // 初始加载设置 - 移除，因为加载逻辑已注释
  /*
  useEffect(() => {
    loadSettings();
  }, [loadSettings]);
  */

  // 设置轮询 - 注释掉
  /*
  useEffect(() => {
    // ... polling logic ...
  }, []);
  */

  // 构建Context值
  const value = {
    settings,
    loading,
    error,
    lastUpdate,
    refreshSettings
  };

  // 提供Context
  return (
    <HomepageSettingsContext.Provider value={value}>
      {children}
    </HomepageSettingsContext.Provider>
  );
};

// 自定义Hook，用于访问Context
export const useHomepageSettings = (): HomepageSettingsContextProps => {
  const context = useContext(HomepageSettingsContext);
  if (context === undefined) {
    throw new Error('useHomepageSettings必须在HomepageSettingsProvider内部使用');
  }
  return context;
};

export default HomepageSettingsContext; 